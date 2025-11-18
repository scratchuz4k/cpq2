<script setup>
import queryString from 'query-string';
import { computed, reactive, ref, watch } from 'vue';
import { quote, getQuote } from '@composables/useQuote';
import _ from 'lodash';
import { updateQuoteItem, removeQuoteItem, createQuoteItem } from '@composables/useQuoteItem';
import { Paginator, Select, Skeleton } from 'primevue';

const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 5,
    isLoading: false
});

const disableButton = ref(false);

const props = defineProps({
    step: Object,
    account: Object,
    modelValue: Number,
    index: Number,
    total_steps: Number
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const products = ref([]);
const categories = ref([]);
const selectedParent = ref(null);
const currentStep = ref(props.index);
const showCards = ref(false);

const sortOptions = [
    {
        label: 'Alphabetical (A-Z)',
        order: 'asc',
        orderBy: 'model.name'
    },
    {
        label: 'Alphabetical (Z-A) ',
        order: 'desc',
        orderBy: 'model.name'
    },
    {
        label: 'Lowest Price',
        order: 'asc',
        orderBy: 'price.net_spa'
    },
    {
        label: 'Highest Price',
        order: 'desc',
        orderBy: 'price.net_spa'
    },
    {
        label: 'Lowest Stock',
        order: 'asc',
        orderBy: 'stock'
    },
    {
        label: 'Highest Stock',
        order: 'desc',
        orderBy: 'stock'
    }
];

const sort = ref(sortOptions[3]);

const search = ref(null);

const getProducts = async () => {
    meta.isLoading = true;

    let filters = [
        {
            column: 'steps.id',
            operator: 'equals',
            value: props.step.id
        },
        {
            column: 'status',
            operator: 'equals',
            value: 'Active'
        },
        {
            group: [
                { column: 'parent_id', operator: 'in', value: quote.items.map((x) => x.product_id) },
                { column: 'parent_id', operator: 'is_null', value: '', function: 'orWhere' }
            ]
        }
    ];

    let query = queryString.stringify({
        page: meta.page,
        limit: meta.limit,
        filters: JSON.stringify(filters),
        account_id: props.account.id
    });
    let res = await axios.get('/api/products' + '?' + query);
    res.data.data.forEach((e) => {
        let temp = quote.items.find((x) => x.product_code == e.sku);
        e.quantity = temp ? temp.quantity : 0;
        e.contract_length = e.price_plan.code == 'SINGLE' ? 1 : 12;
    });
    products.value = res.data.data;

    meta.first = (meta.page - 1) * meta.limit;
    meta.total = res.data.total;
    meta.overallTotal = res.data.overall_total;
    meta.isLoading = false;
};

const getCategories = async () => {
    categories.value = [];
    let current_page = 1;
    let last_page = 1;
    do {
        let string = queryString.stringify({
            limit: 500,
            page: current_page
        });
        let res = await axios.get('/api/categories?' + string);
        categories.value.push(...res.data.data);
        last_page = res.data.meta?.last_page;
        current_page++;
    } while (current_page != last_page + 1);
};

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    getProducts();
};

const checkQuantity = _.debounce(
    async (product, temp) => {
        disableButton.value = true;
        if (product.stock <= temp) {
            temp = product.stock;
        }
        product.quantity = temp;

        let item = quote.items.find((x) => x.product_code == product.sku);
        if (!item) {
            createQuoteItem({
                quote_id: quote.id,
                product_code: product.sku,
                product_id: product.id,
                quantity: product.quantity,
                contract_length: product.contract_length,
                name: product.name,
                raw_price: product.price ? product.price.cost : 0,
                price: product.price ? product.price.net_spa : 0,
                price_plan_id: product.price_plan_id
            });
        } else {
            if (product.quantity == 0) {
                removeQuoteItem(item.id);
            } else {
                updateQuoteItem(item.id, { quantity: product.quantity });
            }
        }

        await getQuote(quote.id);

        disableButton.value = false;
    },
    200,
    { leading: true, trailing: true }
);

const emitSaveQuote = () => {
    emitter.emit('save-quote');
};

watch(proxyValue, () => {
    getProducts();
});
getProducts();
getCategories();
</script>

<template>
    <div v-if="proxyValue == currentStep" :id="'step-' + (currentStep + 1)" class="step">
        <div class="step">
            <div class="row">
                <div class="d-flex justify-content-between mb-3">
                    <h2>Select some products</h2>
                    <p class="bigger text-primary bold">
                        STEP
                        <span id="current-step">{{ currentStep + 1 }}</span>
                        /
                        <span id="last-step">{{ props.total_steps }}</span>
                    </p>
                </div>
            </div>
            <div class="row justify-content-end">
                <div class="col-3">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="d-flex">
                            <h2>Products ({{ meta.total }})</h2>
                        </div>
                    </div>
                </div>
                <div class="col-9">
                    <div class="d-flex justify-content-end align-items-end mb-3">
                        <div class="d-flex justify-content-end text-nowrap flex-shrink-0">
                            <span class="me-2 align-self-center">Sort by</span>
                            <Select v-model="sort" :options="sortOptions" optionLabel="label" placeholder="All">
                            </Select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="rounded bg-white p-3">
                        <div class="mb-4">
                            <h2>Search</h2>
                            <input type="search" class="form-control" placeholder="" v-model="search" />
                        </div>
                        <div class="mb-4">
                            <h2>Categories</h2>
                            <CategoriesTree v-model="categories" :categories="categories"></CategoriesTree>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <template v-if="meta.isLoading">
                        <ProductSkeleton v-for="item in [0, 1, 2, 3, 4]"></ProductSkeleton>
                    </template>
                    <template v-else>
                        <div class="text-center pt-2 pb-4" v-if="!products.length">No Results Found</div>
                        <div class="row" :class="[showCards ? 'row-cols-4' : 'row-cols-1']">
                            <ProductCard
                                v-for="(product, index) in products"
                                :hasSelect="true"
                                :showAsColumn="showCards"
                                :index="index"
                                v-model="products[index]"
                            >
                            </ProductCard>
                        </div>
                        <Paginator
                            v-model:first="meta.first"
                            :rows="meta.limit"
                            :totalRecords="meta.total"
                            :rowsPerPageOptions="[12, 24, 48]"
                            @page="handlePaginator"
                        >
                        </Paginator>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <button
                                    :disabled="meta.isLoading"
                                    v-if="props.index > 0"
                                    :id="'btn-step-{{ props.index + 1}}-previous'"
                                    class="btn text-primary step-nav btn-sm me-3"
                                    type="button"
                                    @click="() => proxyValue--"
                                >
                                    <font-awesome-icon :icon="['fas', 'arrow-left']" /> Previous
                                </button>
                            </div>
                            <div>
                                <button
                                    :disabled="disableButton"
                                    :id="'btn-step-' + (props.index + 1) + '-next'"
                                    class="ms-3 btn btn-primary step-nav"
                                    type="button"
                                    @click="() => emitSaveQuote()"
                                >
                                    Next
                                    <font-awesome-icon :icon="['fas', 'arrow-right']" />
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- <div v-if="proxyValue == props.index" :id="'step-' + (props.index + 1)" class="step">
        <div class="d-flex justify-content-between mb-3">
            <h3 class="mb-0">{{ props.index + 1 }}. {{ step?.name }}</h3>
            <p class="bigger bold text-primary">
                STEP <span id="current-step">{{ props.index + 1 }}</span> /
                <span id="last-step">{{ total_steps }}</span>
            </p>
        </div>
        <div v-if="!meta.isLoading">
            <div v-if="products.length" v-for="(product, index) in products" class="mobile-item mobile-product mb-3">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center mb-2">
                        <h4 class="m-0">{{ product.name }}</h4>
                        <p class="m-0 ms-2">
                            {{ product.description }} /
                            {{ format.GBP(product.price?.net_spa || 0) }}
                        </p>
                    </div>
                    <font-awesome-icon :icon="['fa', 'circle-info']" />
                </div>
                <div class="mobile-product-quantity">
                    <label>Quantity</label>
                    <div class="d-flex align-items-center pb-3">
                        <div :style="{ width: '150px' }">
                            <InputQuantity
                                :id="'bolt-' + index"
                                v-model="product.quantity"
                                @change="(ev) => checkQuantity(product, ev)"
                            />
                        </div>

                        <button
                            :id="'btn-plan-' + index + '-add-25'"
                            type=" button"
                            class="btn btn-sm ms-4 btn-add-custom-quantity"
                            @click="checkQuantity(product, product.quantity + 25)"
                        >
                            +25
                        </button>
                        <button
                            :id="'btn-plan-' + index + '-add-50'"
                            type=" button"
                            class="btn btn-sm mx-2 btn-add-custom-quantity"
                            @click="checkQuantity(product, product.quantity + 50)"
                        >
                            +50
                        </button>
                        <button
                            :id="'btn-plan-' + index + '-add-100'"
                            type=" button"
                            class="btn btn-sm btn-add-custom-quantity"
                            @click="checkQuantity(product, product.quantity + 100)"
                        >
                            +100
                        </button>
                    </div>
                </div>
            </div>
            <div v-else>
                <p class="h5 text-center my-4">No products available for this Step!</p>
            </div>
        </div>
        <div v-else v-for="n in meta.limit" class="mobile-item mobile-product mb-3">
            <div class="d-flex justify-content-between mb-3">
                <div class="d-flex">
                    <Skeleton width="10rem" height="1.7rem" class="mb-2 me-2"></Skeleton>
                    <Skeleton width="10rem" height="1.7rem" class="mb-2"></Skeleton>
                </div>
                <Skeleton shape="circle" size="1.5rem" class="mb-2"></Skeleton>
            </div>
            <Skeleton width="5rem" class="mb-2 me-2"></Skeleton>
            <div class="d-flex">
                <Skeleton width="10rem" height="1.7rem" class="mb-2 me-3"></Skeleton>
                <Skeleton width="4rem" height="1.7rem" class="mb-2 me-2"></Skeleton>
                <Skeleton width="4rem" height="1.7rem" class="mb-2 me-2"></Skeleton>
                <Skeleton width="4rem" height="1.7rem" class="mb-2 me-2"></Skeleton>
            </div>
        </div>
        <Paginator
            v-model:first="meta.first"
            :rows="meta.limit"
            :totalRecords="meta.total"
            :rowsPerPageOptions="[5, 10, 20]"
            @page="handlePaginator"
        >
        </Paginator>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <button
                    :disabled="meta.isLoading"
                    v-if="props.index > 0"
                    :id="'btn-step-{{ props.index + 1}}-previous'"
                    class="btn text-primary step-nav btn-sm me-3"
                    type="button"
                    @click="() => proxyValue--"
                >
                    <font-awesome-icon :icon="['fas', 'arrow-left']" /> Previous
                </button>
            </div>
            <div>
                <button
                    :disabled="disableButton"
                    :id="'btn-step-' + (props.index + 1) + '-next'"
                    class="ms-3 btn btn-primary step-nav"
                    type="button"
                    @click="() => emitSaveQuote()"
                >
                    Next
                    <font-awesome-icon :icon="['fas', 'arrow-right']" />
                </button>
            </div>
        </div>
    </div> -->
</template>

<style></style>
