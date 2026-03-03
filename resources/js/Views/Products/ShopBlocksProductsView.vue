<script setup>
import _ from 'lodash';
import queryString from 'query-string';
import { Paginator, Select } from 'primevue';
import { quote } from '@/Composables/useQuote';
import { categories } from '@composables/custom/shopblocks';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import ShopBlocksStorages from '@/Components/ShopBlocks/ShopBlocksStorages.vue';
import StepActionBar from '@/Components/Steps/StepActionBar.vue';

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

const selectedItems = ref([]);
const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 12,
    isLoading: false
});
const search = ref(null);
const showCards = ref(false);

const productsHardware = ref([]);

const filters = reactive({
    model: {
        operator: 'in',
        value: null
    },
    name: {
        operator: 'contains',
        value: null
    }
});

const currentCategory = ref(null);
const currentBrand = ref([]);
const currentModel = ref([]);
const currentStorage = ref([]);

const filterList = reactive({
    models: [],
    storage: [
        { label: '1TB', value: '1TB' },
        { label: '512 GB', value: '512 GB' },
        { label: '256 GB', value: '256 GB' },
        { label: '128 GB', value: '128 GB' },
        { label: '64 GB', value: '64 GB' },
        { label: '32 GB', value: '32 GB' },
        { label: '16 GB', value: '16 GB' }
    ]
});

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

const sort = ref(sorts[3]);
const step = ref(props.modelValue + 1);

let abortController = null;

const getProductsShopBlocks = async () => {
    meta.isLoading = true;
    if (abortController) {
        abortController.abort();
    }
    abortController = new AbortController();

    let filters = [];

    //Hide out of stock products
    filters.push({
        column: 'stock',
        operator: 'greater_than_or_equal_to',
        value: 1
    });

    if (props.category) {
        filters.push({
            column: 'category.id',
            operator: 'equals',
            value: props.category
        });
    }

    if (currentCategory.value) {
        let array = [currentCategory.value];
        let items = [];

        do {
            let current = array.pop();
            items.push(current);
            let temp = categories.value.filter((x) => x.parent_id == current.id);
            array.push(...temp);
        } while (array.length != 0);

        filters.push({
            column: 'category.id',
            operator: 'in',
            value: items.map((x) => x.id)
        });
    }

    if (currentBrand.value.length) {
        filters.push({
            column: 'brand.id',
            operator: 'in',
            value: currentBrand.value.map((x) => x.id)
        });
    }

    if (currentModel.value.length) {
        filters.push({
            column: 'model.id',
            operator: 'in',
            value: currentModel.value.map((x) => x.id)
        });
    }

    let dimensions = currentStorage.value.length
        ? [
              {
                  column: 'internal-storage-capacity',
                  operator: 'in',
                  value: currentStorage.value.map((x) => x.value)
              }
          ]
        : [];

    let query = queryString.stringify({
        page: meta.page,
        perPage: meta.limit,
        filters: JSON.stringify(filters),
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
        filterList.models = res.data?.filters?.model
            ? Object.keys(res.data.filters.model)
                  .sort()
                  .reduce((acc, key) => {
                      acc[key] = res.data.filters.model[key];
                      return acc;
                  }, {})
            : [];
    } catch (error) {
        if (axios.isCancel?.(error) || error.name === 'CanceledError' || error.code === 'ERR_CANCELED') {
            // Request was cancelled, do nothing
        } else {
            console.error(error);
        }
    }
};

const addSelected = (item) => {
    if (selectedItems.value.length >= 4) {
        notyf.error('Comparator is full');
        return;
    }
    if (selectedItems.value.find((x) => x.sku == item.value.sku)) {
        notyf.error('Already added');
        return;
    }

    selectedItems.value.push(item.value);
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

watchEffect(() => {
    actionBarButtons.value = [
        step.value !== 1
            ? {
                  btn: 'back',
                  onClick: () => proxyValue.value--
              }
            : null,
        {
            btn: 'next',
            onClick: () => emit('save')
        }
    ].filter(Boolean);
});

watch([filters, sort], () => {
    getProductsShopBlocks();
});

watch(
    [currentCategory, currentBrand, currentModel, currentStorage],
    _.debounce(() => {
        meta.page = 1;
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
        <div class="row">
            <div class="d-flex justify-content-between mb-3">
                <h3>{{ quote.solution_type.code == 4 ? 'Handsets' : 'Hardware' }}</h3>
                <p class="bigger text-primary bold">
                    STEP
                    <span id="current-step">{{ step }}</span>
                    /
                    <span id="last-step">{{ step }}</span>
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
                <div class="d-flex justify-content-between align-items-end mb-3">
                    <div class="d-flex flex-fill justify-content-start">
                        <!--                        <button-->
                        <!--                            class="btn border"-->
                        <!--                            :class="[showCards ? 'btn-dark' : '']"-->
                        <!--                            @click="() => (showCards = !showCards)"-->
                        <!--                            style="border-radius: 5px 0 0 5px"-->
                        <!--                        >-->
                        <!--                            <font-awesome-icon :icon="['fas', 'grip']"></font-awesome-icon>-->
                        <!--                        </button>-->
                        <!--                        <button-->
                        <!--                            class="btn border"-->
                        <!--                            :class="[showCards ? '' : 'btn-dark']"-->
                        <!--                            @click="() => (showCards = !showCards)"-->
                        <!--                            style="border-radius: 0 5px 5px 0"-->
                        <!--                        >-->
                        <!--                            <font-awesome-icon :icon="['fas', 'grip-lines']"></font-awesome-icon>-->
                        <!--                        </button>-->
                    </div>
                    <div class="d-flex justify-content-end text-nowrap flex-shrink-0">
                        <span class="me-2 align-self-center">Sort by</span>
                        <Select v-model="sort" :options="sorts" optionLabel="label" placeholder="All"> </Select>
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
                    <ul class="d-flex list-unstyled">
                        <li>
                            <Badge v-for="(brand, index) in currentBrand" :value="brand.name">
                                <template #close-button>
                                    <font-awesome-icon
                                        :icon="['fas', 'x']"
                                        role="button"
                                        class="me-1 fs-7"
                                        @click="() => currentBrand.splice(index, 1)"
                                    />
                                </template>
                            </Badge>
                            <Badge v-for="(model, index) in currentModel" :value="model.name">
                                <template #close-button>
                                    <font-awesome-icon
                                        :icon="['fas', 'x']"
                                        role="button"
                                        class="me-1 fs-7"
                                        @click="() => currentModel.splice(index, 1)"
                                    />
                                </template>
                            </Badge>
                            <Badge v-for="(model, index) in currentStorage" :value="model.value">
                                <template #close-button>
                                    <font-awesome-icon
                                        :icon="['fas', 'x']"
                                        role="button"
                                        class="me-1 fs-7"
                                        @click="() => currentStorage.splice(index, 1)"
                                    />
                                </template>
                            </Badge>
                        </li>
                    </ul>

                    <div class="mb-4">
                        <h2>Categories</h2>
                        <ShopBlocksCategoriesTree
                            v-model="currentCategory"
                            :defaultCategory="category"
                        ></ShopBlocksCategoriesTree>
                    </div>
                    <div class="mb-4">
                        <h2>Brands</h2>
                        <ShopBlocksBrands v-model="currentBrand" :category="currentCategory"></ShopBlocksBrands>
                    </div>

                    <div v-if="!category > 0" class="mb-4">
                        <h2>Models</h2>
                        <ShopBlocksModels v-model="currentModel" :category="currentCategory"></ShopBlocksModels>
                    </div>

                    <div class="mb-4">
                        <h2>Storage</h2>
                        <ShopBlocksStorages v-model="currentStorage" :category="currentCategory"></ShopBlocksStorages>
                    </div>
                </div>
            </div>
            <div class="col">
                <template v-if="meta.isLoading">
                    <ShopblocksProductSkeleton v-for="item in [0, 1, 2, 3, 4]"></ShopblocksProductSkeleton
                ></template>
                <template v-else>
                    <div class="text-center pt-2 pb-4" v-if="!productsHardware.length">No Results Found</div>
                    <div class="row" :class="[showCards ? 'row-cols-4' : 'row-cols-1']">
                        <ShopBlocksProductCard
                            v-for="(product, index) in productsHardware"
                            @select="(item) => addSelected(item)"
                            :hasSelect="true"
                            :showAsColumn="showCards"
                            :index="index"
                            v-model="productsHardware[index]"
                        >
                        </ShopBlocksProductCard>
                    </div>
                    <Paginator
                        v-model:first="meta.first"
                        :rows="meta.limit"
                        :totalRecords="meta.total"
                        :rowsPerPageOptions="[12, 24, 48]"
                        @page="handlePaginator"
                    >
                    </Paginator
                ></template>
            </div>
        </div>
        <ProductCompareShopblocks v-if="selectedItems.length" v-model="selectedItems"></ProductCompareShopblocks>
    </div>
</template>
<style></style>
