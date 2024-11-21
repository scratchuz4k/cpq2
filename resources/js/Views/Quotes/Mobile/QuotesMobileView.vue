<script setup>
import _ from "lodash";
import Select from 'primevue/select';
import MultiSelect from 'primevue/multiselect';
import Paginator from 'primevue/paginator';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue'
import queryString from 'query-string';
import InputQuantity from '@/Components/InputQuantity.vue';

const props = defineProps({
    modelValue: Object,
})

const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 5,
    isLoading: false
})

const urlParams = new URLSearchParams(window.location.search)
const params = new Proxy(urlParams, {
    get: (searchParams, prop) => searchParams.get(prop),
})
const step = ref(params["step"] ?? 1)

const search = ref(null)

const products = ref([])
const productsHardware = ref([])
const quote = reactive({
    items: props.modelValue.items,
    new: []
})

const filters = reactive({
    model: {
        operator: "in",
        value: null
    },
    name: {
        operator: "contains",
        value: null
    },
})

const filterList = reactive({
    models: [],
    storage: [
        { label: '1TB', value: '1TB' },
        { label: '512 GB', value: '512 GB' },
        { label: '256 GB', value: '256 GB' },
        { label: '128 GB', value: '128 GB' },
        { label: '64 GB', value: '64 GB' },
        { label: '32 GB', value: '32 GB' },
        { label: '16 GB', value: '16 GB' },
    ],
})

const sorts = [
    {
        label: "Lowet Price",
        order: "asc",
        orderBy: "price.net",
    },
    {
        label: "Hightest Price",
        order: "desc",
        orderBy: "price.net",
    }
]

const sort = ref(sorts[1])

const getProducts = async () => {
    let query = queryString.stringify({
        limit: 100,
        filters: JSON.stringify([{
            column: 'categories.code',
            operator: 'equals',
            value: 'plan'
        }]),
        account_id: props.modelValue.account_id
    })
    let res = await axios.get("/api/products" + '?' + query)
    res.data.data.forEach((e) => {
        let temp = props.modelValue.items.find(x => x.product_code == e.code)
        e.quantity = temp ? temp.quantity : 0
        e.contract_length = 12
    });
    products.value = res.data.data
}

const getProductsShopBlocks = async () => {
    meta.isLoading = true
    let temp = []
    Object.keys(filters).forEach((key) => {
        temp.push({ column: key, operator: filters[key].operator, value: filters[key].value ?? null })
    })
    let query = {
        page: meta.page,
        perPage: meta.limit,
        filters: temp,
        order: sort.value.order,
        orderBy: sort.value.orderBy,
        search: search.value,
        account_id: props.modelValue.account_id
    };
    let res = await axios.post("/api/external/get-articles-from-shop-blocks", query)
    productsHardware.value = res.data.data
    productsHardware.value.forEach(e => {
        let temp = props.modelValue.items.find(x => x.product_code == e.code)
        e.quantity = temp ? temp.quantity : 0
        e.contract_length = 1
    });

    meta.first = (meta.page - 1) * meta.limit
    meta.total = res.data.total
    meta.overallTotal = res.data.overall_total
    meta.isLoading = false
    filterList.models = res.data.filters.model
}

const clearFilters = () => {
    Object.keys(filters).forEach((key) => filters[key].value = null)
}

const handlePaginator = (ev) => {
    meta.limit = ev.rows
    meta.page = ev.page + 1
    getProductsShopBlocks()
}

const checkQuantity = async (product, temp, hardwareCheck = false) => {
    let oldQty = product.quantity
    if (product.stock <= temp) {
        temp = product.stock
    }

    product.quantity = temp

    if (checkPlansHardwareQuantity() <= 0 && hardwareCheck) {
        let result = await swal.fire({
            title: 'Quantity exceeds plans limit',
            text: 'Your hardware quantity exceeds plans limit'
        })

        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            product.quantity = oldQty
        } else {
            product.quantity += checkPlansHardwareQuantity()
        }
    }

    let indexItems = quote.items.findIndex(x => x.product_code == product.code)
    let indexNew = quote.new.findIndex(x => x.code == product.code)

    if (indexItems == -1 && indexNew == -1) {
        quote.new.push(product)
        return
    }

    if (indexItems != -1) {
        quote.items[indexItems].quantity = product.quantity
        return
    }

    if (indexNew != -1) {
        quote.new[indexNew].quantity = product.quantity
        return
    }
}

const saveQuote = async () => {
    let items = []
    quote.items.filter(x => x.quantity).forEach((x) => { items.push(x) })

    quote.new.forEach((x) => {
        if (!x.quantity) {
            return
        }
        items.push({
            product_code: x.code,
            product_id: x.id,
            quantity: x.quantity,
            contract_length: x.contract_length,
            name: x.name,
            raw_price: x.price ? x.price.net : 0,
            price: x.price ? x.price.net_spa : 0,
            price_plan_id: x.price ? x.price.price_plan_id : null,
        })
    })

    try {
        await axios.put(`/api/quotes/${props.modelValue.id}`, {
            items: items
        })
        router.get(`/quotes/${props.modelValue.id}`)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const getHardwareProductsCount = () => {
    let codes = products.value.map(x => x.code);
    let items = [...quote.items, ...quote.new]
    return items.filter(x => !(codes.includes(x.product_code) || codes.includes(x.code))).reduce((acc, i) => acc += i.quantity, 0)
}

const checkPlansHardwareQuantity = () => {
    let plansQty = products.value.reduce((acc, x) => acc += x.quantity, 0)
    let hardwareQty = getHardwareProductsCount()

    return plansQty - hardwareQty
}

getProductsShopBlocks()
getProducts()

watch([filters, sort], () => {
    getProductsShopBlocks()
})

watch(search, _.debounce(() => {
    getProductsShopBlocks()
}, 300))


const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Create a quote' },
]);


</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
            <div class="col-lg-7">
                <div v-if="step == 1" id="mobile_step1" class="step">
                    <div class="d-flex justify-content-between mb-3">
                        <h2>Select some products to get started</h2>
                        <p class="bigger bold text-primary">STEP 1/2</p>
                    </div>

                    <div v-for="(product, index) in products" class="mobile-item mobile-product mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-center mb-2">
                                <h4 class="m-0">{{ product.name }}</h4>
                                <p class="m-0 ms-2">
                                    {{ product.description }} /
                                    {{ GBP.format(product.price.net_spa) }}
                                </p>
                            </div>
                            <font-awesome-icon :icon="['fa', 'circle-info']" />
                        </div>
                        <div class="mobile-product-quantity">
                            <label>Quantity</label>
                            <div class="d-flex align-items-center pb-3">
                                <InputQuantity :id="'bolt-' + index" v-model="product.quantity"
                                    @change="(ev) => checkQuantity(product, ev)" />
                                <button :id="'btn-plan-' + index + '-add-25'" type=" button"
                                    class="btn btn-sm ms-4 btn-add-custom-quantity"
                                    @click="checkQuantity(product, product.quantity + 25)">+25</button>
                                <button :id="'btn-plan-' + index + '-add-50'" type=" button"
                                    class="btn btn-sm mx-2 btn-add-custom-quantity"
                                    @click="checkQuantity(product, product.quantity + 50)">+50</button>
                                <button :id="'btn-plan-' + index + '-add-100'" type=" button"
                                    class="btn btn-sm btn-add-custom-quantity"
                                    @click="checkQuantity(product, product.quantity + 100)">+100</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center">
                        <span id="total_mobile_users">
                            Add {{ products.reduce((acc, item) => acc += item.quantity, 0) }}
                            mobile users
                        </span>
                        <button :id="'btn-step-1-next'" class="ms-3 btn btn-primary step-nav" type="button"
                            @click="() => step++">Next
                            <font-awesome-icon :icon="['fas', 'arrow-right']" />
                        </button>
                    </div>
                </div>
                <div v-if="step == 2" id="mobile_step3" class="step">
                    <div class="d-flex justify-content-between mb-4">
                        <h2 class="m-0">Finally, choose your add-ons</h2>
                        <p class="bigger text-primary bold">STEP 2/2</p>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <h3>Handsets</h3>
                        <div>
                            <div class="input-group">
                                <input type="search" class="form-control" placeholder="Search Handsets"
                                    v-model="search">
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
                                <ul class="mb-0" style="list-style: none; display: flex; flex-wrap: wrap;">
                                    <template v-for="filter in filters">
                                        <li class="bullet me-2 mb-2" v-for="(item, index) in filter.value">
                                            <p class="small">{{ item }}
                                                <span @click="() => filter.value.splice(index, 1)"
                                                    style="cursor: pointer;"> <font-awesome-icon
                                                        :icon="['fas', 'xmark']" /></span>
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

                    <div class="text-center pt-2 pb-4" v-if="!productsHardware.length"> No Results Found</div>
                    <div v-for="(product, index) in productsHardware" class="panel p-4 mb-3">
                        <div class="row">
                            <div class="col-2">
                                <a :href="product.url" target="_blank">
                                    <img :src="product.primary_image" style="width: 100%;object-fit: contain;">
                                </a>

                            </div>
                            <div class="col-10">
                                <div class="d-flex w-100 justify-content-between">
                                    <p class="big bold">{{ product.model }}</p>
                                    <div class="d-flex justify-content-end">
                                        <div class="me-3">
                                            <p v-if="product.stock > 0" class="badge text-bg-warning"
                                                style="--bs-bg-opacity: .5;">
                                                <span :id="'hardware-' + index + '-stock'">
                                                    {{ product.stock }}
                                                </span> in stock
                                            </p>
                                            <p v-else class="badge text-bg-danger" style="--bs-bg-opacity: .5;">
                                                <font-awesome-icon :icon="['fa', 'warning']" />
                                                <span :id="'hardware-' + index + '-stock'"></span>
                                                OUT OF STOCK
                                            </p>
                                        </div>
                                        <div>
                                            <p class="bold big"> {{ GBP.format(product.price.net_spa) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <p class="small">
                                    {{ product.sku }} - {{ product.name }}</p>
                                <div class="pt-3">
                                    <p style="font-weight: 500;">Quantity</p>
                                    <div class="d-flex align-items-center mb-2">
                                        <InputQuantity :id="'hardware-' + index" v-model="product.quantity"
                                            :disabled="checkPlansHardwareQuantity() <= 0"
                                            @change="(val) => checkQuantity(product, val, true)" />

                                        <button :id="'btn-hardware-' + index + '-add-25'"
                                            class="btn btn-sm ms-4 btn-add-custom-quantity"
                                            @click="checkQuantity(product, product.quantity + 25, true)"
                                            :disabled="checkPlansHardwareQuantity() <= 0">+25</button>
                                        <button :id="'btn-hardware-' + index + '-add-50'"
                                            class="btn btn-sm ms-2 btn-add-custom-quantity"
                                            @click="checkQuantity(product, product.quantity + 50, true)"
                                            :disabled="checkPlansHardwareQuantity() <= 0">+50</button>
                                        <button :id="'btn-hardware-' + index + '-add-100'"
                                            class="btn btn-sm ms-2 btn-add-custom-quantity"
                                            @click="checkQuantity(product, product.quantity + 100, true)"
                                            :disabled="checkPlansHardwareQuantity() <= 0">+100</button>
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
                            <button id="btn-step-2-previous" class="btn text-primary step-nav btn-sm me-3" type="button"
                                @click="() => step--">
                                <font-awesome-icon :icon="['fas', 'arrow-left']" /> Previous
                            </button>
                            <button class="btn btn-outline-primary text-primary" @click="saveQuote()"
                                :disabled="checkPlansHardwareQuantity() < 0">
                                Save
                            </button>
                        </div>
                        <div>
                            <span id="total_add_ons">
                                Add {{ productsHardware.reduce((acc, item) => acc += item.quantity, 0) }}
                                handsets
                            </span>
                            <button id="btn-step-2-next" class="ms-3 btn btn-primary step-nav" type="button"
                                @click="saveQuote()">Next
                                <font-awesome-icon :icon="['fas', 'arrow-right']" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 order-first order-lg-last mb-5">
                <InfoBox>
                    <div style="font-size: 14px;">
                        <b style="font-size: 16px;">Quote {{ modelValue.id }} for {{ modelValue.account.name }}</b>
                        <br>
                        Contact Number: 0124567890<br>
                        Email: firstname.lastname@gmail.com
                    </div>
                </InfoBox>
                <div class="configure-mobile-sidebar px-4 py-4" style="position: sticky; top: 25px">
                    <h4 id="sidebar_products" data-total="0">1. Plans</h4>
                    <div class="sidebar_items mb-3">
                        <div v-for="product in products" class="item mb-2">
                            <span>{{ product.name }}</span>
                            <span class="badge bg-light-grey dark">
                                {{ product.quantity }}
                            </span>
                        </div>
                    </div>
                    <h4>2. Add-ons</h4>
                    <div class="sidebar_add_ons mb-3">
                        <div class="item mb-2">
                            <span>Hardware</span><span class="badge bg-light-grey dark">
                                {{ getHardwareProductsCount() }}
                            </span>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-2" @click="saveQuote()"
                        :disabled="checkPlansHardwareQuantity() < 0">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn-add-custom-quantity {
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-decoration: none;
    background: #F5E5F3;
    color: #A34996;
    padding-left: 15px;
    padding-right: 15px;
}

.bullet {
    padding: 0px 12px 0px 12px;
    border-radius: 20px;
    opacity: 0px;
    background: #F5F5F5;
}
</style>
