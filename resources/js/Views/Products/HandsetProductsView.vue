<script setup>
import _ from 'lodash';
import queryString from 'query-string';
import Select from 'primevue/select';
import Paginator from 'primevue/paginator';
import { quote } from '@/Composables/useQuote';
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
    quote: Object
});

const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 6,
    isLoading: false
});
const showCards = ref(false);
const search = ref(null);
const productsHardware = ref([]);

const filters = reactive({
    brands: [],
    storage: null,
    name: null
});

// const storage = [
//     { label: '1TB', value: '1TB' },
//     { label: '512 GB', value: '512 GB' },
//     { label: '256 GB', value: '256 GB' },
//     { label: '128 GB', value: '128 GB' },
//     { label: '64 GB', value: '64 GB' },
//     { label: '32 GB', value: '32 GB' },
//     { label: '16 GB', value: '16 GB' }
// ];
const storage = ref([]);

const sorts = [
    {
        label: 'Lowest Price',
        order: 'asc',
        orderBy: 'price.net'
    },
    {
        label: 'Highest Price',
        order: 'desc',
        orderBy: 'price.net'
    }
];

const selectedItems = ref([]);
const sort = ref(sorts[1]);
const step = ref(props.modelValue + 1);

const getDimensionStorage = async () => {
    let query = queryString.stringify({
        limit: 1,
        filters: JSON.stringify([
            { column: 'key', operator: 'contains', value: 'internal-storage-capacity' },
            { column: 'group', operator: 'equals', value: 'Storage' }
        ])
    });

    let res = await axios.get('/api/shop-blocks/dimension-types?' + query);

    if (!res.data?.data?.length) {
        return;
    }

    query = queryString.stringify({
        limit: 200,
        filters: JSON.stringify([
            { column: 'dimension_type_id', operator: 'equals', value: res.data.data[0].id },
            { column: 'products.category.code', operator: 'equals', value: 22 }
        ])
    });

    res = await axios.get('/api/shop-blocks/dimensions?' + query);
    storage.value = res.data.data;
};

const getProductsShopBlocks = async () => {
    meta.isLoading = true;
    let temp = [];
    let dims = [];

    //Hide out of stock products
    temp.push({
        column: 'stock',
        operator: 'greater_than_or_equal_to',
        value: 1
    });

    if (filters.brands.length) {
        temp.push({
            column: 'brand_id',
            operator: 'in',
            value: filters.brands.map((x) => x.id).join(',')
        });
    }

    if (filters.storage) {
        dims.push({
            column: 'internal-storage-capacity',
            operator: 'contains',
            value: filters.storage
        });
    }

    temp.push({
        column: 'category.code',
        operator: 'equals',
        value: 22
    });

    let query = queryString.stringify({
        page: meta.page,
        limit: meta.limit,
        filters: JSON.stringify(temp),
        dimensions: JSON.stringify(dims),
        order: sort.value.order,
        orderBy: sort.value.orderBy,
        columns: ['name,description,model.name'],
        search: search.value,
        account_id: quote.account_id
    });

    let res = await axios.get('/api/shop-blocks/products?' + query);
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
};

const clearFilters = () => {
    Object.keys(filters).forEach((key) => (filters[key] = null));
    filters.brands = [];
};

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    getProductsShopBlocks();
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

onMounted(async () => {
    await getProductsShopBlocks();
});

watch([filters, sort], () => {
    getProductsShopBlocks();
});

watch(
    search,
    _.debounce(() => {
        getProductsShopBlocks();
    }, 300)
);
getDimensionStorage();
</script>
<template>
    <div id="mobile_step_shopblocks" class="step">
        <div class="d-flex justify-content-between mb-3">
            <h3 class="mb-0">{{ step }}. Handsets</h3>
            <p class="bigger text-primary bold">
                STEP
                <span id="current-step">{{ step }}</span>
                /
                <span id="last-step">{{ step }}</span>
            </p>
        </div>
        <div class="d-flex justify-content-between mb-3">
            <div class="d-flex justify-content-end">
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
            <div class="d-flex">
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Search Handsets" v-model="search" />
                    <button class="btn btn-primary" type="submit" id="button-addon2">
                        <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                    </button>
                </div>
            </div>
        </div>
        <div class="panel mb-3">
            <div class="d-flex justify-content-between mb-2">
                <div class="d-flex">
                    <h4>Filters ({{ meta.total }})</h4>
                </div>
                <div>
                    <button @click="clearFilters()" class="btn border-0 p-0 text-primary" type="button">
                        Clear filters
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <label class="mb-1">Brands</label>
                    <InputSelectMultiple
                        id="product-brands"
                        url="/shop-blocks/brands"
                        :fields="['name']"
                        :query="[{ column: 'products.category_id', operator: 'equals', value: 22 }]"
                        v-model="filters.brands"
                        placeholder="All"
                    >
                    </InputSelectMultiple>
                </div>
                <div class="col-4">
                    <label class="mb-1">Storage</label>
                    <Select
                        v-model="filters.storage"
                        :options="storage"
                        showClear
                        optionLabel="value"
                        optionValue="value"
                        placeholder="All"
                        class="w-100"
                    >
                    </Select>
                </div>
                <div class="col-4">
                    <label class="mb-1">Sort by</label>
                    <Select v-model="sort" :options="sorts" optionLabel="label" placeholder="All" class="w-100">
                    </Select>
                </div>
            </div>
        </div>
        <div v-if="!meta.isLoading">
            <div class="text-center pt-2 pb-4" v-if="!productsHardware.length">No Results Found</div>
            <div class="row" :class="[showCards ? 'row-cols-3' : 'row-cols-1']">
                <ProductCard
                    v-for="(product, index) in productsHardware"
                    @select="(item) => addSelected(item)"
                    :hasSelect="true"
                    :showAsColumn="showCards"
                    :index="index"
                    v-model="productsHardware[index]"
                >
                </ProductCard>
            </div>
        </div>
        <div v-else>
            <ProductSkeleton v-for="n in meta.limit"></ProductSkeleton>
        </div>
        <Paginator
            v-model:first="meta.first"
            :rows="meta.limit"
            :totalRecords="meta.total"
            :rowsPerPageOptions="[6, 12, 24]"
            @page="handlePaginator"
        >
        </Paginator>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <button
                    v-if="step > 1"
                    id="btn-step-previous"
                    class="btn text-primary step-nav btn-sm me-3"
                    type="button"
                    @click="() => proxyValue--"
                >
                    <font-awesome-icon :icon="['fas', 'arrow-left']" /> Previous
                </button>
            </div>
            <div>
                <button id="btn-step-next" class="ms-3 btn btn-primary step-nav" type="button" @click="emit('save')">
                    Next
                    <font-awesome-icon :icon="['fas', 'arrow-right']" />
                </button>
            </div>
        </div>

        <ProductCompareShopblocks v-if="selectedItems.length" v-model="selectedItems"></ProductCompareShopblocks>
    </div>
</template>
<style scoped>
.truncate-1-line {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em; /* or a fixed px value, e.g. 24px */
    height: calc(1.5em * 1); /* 3 lines × line-height */
}

.truncate-2-lines {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 2em; /* or a fixed px value, e.g. 24px */
    height: calc(2em * 2); /* 3 lines × line-height */
    font-size: 14px !important;
}
</style>
