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

const isLoading = ref(true)
const showLoadingModal = ref(true)
const showLowestCost = ref(true)
const showQuoted = ref(false)
const showFilters = ref(true)
const articles = ref([])
const selectedFilters = ref([]);
const filters = reactive({
    providerName: {
        operator: "in",
        value: null,
        name: 'Provider'
    },
    connectivitySupplierName: {
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
    limit: 10,
    isUpdating: false
})

const getSubproviderImage = (img) => {
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
        "its technology group": "its",
        "neos networks": "neos",
        "sky": "sky",
        "talktalk": "talktalk",
        "talktalk business": "talktalk",
        "virgin media": "virgin",
        "virgin media business": "virgin_media_business",
        "virgin": "virgin",
        "virtual1": "virtual1",
        "vodafone": "vodafone",
    };
    if (Object.keys(subProviders).includes(img)) {
        return subProviders[img]
    }
    return 'no_image'
}

const getArticles = async () => {
    isLoading.value = true
    let temp = []
    Object.keys(filters).forEach((key) => {
        temp.push({ column: key, operator: filters[key].operator, value: filters[key].value ?? null })
    })
    if (showQuoted.value) {
        let items = props.modelValue.items.length ? props.modelValue.items.filter((item) => item.data.code).map((item) => item.data.code) : ['#']
        temp.push({ column: 'code', operator: 'in', value: items })
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
        street: props.modelValue.address_data?.thoroughfareName,
        town: props.modelValue.address_city,
        addressKey: props.modelValue.address_data?.addressKey,
        buildingNumber: props.modelValue.address_data?.thoroughfareNumber ?? props.modelValue.address_data.BuildingNumber,
        buildingName: props.modelValue.address_data?.premisesName ?? props.modelValue.address_data.BuildingName ?? props.modelValue.address_data?.organisationName,
        page: meta.page,
        perPage: meta.limit,
        filters: temp,
        order: sort.order,
        orderBy: sort.field,
    };

    const csrfToken = document.head.querySelector("[name~=csrf-token][content]").content;
	/*
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
        }
    }
	*/
    let res = await axios.post('/api/external/get-articles-from-cloud-market',query)

	articles.value = res.data.data
	isLoading.value = false
	meta.first = (meta.page - 1) * meta.limit
	meta.total = res.data.total
	meta.overallTotal = res.data.overall_total
	meta.isUpdating = !!res.data.finished_at
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

    showLoadingModal.value = false
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
        let res = await axios.post('/api/quote-items',
            {
                quote_id: props.modelValue.id,
                product_id: null,
                quantity: 1,
                name: article.productName,
                data: article,
                price: article.priceInstall ?? 0,
                price_monthly: article.priceMonthly ?? 0
            })
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
    let index = selectedFilter.value.findIndex((x) => x === value)

    selectedFilter.value.splice(index, 1)
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


getArticles()

</script>
<template>
    <div>
        <InfoConnectivity :quote="modelValue"></InfoConnectivity>
        <div class="row mb-4">
            <div class="col position-relative">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fs-4 me-3 mb-0"><b>Add products</b></p>
                        <p class="mb-0 text-primary bold fs-5 pt-1">STEP 1/2</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fs-4 me-3 mb-0"><b>Add {{ modelValue.items.length }} products</b></p>
                        <button v-if="modelValue.items.length == 0" class="btn btn-primary continue-btn" type="button" disabled>
                            Continue
                            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                        </button>
                        <a v-else class="btn btn-primary continue-btn" type="submit" :href="route('quotes.show', { quote: modelValue.id })">
                            Continue
                            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="d-flex justify-content-between">
                        <div class="filters d-flex">
                            <p class="fs-5 bold mb-2">
                                Filters
                                <span class="fw-normal">({{ meta.total }})</span>
                            </p>
                            <ul class="d-flex list-unstyled ms-2 mb-0">
                                <li>
                                    <template v-for="(selectedFilter, column) in selectedFilters">
                                        <Badge
                                            v-for="value in (Array.isArray(selectedFilter.value) ? selectedFilter.value : [selectedFilter.value])"
                                            :value="['speed', 'bearer'].includes(column) ? 'Min. ' + format.speed(value) : (['contractTermMonths'].includes(column) ? value + ' Months' : value)"
                                            class="px-3 py-1"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            :title="selectedFilter.name"
                                        >
                                            <template #close-button>
                                                <font-awesome-icon :icon="['fas', 'x']" role="button" class="ms-1 fs-7" @click="removeFilter(selectedFilter, value)"/>
                                            </template>
                                        </Badge>
                                    </template>
                                </li>
                            </ul>
                        </div>
                        <div class="clear-filter">
                            <a href="#" class="nav-link" @click="resetFilters">Clear filters</a>
                        </div>
                    </div>
                </div>
                    <div id="filters" v-if="showFilters" class="p-0 m-0 mt-2 mb-4 row">
                        <div class="col-lg-2 col-12 p-0 pe-1">
                            <label for="filter-provider" class="form-label">Provider</label>
                            <MultiSelect v-model="filters['providerName'].value" style="width:100%"
                                :options="Object.keys(filterList.providerName)" placeholder="All" size="small"
                                :disabled="!meta.isUpdating"
                                class="custom-multiselect"
                            >
                                <template #option="slotProps">
                                    <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }}
                                        ( {{ filterList.providerName[slotProps.option] }} )
                                    </div>
                                </template>
                            </MultiSelect>
                        </div>
                        <div class="col-lg-2 col-12 p-0 pe-1">
                            <label for="filter-subprovider" class="form-label">Subprovider</label>
                            <MultiSelect v-model="filters['connectivitySupplierName'].value" style="width:100%"
                                :options="Object.keys(filterList.connectivitySupplierName)" placeholder="All"
                                class="me-3 custom-multiselect" :disabled="!meta.isUpdating">
                                <template #option="slotProps">
                                    <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }}
                                        ( {{ filterList.connectivitySupplierName[slotProps.option] }} )
                                    </div>
                                </template>
                            </MultiSelect>
                        </div>
                        <div class="col-lg-2 col-12 p-0 pe-1">
                            <label for="filter-contract" class="form-label">Contract Length</label>
                            <MultiSelect v-model="filters['contractTermMonths'].value" style="width:100%"
                                :options="Object.keys(filterList.contractTermMonths)" placeholder="All"
                                class="me-3 custom-multiselect " :disabled="!meta.isUpdating">
                                <template #option="slotProps">
                                    <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }} Months
                                    </div>
                                </template>
                            </MultiSelect>
                        </div>
                        <div class="col-lg-2 col-12 p-0 pe-1">
                            <label for="filter-contract" class="form-label">Min. Bearer</label>

                            <Select v-model="filters['bearer'].value" style="width:100%"
                                         :options="[{label: 'All', value: null}, ...filterList.bearer]" optionLabel="label" optionValue="value" placeholder="All"
                                         class="me-3 custom-multiselect " :disabled="!meta.isUpdating">
                                <template #option="slotProps">
                                    <div style="font-size:14px;line-height: 1.5rem;">
                                        {{ slotProps.option.label }}
                                    </div>
                                </template>
                            </Select>
                        </div>
                        <div class="col-lg-2 col-12 p-0 pe-1">
                            <label for="filter-contract" class="form-label">Min. Speed</label>
                            <Select v-model="filters['speed'].value" style="width:100%"
                                         :options="[{label: 'All', value: null}, ...filterList.speed]" optionLabel="label" optionValue="value" placeholder="All"
                                         class="me-3 custom-multiselect " :disabled="!meta.isUpdating">
                                <template #option="slotProps">
                                    <div style="font-size:14px;line-height: 1.5rem;">
                                        {{ slotProps.option.label }}
                                    </div>
                                </template>
                            </Select>
                        </div>
                        <div class="col-lg-2 col-12 d-flex align-self-end justify-content-around">
                            <button class="btn btn-secondary btn-sm mt-2 me-2 w-100" @click="resetFilters()"
                                    :disabled="!meta.isUpdating">Reset</button>
                            <div class="advanced-settings pt-2 w-100">
                                <button class="btn btn-secondary dropdown-toggle lh-1 w-100" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"
                                        :disabled="!meta.isUpdating">
                                    <font-awesome-icon :icon="['fas', 'gear']"></font-awesome-icon>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end p-1">
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
                <div v-if="isLoading || !meta.isUpdating" data-provider="loading" class="position-relative">
                    <div class="cpq-progress-bar" style="position: absolute;z-index: 2; ">
                        <div class="cpq-bar"></div>
                        <div class="cpq-bar-text" v-if="!articles.length">
                            <font-awesome-icon :icon="['fas', 'refresh']" class="fa-spin"></font-awesome-icon>
                            Please wait... <span>({{ meta.overallTotal }})</span>
                        </div>
                    </div>
                </div>
                <div id="no-results" v-else-if="!articles.length">
                    <div class="alert alert-secondary mt-3">No results match your filters. <button @click="resetFilters"
                            class="btn btn-primary  btn-sm js-clear-filters ms-3">Clear filters</button></div>
                </div>
                <div v-if="articles.length" class="table-wrapper">
                    <table id="table_products" class="table  table-bordered table-hover">
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
                                <th class="text-center" :style="{ whiteSpace: 'nowrap' }" @click="orderBy('providerName')">
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
                                <th class="text-center" :style="{ whiteSpace: 'nowrap' }" @click="orderBy('connectivitySupplierName')">
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
                            <tr v-for="article in articles" style="text-align: center;">
                                <td class="lh-sm text-left ps-3">
                                    {{ article.productName }}
                                    <span v-if="article.additionalInfo['Primary Router Model']" v-tooltip
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Primary Router Model"
                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                        <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                        {{ article.additionalInfo['Primary Router Model'] }}
                                    </span>
                                </td>
                                <td><img class="img-provider" @error="setAltImg"
                                        :src="`/images/providers/${article.providerKey}.png`"
                                        :alt="article.providerName" :title="article.providerName"
                                        :data-bs-original-title="article.providerName" v-tooltip /> </td>
                                <td>
                                    <img class="img-subprovider" @error="setAltImg"
                                        :src="`/images/providers/${getSubproviderImage(article.connectivitySupplierName?.toLowerCase())}.png`"
                                        :alt="article.connectivitySupplierName"
                                        :title="article.connectivitySupplierName"
                                        :data-bs-original-title="article.connectivitySupplierName" v-tooltip />
                                </td>
                                <td>{{ bytesFormatter(article.bearer) }} </td>
                                <td>{{ bytesFormatter(article.speed) }} </td>
                                <td>{{ GBP.format(article.priceInstall) }} </td>
                                <td>{{ GBP.format(article.priceMonthly) }} </td>
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
                                    <button
                                        v-if="modelValue.items.find((item) => item.data.code == article.code)"
                                        class="btn btn-primary me-2 py-1"
                                        @click="checkProduct(article)"
                                    >
                                        <font-awesome-icon :icon="['fas', 'check']" />
                                        Added
                                    </button>
                                    <button
                                        v-else
                                        class="btn btn-outline-primary me-2 py-1"
                                        @click="checkProduct(article)"
                                    >
                                        <font-awesome-icon :icon="['fas', 'plus']" />
                                        Add
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Paginator
                        v-model:first="meta.first"
                        :rows="meta.limit"
                        :pageLinkSize="5"
                        :totalRecords="meta.total"
                        :rowsPerPageOptions="[10, 25, 50]"
                        @page="handlePaginator"
                        class="custom-paginator"
                    >
                    </Paginator>
                </div>
                <div class="d-flex justify-content-end align-items-center my-5">
                    <p class="fs-4 me-3 mb-0"><b>Add {{ modelValue.items.length }} products</b></p>
                    <button v-if="modelValue.items.length == 0" class="btn btn-primary" type="button" disabled>
                        Continue
                        <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                    </button>
                    <a v-else class="btn btn-primary" type="submit" :href="route('quotes.show', { quote: modelValue.id })">
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
                    <Spinner class="mt-5 mb-4 d-flex justify-content-center"/>
                    <h4><strong>Finding your available products</strong></h4>
                    <h6>This may take a few moments, the perfect <br> chance to grab a coffee!</h6>
                    <div class="cpq-bar-bg">
                        <div class="cpq-bar"></div>
                    </div>
                    <p>
                        <strong>{{ meta.overallTotal }}</strong>
                        results retrieved so far
                        <a href="#" class="text-dark" @click.prevent="showLoadingModal=false">view now</a>
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
    animation: progressBar 100s linear forwards;
}

</style>
