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
    limit: 12,
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
const categories = ref([]); // categories for the current result set (for CategoriesTree)
const allCategories = ref([]); // full category tree seen so far (from initial run)
const currentCategory = ref(null);
const selectedParent = ref(null);
const currentStep = ref(props.index);
const showCards = ref(false);

const sortOptions = [
    {
        label: 'Alphabetical (A-Z)',
        order: 'asc',
        orderBy: 'name'
    },
    {
        label: 'Alphabetical (Z-A) ',
        order: 'desc',
        orderBy: 'name'
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
    }
];

const sort = ref(sortOptions[3]);
const search = ref(null);

// Build allCategories once from the categories you get back on products.
const buildAllCategories = (productsData) => {
    const map = new Map();

    productsData.forEach((p) => {
        (p.categories || []).forEach((cat) => {
            // add the category itself
            if (!map.has(cat.id)) {
                map.set(cat.id, {
                    id: cat.id,
                    name: cat.name,
                    parent_id: cat.parent_id,
                    // add any other fields you need in CategoriesTree
                    ...cat
                });
            }

            // walk up parents from the payload (if present)
            let current = cat.parent;
            while (current && current.id) {
                if (!map.has(current.id)) {
                    map.set(current.id, {
                        id: current.id,
                        name: current.name,
                        parent_id: current.parent_id,
                        ...current
                    });
                }
                current = current.parent;
            }
        });
    });

    allCategories.value = Array.from(map.values());
};

/**
 * Main: get products
 */
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

    // Use allCategories here to get selected category + its descendants
    if (currentCategory.value) {
        let array = [currentCategory.value];
        let items = [];

        do {
            let current = array.pop();
            items.push(current);
            // IMPORTANT: use allCategories for traversal, not just categories for current result
            let temp = allCategories.value.filter((x) => x.parent_id == current.id);
            array.push(...temp);
        } while (array.length != 0);

        filters.push({
            column: 'categories.id',
            operator: 'in',
            value: items.map((x) => x.id)
        });
    }

    let query = queryString.stringify({
        page: meta.page,
        perPage: meta.limit,
        filters: JSON.stringify(filters),
        account_id: props.account.id,
        order: sort.value.order,
        orderBy: sort.value.orderBy,
        search: search.value
    });
    let tempCategoryIds = [];
    let res = await axios.get('/api/products' + '?' + query);
    res.data.data.forEach((e) => {
        let temp = quote.items.find((x) => x.product_code == e.sku);
        e.quantity = temp ? temp.quantity : 0;
        e.contract_length = e.price_plan.code == 'SINGLE' ? 1 : 12;

        (e.categories || []).forEach((cat) => {
            if (!tempCategoryIds.includes(cat.id)) {
                tempCategoryIds.push(cat.id);
            }

            let current = cat.parent;
            while (current && current.id) {
                if (!tempCategoryIds.includes(current.id)) {
                    tempCategoryIds.push(current.id);
                }
                current = current.parent;
            }
        });
    });
    products.value = res.data.data;

    // First run: build the allCategories from the initial products
    if (!allCategories.value.length && products.value.length) {
        buildAllCategories(products.value);
    }

    // categories for the CategoriesTree - only those that have products in this result
    categories.value = allCategories.value.filter((c) => tempCategoryIds.includes(c.id));

    meta.first = (meta.page - 1) * meta.limit;
    meta.total = res.data.total;
    meta.overallTotal = res.data.overall_total;
    meta.isLoading = false;
};

const getCategories = async (categoryIds = []) => {
    if (!categoryIds.length) {
        return;
    }

    const filters = [
        {
            column: 'id',
            operator: 'in',
            value: categoryIds
        },
        {
            column: 'is_active',
            operator: 'equals',
            value: 1
        }
    ];

    const query = queryString.stringify({
        filters: JSON.stringify(filters)
    });

    const res = await axios.get('/api/categories' + '?' + query);
    categories.value = res.data.data ?? [];
};

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    getProducts();
};

const emitSaveQuote = () => {
    emitter.emit('save-quote');
};

watch(sort, () => {
    getProducts();
});

watch(
    currentCategory,
    _.debounce(() => {
        meta.page = 1;
        getProducts();
    }, 500),
    { deep: true }
);

watch(proxyValue, async () => {
    await getProducts();
});

watch(
    search,
    _.debounce(() => {
        if (search.value) {
            meta.page = 1;
        }
        getProducts();
    }, 500)
);

getProducts();
</script>

<template>
    <div v-if="proxyValue == currentStep" :id="'step-' + (currentStep + 1)" class="step">
        <div class="step">
            <div class="row">
                <div class="d-flex justify-content-between mb-3">
                    <h3>{{ currentStep + 1 }}. {{ step.name }}</h3>
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
                        <div class="d-flex flex-fill justify-content-start">
                            <button
                                class="btn border"
                                :class="[showCards ? 'btn-dark' : '']"
                                @click="() => (showCards = !showCards)"
                                style="border-radius: 5px 0 0 5px"
                            >
                                <font-awesome-icon :icon="['fas', 'grip']"></font-awesome-icon>
                            </button>
                            <button
                                class="btn border"
                                :class="[showCards ? '' : 'btn-dark']"
                                @click="() => (showCards = !showCards)"
                                style="border-radius: 0 5px 5px 0"
                            >
                                <font-awesome-icon :icon="['fas', 'grip-lines']"></font-awesome-icon>
                            </button>
                        </div>
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
                        <div v-if="categories.length" class="mb-4">
                            <h2>Categories</h2>
                            <CategoriesTree v-model="currentCategory" :categories="categories"></CategoriesTree>
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
</template>

<style></style>
