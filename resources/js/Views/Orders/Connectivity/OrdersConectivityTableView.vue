<script setup>
import _ from "lodash";
import axios from 'axios';
import Paginator from 'primevue/paginator';
import { ref, reactive, watch, onMounted } from 'vue'
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
        value: [0, 10000]
    },
    speed: {
        operator: "between",
        value: [100, 1000]
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
    overallTotal: 0,
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

const getArticles = _.debounce(async () => {
    isLoading.value = true
    let temp = []
    Object.keys(filters).forEach((key) => {
        temp.push({ column: key, operator: filters[key].operator, value: filters[key].value })
    })
    if (showQuoted.value) {
        temp.push({ column: 'code', operator: 'in', value: props.modelValue.items.filter((item) => item.data.code).map((item) => item.data.code) })
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

    const csrfToken = document.head.querySelector("[name~=csrf-token][content]").content;
    let res = await fetch('/api/external/get-articles-from-cloud-market',
        {
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-Token": csrfToken
            },
            method: "post",
            credentials: "same-origin",
            body: JSON.stringify(query)
        })
    const reader = res.body.getReader();
    const decoder = new TextDecoder();
    const chunks = [];


    while (true) {
        const { done, value } = await reader.read();
        if (done) {
            break;
        }

        try {
            const data = JSON.parse(decoder.decode(value, { stream: true }));
            articles.value = data.data
            isLoading.value = false
            meta.first = (meta.page - 1) * meta.limit
            meta.total = data.total
            meta.overallTotal = data.overall_total
            meta.isUpdating = !!data.finished_at
            Object.assign(filterList, data.filters)
        } catch (e) {
            console.log(e)
        }
    }

}, 800)


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
    field: "priceMonthly",
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

    emitter.emit('bearerSlider-reset')
    emitter.emit('speedSlider-reset')
    showLowestCost.value = true
    showQuoted.value = false
}

const checkProduct = (article) => {
    let item = props.modelValue.items.find((item) => item.data.code == article.code)
    let index = props.modelValue.items.findIndex((item) => item.data.code == article.code)
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


const handlePaginator = (ev) => {
    meta.limit = ev.rows
    meta.page = ev.page + 1
    getArticles()
}
watch(showQuoted, () => {
    getArticles()
})
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
                        <button class="btn m-2" :class="[showFilters ? ' btn-light' : 'btn-primary']"
                            @click="() => showFilters = !showFilters" :disabled="!meta.isUpdating">
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
                                    :options="Object.keys(filterList.providerName)" placeholder="Select a provider"
                                    size="small" :disabled="!meta.isUpdating">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }}
                                            ( {{ filterList.providerName[slotProps.option] }} )
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div style="width:150px;" class="me-3">
                                <label for="filter-subprovider" class="form-label">Subprovider:</label>
                                <MultiSelect v-model="filters['connectivitySupplierName'].value" style="width:100%"
                                    :options="Object.keys(filterList.connectivitySupplierName)"
                                    placeholder="Select a subprovider" class="me-3 " :disabled="!meta.isUpdating">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }}
                                            ( {{ filterList.connectivitySupplierName[slotProps.option] }} )
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div style="width:150px;" class="me-3">
                                <label for="filter-contract" class="form-label">Contract Length:</label>
                                <MultiSelect v-model="filters['contractTermMonths'].value" style="width:100%"
                                    :options="Object.keys(filterList.contractTermMonths)" placeholder="Select months"
                                    class="me-3 " :disabled="!meta.isUpdating">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }} Months
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div class="d-flex slider-wrapper me-4">
                                <label for="bearerSlider" class="form-label">Bearer:</label>
                                <BearerRange v-model="filters['bearer'].value" :options="Object.keys(filterList.bearer)"
                                    :disabled="!meta.isUpdating" />
                            </div>
                            <div class="d-flex slider-wrapper me-5">
                                <label for="speedSlider" class="form-label">Speed:</label>
                                <SpeedRange v-model="filters['speed'].value" :options="Object.keys(filterList.speed)"
                                    :disabled="!meta.isUpdating" />
                            </div>
                            <div>
                                <button class="btn btn-secondary btn-sm" @click="resetFilters()"
                                    :disabled="!meta.isUpdating">Reset</button>
                                <div class="advanced-settings pt-2">
                                    <button class="btn btn-secondary dropdown-toggle lh-1" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"
                                        :disabled="!meta.isUpdating">
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
                                            <div class="checkbox d-flex px-3">
                                                <input class="form-check-input me-2" type="checkbox"
                                                    value="filter-show-quoted" id="filter-show-quoted"
                                                    v-model="showQuoted">
                                                <label class="form-check-label" for="filter-show-quoted">
                                                    Show quoted items
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="isLoading || !meta.isUpdating" data-provider="loading" class="cpq-progress-bar">
                    <div class="cpq-bar"></div>
                    <div class="cpq-bar-text">
                        <font-awesome-icon :icon="['fas', 'refresh']" class="fa-spin"></font-awesome-icon>
                        Please wait... <span>( {{ meta.overallTotal }} )</span>
                    </div>
                </div>
                <div id="no-results" v-else-if="!articles.length">
                    <div class="alert alert-info mt-3">No results match your filters. <button @click="resetFilters"
                            class="btn btn-primary  btn-sm js-clear-filters ms-3">Clear filters</button></div>
                </div>
                <div v-if="articles.length" class="table-wrapper">
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
                                        :checked="modelValue.items.find((item) => item.data.code == article.code)">
                                </td>
                                <td style="text-align: left;">
                                    {{ article.productName }}
                                    <span v-if="article.additionalInfo['Primary Router Model']" v-tooltip
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Primary Router Model"
                                        class="badge border border-secondary text-secondary opacity-75 ms-1">
                                        <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                        {{ article.additionalInfo['Primary Router Model'] }}
                                    </span>
                                </td>
                                <td><img class="img-provider" @error="setAltImg"
                                        :src="`/images/providers/${article.providerKey}.png`"
                                        :alt="article.providerName" :title="article.providerName" v-tooltip /> </td>
                                <td>
                                    <img class="img-subprovider" @error="setAltImg"
                                        :src="`/images/providers/${subProviders[article.connectivitySupplierName?.toLowerCase()]}.png`"
                                        :alt="article.connectivitySupplierName"
                                        :title="article.connectivitySupplierName" v-tooltip />
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
                    <Paginator v-if="meta.isUpdating" v-model:first="meta.first" :rows="meta.limit"
                        :totalRecords="meta.total" :rowsPerPageOptions="[20, 30, 50]" @page="handlePaginator">
                    </Paginator>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.p-multiselect-header {
    display: none !important
}

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
