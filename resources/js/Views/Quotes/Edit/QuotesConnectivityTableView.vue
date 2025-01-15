<script setup>
import _ from "lodash";
import axios from 'axios';
import Paginator from 'primevue/paginator';
import { ref, reactive, watch } from 'vue'
import MultiSelect from 'primevue/multiselect';
import Dialog from "primevue/dialog";
import format from "@/Composables/format.js";
import Spinner from "@/Components/Spinner.vue";
import Select from "primevue/select";

const props = defineProps({
    modelValue: Object
})

let timeoutId = null
const isLoading = ref(true)
const showLoadingModal = ref(true)
const showLowestCost = ref(true)
const showQuoted = ref(false)
const showFilters = ref(true)
const articles = ref([])
const errors = ref([])
const selectedFilters = ref([]);
const filters = reactive({
    productName: {
        operator: "not_in",
        value: [
            'ADSL 2+ MPF',
            'Software Defined SOGEA',
            'Broadband FTTC MPF',
            'Cellular Ethernet',
            'Internet Access (Wires Only)'
        ],
        name: 'Product'
    },
    providerName: {
        operator: "in",
        value: null,
        name: 'Provider'
    },
    connectivitySupplierGroup: {
        operator: "in",
        value: null,
        name: 'Subprovider'
    },
    contractTermMonths: {
        operator: "in",
        value: null,
        name: 'Contract Length'
    },
    bearer: {
        operator: "greater_than_or_equal_to",
        value: null,
        name: 'Bearer'
    },
    speed: {
        operator: "greater_than_or_equal_to",
        value: null,
        name: 'Speed'
    }
})

const filterList = reactive({
    providerName: [],
    connectivitySupplierGroup: [],
    contractTermMonths: [],
    bearer: [],
    speed: []
})

const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 10,
    isRequestCompleted: false
})

const getArticles = async (clearCache = false, runTimeout = false) => {
    isLoading.value = true
    let temp = []
    Object.keys(filters).forEach((key) => {
        temp.push({ column: key, operator: filters[key].operator, value: filters[key].value ?? null })
    })
    if (showQuoted.value) {
        let items = props.modelValue.items.length ? props.modelValue.items.filter((item) => item.product_code).map((item) => item.product_code) : ['#']
        temp.push({ column: 'code', operator: 'in', value: items })
    }
    if (showLowestCost.value) {
        temp.push({ column: '*', operator: 'lowest-cost', value: true })
    }
    if (clearCache) {
        showLoadingModal.value = true
    }

    let query = {
        clearCache: clearCache,
        connectivityType: 3,
        postCode: props.modelValue.address_postcode,
        latitude: props.modelValue.address_position?.lat,
        longitude: props.modelValue.address_position?.lon,
        uprn: props.modelValue.address_data?.uprn,
        street: props.modelValue.address_data?.thoroughfareName ?? props.modelValue.address_data?.dependentThoroughfareName,
        town: props.modelValue.address_city ?? props.modelValue.address_data?.locality ?? props.modelValue.address_data?.doubleDependentLocality,
        addressKey: props.modelValue.address_data?.addressKey,
        buildingNumber: props.modelValue.address_data?.thoroughfareNumber,
        buildingName: props.modelValue.address_data?.premisesName ?? props.modelValue.address_data?.organisationName,
        subPremises: props.modelValue.address_data?.subPremises,
        page: meta.page,
        perPage: meta.limit,
        filters: temp,
        order: sort.order,
        orderBy: sort.field,
        account_id: props.modelValue.account_id
    };

    try {
        let res = await axios.post('/api/external/get-articles-from-cloud-market', query)

        articles.value = res.data.data
        const errorMessages = {
            "BadGateway": "A Bad Gateway error occurred, resulting in partial or no results.",
            "Request timed out": "The request timed out, resulting in partial or no results.",
            "InternalServerError": "An error occurred on the server.",
            "An error occurred while sending the request": "There was an issue sending your request.",
            "TooManyRequests": "The system is experiencing a high volume of requests.",
            "Too Many Attempts": "There are currently too many requests being processed.",
            "System.Text.Json.JsonException": "The request could not be processed due to a data format issue.",
            "Service Unavailable": "The service is temporarily unavailable.",
            "There is an error in XML document": "The request could not be processed due to a data format issue.",
            "No provider is configured": "No provider is configured, please add at least one provider with the required credentials to obtain a quote.",
            "Request failed with status Unauthorized": "Request failed with status Unauthorized (Check login credentials for this provider)",
        };

        errors.value = res.data.errors.map((error) => {
            for (const [key, message] of Object.entries(errorMessages)) {
                if (error.response.includes(key)) {
                    return { provider: error.provider, response: message };
                }
            }
            return { provider: error.provider, response: 'An error occurred, resulting in partial or no results' }
        });

        articles.value = articles.value.map((article) => {
            if (article.productName.includes('with router:')) {
                const productInfo = article.productName.split('with router:');
                article.productName = productInfo[0].trim();
                if (productInfo.length > 1) {
                    article.additionalInfo = article.additionalInfo || {};
                    article.additionalInfo['Primary Router Model'] = productInfo[1].trim();
                }
            }

            return article;
        });

        isLoading.value = false
        if (!showLoadingModal.value && (meta.overallTotal !== res.data.overall_total) && (meta.overallTotal > 0)) {
            notyf.success("New products added!")
        }
        meta.first = (meta.page - 1) * meta.limit
        meta.total = res.data.total
        meta.overallTotal = res.data.overall_total
        meta.isRequestCompleted = !!res.data.finished_at


        Object.assign(filterList, res.data.filters)

        let bearerFilters = []
        Object.keys(filterList.bearer).forEach((key) => {
            bearerFilters.push({
                label: format.speed(key),
                value: key
            })
        })
        filterList.bearer = bearerFilters

        let speedFilters = []
        Object.keys(filterList.speed).forEach((key) => {
            speedFilters.push({
                label: format.speed(key),
                value: key
            })
        })
        filterList.speed = speedFilters

        selectedFilters.value = Object.fromEntries(
            Object.entries(filters).filter(([key, filter]) => filter.value !== null)
        )

        if (!meta.isRequestCompleted && runTimeout) {
            timeoutId = setTimeout(() => {
                getArticles(false, true)
            }, 15000);
            return
        } else {
            showLoadingModal.value = false
        }

    } catch (error) {
        console.log(error);
        notyf.error(error)
        isLoading.value = false
        meta.isRequestCompleted = false
        showLoadingModal.value = false
    }
}


const setAltImg = (event) => {
    event.target.src = '/images/no_image.png'
}

const sort = reactive({
    field: "priceMonthly",
    order: "asc"
})

const orderBy = (column) => {
    let changeSort = sort.order == 'asc' ? 'desc' : 'asc'
    sort.order = sort.field === column ? changeSort : (['speed', 'bearer'].includes(column) ? 'desc' : sort.order)
    sort.field = column
    getArticles()
}

const resetFilters = () => {
    Object.keys(filters)
        .filter((key) => key !== 'productName')
        .forEach((key) => {
            filters[key].value = null
        })

    showLowestCost.value = true
    showQuoted.value = false
}

const checkProduct = (article) => {
    let item = props.modelValue.items.find((item) => item.product_code == article.code)
    let index = props.modelValue.items.findIndex((item) => item.product_code == article.code)
    return index == -1 ? add(article) : remove(item, index)
}

const add = async (article) => {
    try {
        let childs = []
        if (article.priceMonthly_spa) {
            childs.push({
                name: article.productName,
                price_plan_code: 'monthly',
                contract_length: article.contractTermMonths,
                raw_price: article.priceMonthly,
                price: article.priceMonthly_spa,
            })
        }
        if (article.priceInstall_spa) {
            childs.push({
                name: article.productName + ' (service install)',
                price_plan_code: 'single',
                raw_price: article.priceInstall,
                price: article.priceInstall_spa,
            })
        }

        let item = {
            quote_id: props.modelValue.id,
            product_id: null,
            quantity: 1,
            product_code: article.code,
            name: article.productName,
            data: article,
        }

        if (childs.length == 1) {
            item = { ...item, ...childs[0] }
        } else {
            item.children = childs
        }

        let res = await axios.post('/api/quote-items', item)
        props.modelValue.items.push(res.data)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const remove = async (item, index) => {
    try {
        await axios.delete(`/api/quote-items/${item.id}`)
        props.modelValue.items.splice(index, 1)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}


const removeFilter = (selectedFilter, value) => {
    if (Array.isArray(selectedFilter.value)) {
        let index = selectedFilter.value.findIndex((x) => x === value)
        selectedFilter.value.splice(index, 1)
    } else {
        selectedFilter.value = null
    }
}

const handlePaginator = (ev) => {
    meta.limit = ev.rows
    meta.page = ev.page + 1
    getArticles()
}

watch([filters, showQuoted, showLowestCost], _.debounce(function () {
    meta.page = 1
    getArticles()
}, 500))

window.addEventListener("beforeunload", () => {
    clearTimeout(timeoutId); // Cancel timeout on page navigation or refresh
});

getArticles(false, true)

</script>
<template>
    <div>
        <InfoConnectivity :quote="modelValue"></InfoConnectivity>
        <div class="row mb-2">
            <div class="col position-relative">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fs-4 me-3 mb-0"><b>Add products</b></p>
                        <p class="mb-0 text-primary bold fs-5 pt-1">STEP 1/2</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fs-4 me-3 mb-0"><b>Add {{ modelValue.items.length }} products</b></p>
                        <button v-if="modelValue.items.length == 0" class="btn btn-primary continue-btn" type="button"
                            disabled>
                            Continue
                            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                        </button>
                        <a v-else class="btn btn-primary continue-btn" type="submit"
                            :href="route('quotes.show', { quote: modelValue.id })">
                            Continue
                            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                        </a>
                    </div>
                </div>
                <div class="row mb-3" v-if="errors.length">
                    <div class="col">
                        <InfoBox class="mb-2 py-0" severity="info">
                            <div class="d-flex justify-content-between">
                                <div class="small">
                                    <div class="mb-2 fw-bold">There were issues retrieving data from:</div>
                                    <div v-for="error in errors" class="mb-1">
                                        <span class="small fw-bold bg-light rounded-2 py-1 px-2 me-1 text-capitalize">{{
                                            error?.provider }}</span><i>{{ error?.response }}</i>
                                    </div>
                                </div>
                                <div v-if="meta.isRequestCompleted" class="small d-flex align-items-center">
                                    <button class="btn btn-sm btn-outline-primary" @click="getArticles(true, true)">Get
                                        fresh data
                                        <font-awesome-icon :icon="['fas', 'refresh']"
                                            class="ps-2"></font-awesome-icon></button>
                                </div>
                            </div>
                        </InfoBox>
                    </div>
                </div>
                <div class="row">
                    <div class="d-flex justify-content-between">
                        <div class="filters d-flex">
                            <div class="d-flex mb-2">
                                <p class="fs-5 bold me-2">
                                    <font-awesome-icon v-if="!meta.isRequestCompleted" :icon="['fas', 'refresh']"
                                        class="fa-spin me-2"></font-awesome-icon>Filters
                                    <span class="fw-normal">({{ meta.total }})</span>
                                </p>
                            </div>
                            <ul class="d-flex list-unstyled ms-2 mb-0">
                                <li>
                                    <template v-for="(selectedFilter, column) in selectedFilters">
                                        <Badge
                                            v-for="value in column === 'productName' ? [] : (Array.isArray(selectedFilter.value) ? selectedFilter.value : [selectedFilter.value]).filter(v => v !== null)"
                                            :value="['speed', 'bearer'].includes(column) ? 'Min. ' + format.speed(value) : (['contractTermMonths'].includes(column) ? value + ' Months' : value)"
                                            class="px-3 py-1" data-toggle="tooltip" data-placement="top"
                                            :title="selectedFilter.name">
                                            <template #close-button>
                                                <font-awesome-icon :icon="['fas', 'x']" role="button" class="ms-1 fs-7"
                                                    @click="removeFilter(selectedFilter, value)" />
                                            </template>
                                        </Badge>
                                    </template>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div id="filters" v-if="showFilters" class="p-0 m-0 mt-2 mb-4 row">
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-provider" class="form-label">Provider</label>
                        <MultiSelect id="provider-multiselect" v-model="filters['providerName'].value"
                            style="width:100%" :options="Object.keys(filterList.providerName)" placeholder="All"
                            size="small" class="custom-multiselect">
                            <template #option="slotProps">
                                <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }}
                                    ( {{ filterList.providerName[slotProps.option] }} )
                                </div>
                            </template>
                        </MultiSelect>
                    </div>
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-subprovider" class="form-label">Subprovider</label>
                        <MultiSelect id="subprovider-multiselect" v-model="filters['connectivitySupplierGroup'].value"
                            style="width:100%" :options="Object.keys(filterList.connectivitySupplierGroup)"
                            placeholder="All" class="me-3 custom-multiselect">
                            <template #option="slotProps">
                                <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }}
                                    ( {{ filterList.connectivitySupplierGroup[slotProps.option] }} )
                                </div>
                            </template>
                        </MultiSelect>
                    </div>
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-contract" class="form-label">Contract Length</label>
                        <MultiSelect v-model="filters['contractTermMonths'].value" style="width:100%"
                            :options="Object.keys(filterList.contractTermMonths)" placeholder="All"
                            class="me-3 custom-multiselect ">
                            <template #option="slotProps">
                                <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }} Months
                                </div>
                            </template>
                        </MultiSelect>
                    </div>
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-contract" class="form-label">Min. Bearer</label>
                        <Select v-model="filters['bearer'].value" style="width:100%" :options="filterList.bearer"
                            optionLabel="label" optionValue="value" placeholder="All" class="me-3 custom-select ">
                            <template #option="slotProps">
                                <div style="font-size:14px;line-height: 1.5rem;">
                                    {{ slotProps.option.label }}
                                </div>
                            </template>
                        </Select>
                    </div>
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-contract" class="form-label">Min. Speed</label>
                        <Select v-model="filters['speed'].value" style="width:100%" :options="filterList.speed"
                            optionLabel="label" optionValue="value" placeholder="All" class="me-3 custom-select ">
                            <template #option="slotProps">
                                <div style="font-size:14px;line-height: 1.5rem;">
                                    {{ slotProps.option.label }}
                                </div>
                            </template>
                        </Select>
                    </div>
                    <div class="col-lg-2 col-12 d-flex align-self-end justify-content-around">
                        <button class="btn btn-secondary btn-sm mt-2 me-2 w-100" @click="resetFilters()">Reset</button>
                        <div class="advanced-settings pt-2 w-100">
                            <button id="btn-more-filters" class="btn btn-secondary dropdown-toggle lh-1 w-100"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                data-bs-auto-close="outside">
                                <font-awesome-icon :icon="['fas', 'gear']"></font-awesome-icon>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-1">
                                <li>
                                    <div class="checkbox d-flex px-3">
                                        <input class="form-check-input me-2" type="checkbox" value="filter-lower-cost"
                                            id="filter-lower-cost" v-model="showLowestCost">
                                        <label class="form-check-label" for="filter-lower-cost">
                                            Only show lowest cost router
                                        </label>
                                    </div>
                                    <div class="checkbox d-flex px-3">
                                        <input class="form-check-input me-2" type="checkbox" value="filter-show-quoted"
                                            id="filter-show-quoted" v-model="showQuoted">
                                        <label class="form-check-label" for="filter-show-quoted">
                                            Show quoted items
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div v-if="!meta.isRequestCompleted" data-provider="loading" class="position-relative">
                    <div class="cpq-progress-bar" style="position: absolute;z-index: 2; ">
                        <div class="cpq-bar"></div>
                    </div>
                </div>
                <div id="no-results" v-if="!articles.length">
                    <div class="alert alert-secondary mt-3">No results match your filters. <button @click="resetFilters"
                            class="btn btn-primary  btn-sm js-clear-filters ms-3">Clear filters</button></div>
                </div>
                <div v-if="articles.length" class="table-wrapper">
                    <table class="table-dealmaker table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="ps-3" :style="{
                                    whiteSpace: 'nowrap',
                                    width: '380px'
                                }" @click="orderBy('productName')">
                                    Product
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'productName'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th class="text-center" :style="{ whiteSpace: 'nowrap' }"
                                    @click="orderBy('providerName')">
                                    Provider
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'providerName'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th class="text-center" :style="{ whiteSpace: 'nowrap' }"
                                    @click="orderBy('connectivitySupplierName')">
                                    Subprovider
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'connectivitySupplierName'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th class="text-center" :style="{
                                    whiteSpace: 'nowrap'
                                }" @click="orderBy('bearer')">
                                    Bearer
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'bearer'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th class="text-center" :style="{
                                    whiteSpace: 'nowrap'
                                }" @click="orderBy('speed')">
                                    Speed
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'speed'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th class="text-center" :style="{
                                    whiteSpace: 'nowrap'
                                }" @click="orderBy('priceInstall')">
                                    Set Up
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'priceInstall'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th class="text-center" :style="{
                                    whiteSpace: 'nowrap'
                                }" @click="orderBy('priceMonthly')">
                                    Monthly
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'priceMonthly'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th class="text-center" :style="{
                                    whiteSpace: 'nowrap'
                                }" @click="orderBy('priceECC')">
                                    ECC
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'priceECC'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th class="text-center" :style="{
                                    whiteSpace: 'nowrap'
                                }" @click="orderBy('contractTermMonths')">
                                    Contract Term
                                    <span class="text-center d-inline-block align-middle" style="width: 20px;">
                                        <template v-if="sort.field == 'contractTermMonths'">
                                            <font-awesome-icon v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']" />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th :style="{
                                    width: '130px'
                                }"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="connectivity-table-item" v-for="(article, index) in articles"
                                style="text-align: center;">
                                <td class="lh-sm text-left ps-3">
                                    {{ article.productName }}
                                    <span v-if="article?.additionalInfo?.['Primary Router Model']" v-tooltip
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Primary Router Model"
                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                        <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                        {{ article.additionalInfo['Primary Router Model'] }}
                                    </span>
                                    <span v-if="article?.isContended"
                                          class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                          data-bs-toggle="tooltip" data-bs-placement="top" title="Contended" v-tooltip>
                                        <font-awesome-icon :icon="['fas', 'network-wired']" />
                                    </span>
                                </td>
                                <td><img class="img-provider" @error="setAltImg"
                                        :src="`/images/providers/${article.providerKey}.png`"
                                        :alt="article.providerName" :title="article.providerName"
                                        :data-bs-original-title="article.providerName" v-tooltip/> </td>
                                <td>
                                    <img class="img-subprovider" @error="setAltImg"
                                        :src="`/images/providers/${article.connectivitySupplierGroup?.replace(/ /g, '_').toLowerCase()}.png`"
                                        :alt="article.connectivitySupplierName"
                                        :title="article.connectivitySupplierName"
                                        :data-bs-original-title="article.connectivitySupplierName" v-tooltip />
                                </td>
                                <td>{{ format.speed(article.bearer) }} </td>
                                <td>{{ format.speed(article.speed) }} </td>
                                <td :id="'line-' + index + '-price-install'">{{ GBP.format(article.priceInstall_spa) }}
                                </td>
                                <td :id="'line-' + index + '-price-monthly'">{{ GBP.format(article.priceMonthly_spa) }}
                                </td>
                                <td>
                                    <template v-if="article.priceECC">
                                        {{ GBP.format(article.priceECC) }}
                                    </template>
                                    <span v-else title="Subject to Survey" class="h6"
                                        data-bs-original-title="Subject to Survey" v-tooltip>
                                        <font-awesome-icon :icon="['far', 'circle-question']"></font-awesome-icon>
                                    </span>
                                </td>
                                <td>{{ article.contractTermMonths }} Months </td>
                                <td class="text-end">
                                    <button v-if="modelValue.items.find((item) => item.product_code == article.code)"
                                        class="btn btn-primary me-2 py-1" @click="checkProduct(article)">
                                        <font-awesome-icon :icon="['fas', 'check']" />
                                        Added
                                    </button>
                                    <button v-else class="btn btn-outline-primary me-2 py-1"
                                        @click="checkProduct(article)">
                                        <font-awesome-icon :icon="['fas', 'plus']" />
                                        Add
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Paginator v-model:first="meta.first" :rows="meta.limit" :pageLinkSize="5"
                        :totalRecords="meta.total" :rowsPerPageOptions="[10, 25, 50]" @page="handlePaginator"
                        class="custom-paginator">
                    </Paginator>
                </div>
                <div class="d-flex justify-content-end align-items-center my-5">
                    <p class="fs-4 me-3 mb-0"><b>Add {{ modelValue.items.length }} products</b></p>
                    <button v-if="modelValue.items.length == 0" class="btn btn-primary" type="button" disabled>
                        Continue
                        <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                    </button>
                    <a v-else class="btn btn-primary" type="submit"
                        :href="route('quotes.show', { quote: modelValue.id })">
                        Continue
                        <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="showLoadingModal" modal :show-header="false" class="loading-modal px-4"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" :draggable="false">
        <div class="panel">
            <div class="row">
                <div class="text-center">
                    <Spinner class="mt-5 mb-4 d-flex justify-content-center" />
                    <h4><strong>Finding your available products</strong></h4>
                    <h6>This may take a few moments, the perfect <br> chance to grab a coffee!</h6>
                    <div class="cpq-bar-bg">
                        <div class="cpq-bar"></div>
                    </div>
                    <p>
                        <strong>{{ meta.overallTotal }}</strong>
                        results retrieved so far
                        <a role="button" class="text-dark" @click.prevent="showLoadingModal = false">view now</a>
                    </p>
                </div>
            </div>
        </div>
    </Dialog>
</template>

<style scoped>
tr.active td {
    background-color: var(--light-teal);
}

.table-wrapper {
    position: relative;
    overflow-x: auto;
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

.clear-filter>p {
    font-size: 14px;
}

.loading-modal .cpq-bar-bg {
    height: 8px;
    border-radius: 4px;
    background-color: #D9D9D9;
    position: relative;
    width: 100%;
    z-index: -1;
    margin-top: 3rem;
    margin-bottom: 1rem;
}

.loading-modal .cpq-bar {
    height: 8px;
    border-radius: 4px;
    width: 0%;
    background-color: var(--purple);
    animation: progressBar 240s linear forwards;
}
</style>
