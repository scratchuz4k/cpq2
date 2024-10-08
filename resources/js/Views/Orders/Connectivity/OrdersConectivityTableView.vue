<script setup>
import axios from 'axios';
import Paginator from 'primevue/paginator';
import { ref, reactive, watch } from 'vue'
import MultiSelect from 'primevue/multiselect';

const props = defineProps({
    modelValue: Object
})

const isLoading = ref(true)
const showLowestCost = ref(true)
const showQuoted = ref(false)
const showFilters = ref(true)
const articles = ref([])
const filters = reactive({
    providerName: {
        operator: "in",
        value: ""
    },
    connectivitySupplierName: {
        operator: "in",
        value: ""
    },
    contractTermMonths: {
        operator: "in",
        value: ""
    },
    bearer: {
        operator: "between",
        value: []
    },
    speed: {
        operator: "between",
        value: []
    }
})

const filterList = reactive({
    providerName: [],
    connectivitySupplierName: [],
    contractTermMonths: [],
    bearer: [],
    speed: []
})

const meta = reactive({
    page: 1,
    total: 0,
    first: 1,
    limit: 20,
    isUpdating: false
})

const subProviders = {
    "btw": "bt",
    "bt": "bt",
    "bt wholesale": "bt",
    "cityfibre": "cityfibre",
    "colt": "colt",
    "gamma": "gamma",
    "gamma openreach": "gamma",
    "giacom": "giacom",
    "glide": "glide",
    "its": "its",
    "neos networks": "neos",
    "sky": "sky",
    "talktalk": "talktalk",
    "talktalk business": "talktalk",
    "virgin media": "virgin",
    "virgin": "virgin",
    "virtual1": "virtual1",
    "vodafone": "vodafone",
};

const getArticles = async () => {
    isLoading.value = true
    let temp = []
    Object.keys(filters).forEach((key) => {
        temp.push({ column: key, operator: filters[key].operator, value: filters[key].value })
    })
    if (showQuoted.value) {
        temp.push({ column: 'productCode', operator: 'in', value: props.modelValue.items.filter((item) => item.data.productCode).map((item) => item.data.productCode) })
    }
    if (showLowestCost.value) {
        temp.push({ column: '*', operator: 'lowest-cost', value: true })
    }

    let query = {
        connectivityType: 3,
        postCode: props.modelValue.address_postcode,
        latitude: props.modelValue.address_position?.lat,
        longitude: props.modelValue.address_position?.lon,
        uprn: props.modelValue.address_data?.uprn,
        street: props.modelValue.address_data?.addressparts?.ThoroughfareName,
        town: props.modelValue.address_city,
        addressKey: props.modelValue.address_data?.value,
        buildingNumber: props.modelValue.address_data?.addressparts?.ThoroughfareNumber,
        page: meta.page,
        perPage: meta.limit,
        filters: temp,
        order: sort.order,
        orderBy: sort.field,
    };

    let res = await axios.post('/api/external/get-articles-from-cloud-market', query)
    resolveGetProducts(!!res.data.finished_at)
    articles.value = res.data.data
    isLoading.value = !articles.value.length
    meta.first = (meta.page - 1) * meta.limit
    meta.total = res.data.total
    meta.isUpdating = !!res.data.finished_at
    Object.assign(filterList, res.data.filters)
}

const resolveGetProducts = (finished) => {
    if (finished) {
        return
    }
    setTimeout(() => {
        getArticles()
    }, 10000);
}

const setAltImg = (event) => {
    event.target.src = '/images/no_image.png'
}

const bytesFormatter = (value) => {
    let temp = Math.ceil(value)
    if ((temp / 1000) >= 1) {
        return Math.ceil((temp / 1000)) + " Gb"
    }
    return temp + " Mb"
}

const sort = reactive({
    field: "id",
    order: "asc"
})

const orderBy = (column) => {
    let changeSort = sort.order == 'asc' ? 'desc' : 'asc'
    sort.order = sort.field === column ? changeSort : sort.order
    sort.field = column
    getArticles()
}

const resetFilters = () => {
    Object.keys(filters).forEach((key) => {
        filters[key].value = null
    })

    filters['bearer'].value = [0, 10000000]
    filters['speed'].value = [0, 10000000]
    showLowestCost.value = true
}

const checkProduct = (article) => {
    let item = props.modelValue.items.find((item) => item.data.productCode == article.productCode || item.data.quoteId == article.quoteId)
    let index = props.modelValue.items.findIndex((item) => item.data.productCode == article.productCode || item.data.quoteId == article.quoteId)
    return index == -1 ? add(article) : remove(item, index)
}

const add = async (article) => {
    try {
        let res = await axios.post('/api/order-items',
            {
                order_id: props.modelValue.id,
                product_id: null,
                quantity: 1,
                name: article.productName,
                data: article,
            })
        props.modelValue.items.push(res.data)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const remove = async (item, index) => {
    try {
        await axios.delete(`/api/order-items/${item.id}`)
        props.modelValue.items.splice(index, 1)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const getQuoted = () => {
    showQuoted.value = !showQuoted.value
    getArticles()
}

const handlePaginator = (ev) => {
    meta.limit = ev.rows
    meta.page = ev.page + 1
    getArticles()
}
watch(showLowestCost, () => {
    getArticles()
})
watch(filters, () => {
    getArticles()
})
getArticles()

</script>
<template>
    <div class="panel">
        <InfoConnectivity :order="modelValue"></InfoConnectivity>
        <div class="row mb-4" style="min-height: 50vh;">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex  align-items-center">
                        <h2 class="me-3">Live prices </h2>
                        <div v-if="!meta.isUpdating"
                            class="badge badge-filter text-bg-danger m-0 d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            Searching
                        </div>
                        <p v-else class="badge badge-filter text-bg-success m-0">Prices Updated</p>
                    </div>

                    <div>
                        <button class="btn m-2" :class="[showQuoted ? ' btn-light' : 'btn-secondary']"
                            @click="getQuoted()">
                            Show Quoted
                        </button>
                        <button class="btn m-2" :class="[showFilters ? ' btn-light' : 'btn-primary']"
                            @click="() => showFilters = !showFilters">
                            <font-awesome-icon :icon="['fas', 'filter']" />
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div id="filters" v-if="showFilters">
                            <p class="me-3">
                                <b>FILTERS</b><br>
                                <span class="prices-total">
                                    ({{ meta.total }})</span>
                            </p>
                            <div style="width:150px;" class="me-3">
                                <label for="filter-subprovider" class="form-label">Provider:</label>
                                <MultiSelect v-model="filters['providerName'].value" style="width:100%"
                                    :options="filterList.providerName" placeholder="Select a provider" size="small">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }} </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div style="width:150px;" class="me-3">
                                <label for="filter-subprovider" class="form-label">Subprovider:</label>
                                <MultiSelect v-model="filters['connectivitySupplierName'].value" style="width:100%"
                                    :options="filterList.connectivitySupplierName" placeholder="Select a subprovider"
                                    class="me-3 ">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }}</div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div style="width:150px;" class="me-3">
                                <label for="filter-contract" class="form-label">Contract Length:</label>
                                <MultiSelect v-model="filters['contractTermMonths'].value" style="width:100%"
                                    :options="filterList.contractTermMonths" placeholder="Select months" class="me-3 ">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }} Months
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div class="d-flex slider-wrapper me-4">
                                <label for="bearerSlider" class="form-label">Bearer:</label>
                                <BearerRange v-model="filters['bearer'].value" :options="filterList.bearer" />
                            </div>
                            <div class="d-flex slider-wrapper me-5">
                                <label for="speedSlider" class="form-label">Speed:</label>
                                <SpeedRange v-model="filters['speed'].value" :options="filterList.speed" />
                            </div>
                            <div>
                                <button class="btn btn-secondary btn-sm" @click="resetFilters()">Reset</button>
                                <div class="advanced-settings pt-2">
                                    <button class="btn btn-secondary dropdown-toggle lh-1" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                        <font-awesome-icon :icon="['fas', 'gear']"></font-awesome-icon>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <div class="checkbox d-flex px-3">
                                                <input class="form-check-input me-2" type="checkbox"
                                                    value="filter-lower-cost" id="filter-lower-cost"
                                                    v-model="showLowestCost">
                                                <label class="form-check-label" for="filter-lower-cost">
                                                    Only show lowest cost router
                                                </label>

                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <div v-if="isLoading" class="table-blocker">
                        <div class="spinner-border" style="width: 5rem; height: 5rem;" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <table id="table_products" class="table  table-bordered table-hover">
                        <thead>
                            <tr>
                                <th></th>
                                <th :style="{
                                    whiteSpace: 'nowrap',
                                    textDecoration: sort.field == 'productName' ? 'underline' : 'none',
                                    width: '30%'
                                }" @click="orderBy('productName')">
                                    Product
                                    <font-awesome-icon :icon="['fas', 'sort']" />
                                </th>
                                <th :style="{ whiteSpace: 'nowrap', textDecoration: sort.field == 'providerName' ? 'underline' : 'none' }"
                                    @click="orderBy('providerName')">
                                    Provider
                                    <font-awesome-icon :icon="['fas', 'sort']" />
                                </th>
                                <th :style="{ whiteSpace: 'nowrap', textDecoration: sort.field == 'connectivitySupplierName' ? 'underline' : 'none' }"
                                    @click="orderBy('connectivitySupplierName')">
                                    Subprovider
                                    <font-awesome-icon :icon="['fas', 'sort']"></font-awesome-icon>
                                </th>
                                <th :style="{
                                    whiteSpace: 'nowrap',
                                    textDecoration: sort.field == 'bearer' ? 'underline' : 'none'
                                }" @click="orderBy('bearer')">
                                    Bearer
                                    <font-awesome-icon :icon="['fas', 'sort']" />
                                </th>
                                <th :style="{
                                    whiteSpace: 'nowrap',
                                    textDecoration: sort.field == 'speed' ? 'underline' : 'none'
                                }" @click="orderBy('speed')">
                                    Speed
                                    <font-awesome-icon :icon="['fas', 'sort']" />
                                </th>
                                <th :style="{
                                    whiteSpace: 'nowrap',
                                    textDecoration: sort.field == 'priceInstall' ? 'underline' : 'none'
                                }" @click="orderBy('priceInstall')">
                                    Set Up
                                    <font-awesome-icon :icon="['fas', 'sort']" />
                                </th>
                                <th :style="{
                                    whiteSpace: 'nowrap',
                                    textDecoration: sort.field == 'priceMonthly' ? 'underline' : 'none'
                                }" @click="orderBy('priceMonthly')">
                                    Monthly
                                    <font-awesome-icon :icon="['fas', 'sort']" />
                                </th>
                                <th :style="{
                                    whiteSpace: 'nowrap',
                                    textDecoration: sort.field == 'priceECC' ? 'underline' : 'none'
                                }" @click="orderBy('priceECC')">
                                    ECC
                                    <font-awesome-icon :icon="['fas', 'sort']" />
                                </th>
                                <th :style="{
                                    whiteSpace: 'nowrap',
                                    textDecoration: sort.field == 'contractTermMonths' ? 'underline' : 'none'
                                }" @click="orderBy('contractTermMonths')">
                                    Contract Term
                                    <font-awesome-icon :icon="['fas', 'sort']" />
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="article in articles" style="text-align: center;">
                                <td>
                                    <input type="checkbox" class="form-check-input" @input="checkProduct(article)"
                                        :checked="modelValue.items.find((item) => item.data.productCode == article.productCode || item.data.quoteId == article.quoteId)">
                                </td>
                                <td style="text-align: left;">
                                    {{ article.productName }}
                                    <span v-if="article.additionalInfo['Primary Router Model']"
                                        title="Primary Router Model"
                                        class="badge border border-secondary text-secondary opacity-75 ms-1">
                                        <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                        {{ article.additionalInfo['Primary Router Model'] }}
                                    </span>
                                </td>
                                <td><img class="img-provider" @error="setAltImg"
                                        :src="`/images/providers/${article.providerKey}.png`"
                                        :alt="article.providerName" :title="article.providerName" /> </td>
                                <td>
                                    <img class="img-subprovider" @error="setAltImg"
                                        :src="`/images/providers/${subProviders[article.connectivitySupplierName?.toLowerCase()]}.png`"
                                        :alt="article.connectivitySupplierName"
                                        :title="article.connectivitySupplierName" />
                                </td>
                                <td>{{ bytesFormatter(article.bearer) }} </td>
                                <td>{{ bytesFormatter(article.speed) }} </td>
                                <td>{{ GBP.format(article.priceInstall) }} </td>
                                <td>{{ GBP.format(article.priceMonthly) }} </td>
                                <td>
                                    <template v-if="article.priceECC">
                                        {{ GBP.format(article.priceECC) }}
                                    </template>
                                    <span v-else title="Subject to Survey" class="h6">
                                        <font-awesome-icon :icon="['far', 'circle-question']"></font-awesome-icon>
                                    </span>
                                </td>
                                <td>{{ article.contractTermMonths }} Months </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Paginator v-model:first="meta.first" :rows="meta.limit" :totalRecords="meta.total"
                    :rowsPerPageOptions="[20, 30, 50]" @page="handlePaginator">
                </Paginator>
            </div>
        </div>
    </div>
</template>

<style scoped>
tr.active td {
    background-color: var(--light-teal);
}

.table-wrapper {
    position: relative;
}

.table-blocker {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background-color: lightblue;
    z-index: 10;
    opacity: .5;
}
</style>
