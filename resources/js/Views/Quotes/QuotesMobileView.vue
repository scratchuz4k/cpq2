<script setup>
import _ from "lodash";
import Select from "primevue/select";
import MultiSelect from "primevue/multiselect";
import Paginator from "primevue/paginator";
import { quote, setQuote, getQuote } from "@composables/useQuote";
import {
    updateQuoteItem,
    removeQuoteItem,
    createQuoteItem,
} from "@composables/useQuoteItem";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { onMounted, popScopeId, reactive, ref, watch } from "vue";
import queryString from "query-string";
import InputQuantity from "@/Components/InputQuantity.vue";
const props = defineProps({
    modelValue: Object,
});

const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 5,
    isLoading: false,
});

const solution_type = props.modelValue.solution_type
const active_categories = ref([]);
const category_total_products = ref(0);

const getCategoriesWithProducts = async () => {
    let query = queryString.stringify({
        limit: 200,
        filters: JSON.stringify([
            {
                column: "id",
                operator: "in",
                value: solution_type.categories.map((x) => x.id).join(",")
            },
            {
                column: "products.id",
                operator: "not_equals",
                value: "",
            },
        ]),
    });
    let res = await axios.get("/api/categories" + "?" + query);

    const categories_order = solution_type.categories.map((x) => x.id);

    active_categories.value = res.data.data.sort((a, b) => {
        return categories_order.indexOf(a.id) - categories_order.indexOf(b.id);
    });
};

const urlParams = new URLSearchParams(window.location.search);
const params = new Proxy(urlParams, {
    get: (searchParams, prop) => searchParams.get(prop),
});
const step = ref(params["step"] ?? 0);

const search = ref(null);

const products = ref([]);
const productsHardware = ref([]);
setQuote(props.modelValue);

const filters = reactive({
    model: {
        operator: "in",
        value: null,
    },
    name: {
        operator: "contains",
        value: null,
    },
});

const filterList = reactive({
    models: [],
    storage: [
        { label: "1TB", value: "1TB" },
        { label: "512 GB", value: "512 GB" },
        { label: "256 GB", value: "256 GB" },
        { label: "128 GB", value: "128 GB" },
        { label: "64 GB", value: "64 GB" },
        { label: "32 GB", value: "32 GB" },
        { label: "16 GB", value: "16 GB" },
    ],
});

const sorts = [
    {
        label: "Lowest Price",
        order: "asc",
        orderBy: "price.net",
    },
    {
        label: "Highest Price",
        order: "desc",
        orderBy: "price.net",
    },
];

const sort = ref(sorts[1]);

const getProductsShopBlocks = async () => {
    meta.isLoading = true;
    let temp = [];
    Object.keys(filters).forEach((key) => {
        temp.push({
            column: key,
            operator: filters[key].operator,
            value: filters[key].value ?? null,
        });
    });
    let query = {
        page: meta.page,
        perPage: meta.limit,
        filters: temp,
        order: sort.value.order,
        orderBy: sort.value.orderBy,
        search: search.value,
        account_id: props.modelValue.account_id,
    };
    let res = await axios.post("/api/external/get-articles-from-shop-blocks", query);
    productsHardware.value = res.data.data;
    productsHardware.value.forEach((e) => {
        let temp = props.modelValue.items.find((x) => x.product_code == e.code);
        e.quantity = temp ? temp.quantity : 0;
        e.contract_length = 1;
    });

    meta.first = (meta.page - 1) * meta.limit;
    meta.total = res.data.total;
    meta.overallTotal = res.data.overall_total;
    meta.isLoading = false;
    filterList.models = Object.keys(res.data.filters.model).sort().reduce((acc, key) => {
        acc[key] = res.data.filters.model[key];
        return acc;
    }, {})
};

const clearFilters = () => {
    Object.keys(filters).forEach((key) => (filters[key].value = null));
};

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    getProductsShopBlocks();
};

const checkQuantity = _.debounce(async (product, temp) => {
    if (product.stock <= temp) {
        temp = product.stock;
    }
    product.quantity = temp;
    if (product.quantity == 0) {
        return;
    }

    let item = quote.items.find((x) => x.product_code == product.code);
    if (!item) {
        createQuoteItem({
            quote_id: props.modelValue.id,
            product_code: product.code,
            product_id: product.id,
            quantity: product.quantity,
            contract_length: product.contract_length,
            name: product.name,
            raw_price: product.price ? product.price.net : 0,
            price: product.price ? product.price.net_spa : 0,
            price_plan_id: product.price_plan_id,
        });
    } else {
        if (product.quantity == 0) {
            removeQuoteItem(item.id);
        } else {
            updateQuoteItem(item.id, { quantity: product.quantity });
        }
    }

    getQuote(quote.id);
}, 200);

const saveQuote = async () => {
    if (step.value == active_categories.value.length) {
        router.get(`/quotes/${props.modelValue.id}`);
        return;
    }
    step.value++;
};

const getHardwareProductsCount = () => {
    let items = [...quote.items];
    return items
        .filter((x) => !x.product_id)
        .reduce((acc, i) => (acc += i.quantity), 0);
};

getCategoriesWithProducts();

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
</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
            <div class="col-lg-7">
                STEP {{ step }}
                <div v-for="(category, index) in active_categories">
                    <CategoryProductsView :category="category" :account="props.modelValue.account" v-model="step"
                        :index="index" :total_categories="active_categories.length + 1">
                    </CategoryProductsView>
                </div>

                <div v-if="step == active_categories.length" id="mobile_step_shopblocks" class="step">
                    <div class="d-flex justify-content-between mb-4">
                        <h2 class="m-0">Finally, choose your add-ons</h2>
                        <p class="bigger text-primary bold">STEP {{ active_categories.length + 1 }}/{{
                            active_categories.length + 1 }}</p>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <h3>Handsets</h3>
                        <div>
                            <div class="input-group">
                                <input type="search" class="form-control" placeholder="Search Handsets"
                                    v-model="search" />
                                <button class="btn btn-primary" type="submit" id="button-addon2">
                                    <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex">
                                <h4>Filters</h4>
                                <ul class="mb-0" style="list-style: none; display: flex; flex-wrap: wrap">
                                    <template v-for="filter in filters">
                                        <li class="bullet me-2 mb-2" v-for="(item, index) in filter.value">
                                            <p class="small">
                                                {{ item }}
                                                <span @click="() => filter.value.splice(index, 1)"
                                                    style="cursor: pointer">
                                                    <font-awesome-icon :icon="['fas', 'xmark']" /></span>
                                            </p>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div>
                                <button @click="clearFilters()" class="btn border-0 p-0 text-primary" type="button">
                                    Clear filters
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <label class="mb-1">Models</label>
                                <MultiSelect v-model="filters.model.value" :options="Object.keys(filterList.models)"
                                    placeholder="All" filter class="w-100">
                                </MultiSelect>
                            </div>
                            <div class="col-4">
                                <label class="mb-1">Storage</label>
                                <MultiSelect v-model="filters.name.value" :options="filterList.storage"
                                    optionLabel="label" optionValue="value" placeholder="All" filter class="w-100">
                                </MultiSelect>
                            </div>
                            <div class="col-4">
                                <label class="mb-1">Sort by</label>
                                <Select v-model="sort" :options="sorts" optionLabel="label" placeholder="All"
                                    class="w-100">
                                </Select>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-2 pb-4" v-if="!productsHardware.length">
                        No Results Found
                    </div>
                    <div v-for="(product, index) in productsHardware" class="panel p-4 mb-3">
                        <div class="row">
                            <div class="col-2">
                                <a :href="product.url" target="_blank">
                                    <img :src="product.primary_image" style="width: 100%; object-fit: contain" />
                                </a>
                            </div>
                            <div class="col-10">
                                <div class="d-flex w-100 justify-content-between">
                                    <p class="big bold">{{ product.model }}</p>
                                    <div class="d-flex justify-content-end">
                                        <div class="me-3">
                                            <p v-if="product.stock > 0" class="badge text-bg-warning"
                                                style="--bs-bg-opacity: 0.5">
                                                <span :id="'hardware-' + index + '-stock'">
                                                    {{ product.stock }}
                                                </span>
                                                in stock
                                            </p>
                                            <p v-else class="badge text-bg-danger" style="--bs-bg-opacity: 0.5">
                                                <font-awesome-icon :icon="['fa', 'warning']" />
                                                <span :id="'hardware-' + index + '-stock'"></span>
                                                OUT OF STOCK
                                            </p>
                                        </div>
                                        <div>
                                            <p class="bold big">{{ GBP.format(product.price?.net_spa || 0) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <p class="small">{{ product.sku }} - {{ product.name }}</p>
                                <div class="pt-3">
                                    <p style="font-weight: 500">Quantity</p>
                                    <div class="d-flex align-items-center mb-2">
                                        <InputQuantity :id="'hardware-' + index" v-model="product.quantity"
                                            @change="(val) => checkQuantity(product, val, true)" />

                                        <button :id="'btn-hardware-' + index + '-add-25'"
                                            class="btn btn-sm ms-4 btn-add-custom-quantity"
                                            @click="checkQuantity(product, product.quantity + 25, true)">
                                            +25
                                        </button>
                                        <button :id="'btn-hardware-' + index + '-add-50'"
                                            class="btn btn-sm ms-2 btn-add-custom-quantity"
                                            @click="checkQuantity(product, product.quantity + 50, true)">
                                            +50
                                        </button>
                                        <button :id="'btn-hardware-' + index + '-add-100'"
                                            class="btn btn-sm ms-2 btn-add-custom-quantity"
                                            @click="checkQuantity(product, product.quantity + 100, true)">
                                            +100
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Paginator v-model:first="meta.first" :rows="meta.limit" :totalRecords="meta.total"
                        :rowsPerPageOptions="[5, 10, 20]" @page="handlePaginator">
                    </Paginator>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <button v-if="active_categories.length > 0" id="btn-step-2-previous"
                                class="btn text-primary step-nav btn-sm me-3" type="button" @click="() => step--">
                                <font-awesome-icon :icon="['fas', 'arrow-left']" /> Previous
                            </button>
                        </div>
                        <div>
                            <span id="total_add_ons">
                                Add
                                {{productsHardware.reduce((acc, item) => (acc += item.quantity), 0)}}
                                handsets
                            </span>
                            <button id="btn-step-2-next" class="ms-3 btn btn-primary step-nav" type="button"
                                @click="saveQuote()">
                                Next
                                <font-awesome-icon :icon="['fas', 'arrow-right']" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 order-first order-lg-last mb-5">
                <div style="
            position: sticky;
            top: 25px;
            max-width: 82%;
            margin-right: 0;
            margin-left: auto;
          ">
                    <InfoBox>
                        <div style="font-size: 14px">
                            <b style="font-size: 16px">Quote {{ modelValue.id }} for {{ modelValue.account.name }}</b>
                            <br />
                            Contact Number: 0124567890<br />
                            Email: firstname.lastname@gmail.com
                        </div>
                    </InfoBox>
                    <div class="configure-mobile-sidebar px-4 py-4">
                        <div v-for="(category, index) in active_categories">
                            <h4 id="sidebar_products" data-total="0">{{ index + 1 }}. {{ category.name }}</h4>
                            <div class="sidebar_items mb-3">
                                <div v-for="product in category.products" class="item mb-2">
                                    <span>{{ product.name }}</span>
                                    <span class="badge bg-light-grey dark">
                                        {{quote.items.find((x) => x.product_id == product.id)?.quantity ?? 0}}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <h4>{{ active_categories.length + 1 }}. Add-ons</h4>
                        <div class="sidebar_add_ons mb-3">
                            <div class="item mb-2">
                                <span>Hardware</span><span class="badge bg-light-grey dark">
                                    {{ getHardwareProductsCount() }}
                                </span>
                            </div>
                        </div>
                        <button class="btn btn-primary mt-2" @click="saveQuote()">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style></style>
