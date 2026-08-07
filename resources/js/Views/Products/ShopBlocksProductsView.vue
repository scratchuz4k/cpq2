<script setup>
import _ from 'lodash';
import queryString from 'qs';
import { Paginator, Select } from 'primevue';
import { quote } from '@/Composables/useQuote';
import { selectedItems, addSelected } from '@/Composables/useComparator';
import { categories } from '@composables/custom/shopblocks';
import { computed, onMounted, reactive, ref, watch } from 'vue';

const emit = defineEmits(['save', 'update:modelValue']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const props = defineProps({
    modelValue: Number,
    quote: Object,
    category: {
        type: Number,
        default: 0
    },
    current_step: Number
});

const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 12,
    isLoading: false
});

const filters = reactive({
    categories: [],
    brands: [],
    models: [],
    storage: []
});

if (quote.solution_type.code == 4) {
    filters.categories = [{ id: 22 }];
}

const search = ref(null);
const showCards = ref(false);
const currentStep = ref(props.current_step);
const productsHardware = ref([]);

const sorts = [
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

const sort = ref(sorts[0]);
const step = ref(props.modelValue + 1);

let abortController = null;

const getProductsShopBlocks = async () => {
    meta.isLoading = true;
    if (abortController) {
        abortController.abort();
    }
    abortController = new AbortController();

    let tempfilters = [];

    //Hide out of stock products
    tempfilters.push({
        column: 'stock',
        operator: 'greater_than_or_equal_to',
        value: 1
    });

    if (props.category) {
        tempfilters.push({
            column: 'category.id',
            operator: 'equals',
            value: props.category
        });
    }

    if (filters.categories.length) {
        let array = [...filters.categories];
        let items = [];

        do {
            let current = array.pop();
            items.push(current);
            let temp = categories.value.filter((x) => x.parent_id == current.id);
            array.push(...temp);
        } while (array.length != 0);

        tempfilters.push({
            column: 'category.id',
            operator: 'in',
            value: items.map((x) => x.id)
        });
    }

    if (filters.brands.length) {
        tempfilters.push({
            column: 'brand.id',
            operator: 'in',
            value: filters.brands.map((x) => x.id)
        });
    }

    if (filters.models.length) {
        tempfilters.push({
            column: 'model.id',
            operator: 'in',
            value: filters.models.map((x) => x.id)
        });
    }

    let dimensions = filters.storage.length
        ? [
              {
                  column: 'internal-storage-capacity',
                  operator: 'in',
                  value: filters.storage.map((x) => x.value)
              }
          ]
        : [];

    let query = queryString.stringify({
        page: meta.page,
        limit: meta.limit,
        filters: tempfilters,
        order: sort.value.order,
        orderBy: sort.value.orderBy,
        columns: 'description,name',
        search: search.value,
        account_id: quote.account_id,
        dimensions: JSON.stringify(dimensions)
    });
    try {
        let res = await axios.get('/api/shop-blocks/products?' + query, {
            signal: abortController.signal
        });
        productsHardware.value = res.data.data;
        productsHardware.value.forEach((e) => {
            let temp = quote.items.find((x) => x.product_code == e.code);
            e.quantity = temp ? temp.quantity : 0;
            e.contract_length = 1;
        });

        meta.first = (meta.page - 1) * meta.limit;
        meta.total = res.data.total;
        meta.overallTotal = res.data.overall_total;
        meta.isLoading = false;
    } catch (error) {
        if (axios.isCancel?.(error) || error.name === 'CanceledError' || error.code === 'ERR_CANCELED') {
            // Request was cancelled, do nothing
        } else {
            console.error(error);
        }
    }
};

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    getProductsShopBlocks();
};

onMounted(async () => {
    await getProductsShopBlocks();
});

import { actionBarButtons } from '@/Composables/useQuoteSteps.js';
import { watchEffect, onUnmounted } from 'vue';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';
import OrderSummary from '@/Components/OrderSummary.vue';

const changeStep = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    proxyValue.value--;
};

watchEffect(() => {
    actionBarButtons.value = [
        step.value !== 1
            ? {
                  btn: 'back',
                  onClick: changeStep
              }
            : null,
        {
            btn: 'next',
            onClick: () => emit('save')
        }
    ].filter(Boolean);
});

watch(
    [filters, sort],
    _.debounce(() => {
        meta.page = 1;
        if (quote.solution_type.code == 4) {
            if (!filters.categories.find((x) => x.id === 22)) {
                filters.categories = [{ id: 22 }];
            }
        }

        getProductsShopBlocks();
    }, 500),
    { deep: true }
);

watch(
    search,
    _.debounce(() => {
        if (search.value) {
            meta.page = 1;
        }
        getProductsShopBlocks();
    }, 500)
);
</script>
<template>
    <div id="mobile_step_shopblocks" class="step">
        <div class="row mb-3">
            <div class="col-9 d-flex justify-content-between align-items-center">
                <!-- {{ filters.categories }} -->
                <h3 class="mb-0">{{ step }}. {{ quote.solution_type.code == 4 ? 'Handsets' : 'Hardware' }}</h3>
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
                        :options="sorts"
                        optionLabel="label"
                        placeholder="All"
                    >
                    </Select>
                </div>
            </div>
            <div class="col-3 d-flex justify-content-end align-items-center">
                <p class="bigger text-primary bold">
                    STEP
                    <span id="current-step">{{ step }}</span>
                    /
                    <span id="last-step">{{ step }}</span>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-9">
                <ShopBlocksFilters
                    v-model="filters"
                    :filters="
                        quote.solution_type.code == 4
                            ? ['brands', 'models', 'storage']
                            : ['categories', 'brands', 'models', 'storage']
                    "
                ></ShopBlocksFilters>
                <template v-if="meta.isLoading">
                    <ShopblocksProductSkeleton v-for="item in meta.limit || 6"></ShopblocksProductSkeleton>
                </template>
                <template v-else>
                    <div class="text-center pt-2 pb-4" v-if="!productsHardware.length">No Results Found</div>
                    <div class="row" :class="[showCards ? 'row-cols-4' : 'row-cols-1']">
                        <ProductCard
                            v-for="(product, index) in productsHardware"
                            @select="(item) => addSelected(item, true)"
                            :hasSelect="true"
                            :showAsColumn="showCards"
                            :index="index"
                            v-model="productsHardware[index]"
                            :showFeatures="true"
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
                <OrderSummary />
            </div>
        </div>
    </div>
</template>
<style scoped></style>
