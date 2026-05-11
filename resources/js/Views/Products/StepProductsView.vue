<script setup>
import queryString from 'qs';
import { computed, reactive, ref, watch, onMounted, watchEffect } from 'vue';
import { quote } from '@composables/useQuote';
import _ from 'lodash';
import { Paginator, Select, Skeleton } from 'primevue';
import { selectedItems, addSelected } from '@/Composables/useComparator';
import { actionBarButtons } from '@/Composables/useQuoteSteps.js';
import OrderSummary from '@/Components/OrderSummary.vue';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';

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
    total_steps: Number,
    quote: Object
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
const allCategories = ref([]);
const currentCategory = ref(null);
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

const sort = ref(sortOptions[0]);
const search = ref(null);

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
                { column: 'parents.id', operator: 'in', value: quote.items.map((x) => x.product_id) },
                { column: 'parents.id', operator: 'is_null', value: '', function: 'orWhere' }
            ]
        }
    ];

    if (currentCategory.value) {
        let array = [currentCategory.value];
        let items = [];

        do {
            let current = array.pop();
            items.push(current);
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
        limit: meta.limit,
        filters: filters,
        account_id: props.account.id,
        order: sort.value.order,
        orderBy: sort.value.orderBy,
        search: search.value
    });

    let res = await axios.get('/api/products' + '?' + query);

    const tempCategories = [...allCategories.value];

    res.data.data.forEach((e) => {
        let temp = quote.items.find((x) => x.product_code == e.sku);
        e.quantity = temp ? temp.quantity : 0;
        e.contract_length = e.price_plan.code == 'SINGLE' ? 1 : 12;
        (e.categories || []).forEach((cat) => {
            if (!tempCategories.find((c) => c.id === cat.id)) {
                tempCategories.push({
                    id: cat.id,
                    name: cat.name,
                    parent_id: cat.parent_id,
                    ...cat
                });
            }

            (cat.parent_tree || []).forEach((parentCat) => {
                if (!tempCategories.find((c) => c.id === parentCat.id)) {
                    tempCategories.push({
                        id: parentCat.id,
                        name: parentCat.name,
                        parent_id: parentCat.parent_id,
                        ...parentCat
                    });
                }
            });
        });
    });

    products.value = res.data.data;

    allCategories.value = tempCategories;
    categories.value = tempCategories;

    meta.first = (meta.page - 1) * meta.limit;
    meta.total = res.data.total;
    meta.overallTotal = res.data.overall_total;
    meta.isLoading = false;
};

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    getProducts();
};

const emitSaveQuote = () => {
    emitter.emit('save-quote');
};

onMounted(async () => {
    getProducts();
});

watchEffect(() => {
    if (proxyValue.value !== props.index) return;

    actionBarButtons.value = [
        props.index > 0
            ? {
                  btn: 'back',
                  id: `btn-step-${props.index + 1}-previous`,
                  disabled: meta.isLoading,
                  onClick: () => proxyValue.value--
              }
            : null,
        {
            btn: 'next',
            disabled: disableButton.value,
            id: `btn-step-${props.index + 1}-next`,
            onClick: emitSaveQuote
        }
    ].filter(Boolean);
});

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
    currentCategory.value = null;
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
</script>

<template>
    <div v-if="proxyValue == currentStep" :id="'step-' + (currentStep + 1)" class="step">
        <div class="step">
            <div class="row mb-3">
                <div class="col-9 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">{{ currentStep + 1 }}. {{ step.name }} ({{ meta.total }})</h3>
                    <div class="d-flex">
                        <IconField class="me-2">
                            <InputText
                                :id="'step-' + (currentStep + 1) + '-search-products'"
                                v-model="search"
                                placeholder="Search"
                                type="search"
                            />
                            <InputIcon class="text-black">
                                <font-awesome-icon :icon="['fas', 'search']" />
                            </InputIcon>
                        </IconField>
                        <Select
                            class="sort-order-select"
                            v-model="sort"
                            :options="sortOptions"
                            optionLabel="label"
                            placeholder="All"
                        >
                        </Select>
                    </div>
                </div>
                <div class="col-3 d-flex justify-content-end align-items-center">
                    <p class="bigger text-primary bold">
                        STEP
                        <span id="current-step">{{ currentStep + 1 }}</span>
                        /
                        <span id="last-step">{{ props.total_steps }}</span>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-9">
                    <div v-if="categories.length" class="filters rounded bg-white p-3 mb-3">
                        <div class="row mb-3">
                            <div class="col-3">
                                <div class="mt-2">
                                    <p class="bold">Categories</p>
                                    <CategoriesSelect
                                        v-model="currentCategory"
                                        :categories="categories.map((x) => x.id)"
                                    ></CategoriesSelect>
                                </div>
                            </div>
                        </div>
                    </div>
                    <template v-if="meta.isLoading">
                        <ProductSkeleton v-for="item in [0, 1, 2, 3, 4]"></ProductSkeleton>
                    </template>
                    <template v-else>
                        <div class="text-center pt-2 pb-4" v-if="!products.length">No Results Found</div>
                        <div class="row" :class="[showCards ? 'row-cols-4' : 'row-cols-1']">
                            <ProductCard
                                @select="(item) => addSelected(item)"
                                v-for="(product, index) in products"
                                :hasSelect="true"
                                :showAsColumn="showCards"
                                :index="index"
                                v-model="products[index]"
                                :addInternalProduct="true"
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
                    </template>
                </div>
                <div class="col-3">
                    <OrderSummary v-model="quote" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
