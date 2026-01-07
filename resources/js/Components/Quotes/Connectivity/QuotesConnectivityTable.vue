<script setup>
import _ from 'lodash';
import axios from 'axios';
import Paginator from 'primevue/paginator';
import { ref, reactive, watch, onMounted } from 'vue';
import MultiSelect from 'primevue/multiselect';
import Dialog from 'primevue/dialog';
import format from '@/Composables/format.js';
import Spinner from '@/Components/Spinner.vue';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import TriStateToggle from '@/Components/TriStateToggle.vue';
import queryString from 'query-string';
import { remove, add } from '@/Composables/useConnectivityItem';
import { quote, getQuote } from '@/Composables/useQuote';

const props = defineProps({
    modelValue: Object,
    needsAddons: Boolean,
    currentStep: Number
});

let timeoutId = null;
const isLoading = ref(true);
const showLoadingModal = ref(true);
const showLowestCost = ref(true);
const showQuoted = ref(false);
const showFilters = ref(true);
const showContended = ref(null);
const showManaged = ref(null);
const articles = ref([]);
const errors = ref([]);
const selectedFilters = ref([]);

const filters = reactive({
    providerName: {
        operator: 'in',
        value: null,
        name: 'Provider'
    },
    connectivitySupplierGroup: {
        operator: 'in',
        value: null,
        name: 'Subprovider'
    },
    contractTermMonths: {
        operator: 'in',
        value: quote.configs?.contract_length ? quote.configs.contract_length.split(',') : null,
        name: 'Contract Length'
    },
    bearer: {
        operator: 'greater_than_or_equal_to',
        value: quote.configs?.bearer ?? null,
        name: 'Bearer'
    },
    speed: {
        operator: 'greater_than_or_equal_to',
        value: quote.configs?.speed ?? null,
        name: 'Speed'
    }
});

const filterList = reactive({
    providerName: [],
    connectivitySupplierGroup: [],
    contractTermMonths: [],
    bearer: [],
    speed: []
});

const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 10,
    isRequestCompleted: false
});

const getArticles = async (clearCache = false, runTimeout = false) => {
    isLoading.value = true;
    let temp = [];
    Object.keys(filters).forEach((key) => {
        temp.push({
            column: key,
            operator: filters[key].operator,
            value: filters[key].value ?? null
        });
    });
    if (showQuoted.value) {
        let items = quote.items.length
            ? quote.items.filter((item) => item.product_code).map((item) => item.product_code)
            : ['#'];
        temp.push({ column: 'code', operator: 'in', value: items });
    }
    if (showLowestCost.value) {
        temp.push({ column: '*', operator: 'lowest-cost', value: true });
    }

    if (showContended.value !== null) {
        temp.push({ column: 'isContended', operator: 'equals', value: showContended.value });
    }

    if (showManaged.value !== null) {
        temp.push({ column: 'isManaged', operator: 'equals', value: showManaged.value });
    }

    if (clearCache) {
        showLoadingModal.value = true;
    }

    let query = queryString.stringify({
        quote_id: props.modelValue.id,
        filters: JSON.stringify(temp),
        page: meta.page,
        perPage: meta.limit,
        order: sort.order,
        orderBy: sort.field,
        account_id: props.modelValue.account_id,
        clear_cache: clearCache ? 1 : 0
    });

    try {
        let res = await axios.get('/api/cloudmarket/products?' + query);

        errors.value = res.data.errors ?? [];
        articles.value = res.data.data ?? [];
        articles.value = articles.value.map((article) => {
            if (article.productName.includes('with router:')) {
                const productInfo = article.productName.split('with router:');
                article.productName = productInfo[0].trim();
                if (productInfo.length > 1 && productInfo[1].trim() !== 'n/a') {
                    article.additionalInfo = article.additionalInfo || {};
                    article.additionalInfo['router'] = productInfo[1].trim();
                }
            }

            return article;
        });

        isLoading.value = false;
        if (!showLoadingModal.value && meta.overallTotal !== res.data.overall_total && meta.overallTotal > 0) {
            notyf.success('New products added!');
        }
        meta.first = (meta.page - 1) * meta.limit;
        meta.total = res.data.total;
        meta.overallTotal = res.data.overall_total;
        meta.isRequestCompleted = !!res.data.finished_at;

        Object.assign(filterList, res.data.filters);

        const configSpeed = quote.configs?.speed;
        if (configSpeed && !Object.prototype.hasOwnProperty.call(filterList.speed, configSpeed)) {
            filterList.speed[configSpeed] = 0;
        }

        const configBearer = quote.configs?.bearer;
        if (configBearer && !Object.prototype.hasOwnProperty.call(filterList.bearer, configBearer)) {
            filterList.bearer[configBearer] = 0;
        }

        const contractLengths = quote.configs?.contract_length ? quote.configs.contract_length.split(',') : [];
        contractLengths.forEach((length) => {
            if (length && !Object.prototype.hasOwnProperty.call(filterList.contractTermMonths, length)) {
                filterList.contractTermMonths[length] = 0;
            }
        });

        let bearerFilters = [];
        Object.keys(filterList.bearer).forEach((key) => {
            bearerFilters.push({
                label: format.speed(key),
                value: key
            });
        });
        filterList.bearer = bearerFilters;

        let speedFilters = [];
        Object.keys(filterList.speed).forEach((key) => {
            speedFilters.push({
                label: format.speed(key),
                value: key
            });
        });
        filterList.speed = speedFilters;

        selectedFilters.value = Object.fromEntries(
            Object.entries(filters).filter(([key, filter]) => filter.value !== null)
        );

        if (!meta.isRequestCompleted && runTimeout) {
            timeoutId = setTimeout(() => {
                if (hasPrimaryProduct()) {
                    isLoading.value = false;
                    showLoadingModal.value = false;
                    meta.isRequestCompleted = true;
                    return;
                }
                getArticles(false, true);
            }, 15000);
            return;
        } else {
            showLoadingModal.value = false;
        }
    } catch (error) {
        console.error(error);
        notyf.error(error.message);
        isLoading.value = false;
        meta.isRequestCompleted = true;
        showLoadingModal.value = false;
    }
};

const setAltImg = (event) => {
    event.target.src = '/images/no_image.png';
};

const sort = reactive({
    field: 'priceMonthly_spa',
    order: 'asc'
});

const orderBy = (column) => {
    let changeSort = sort.order == 'asc' ? 'desc' : 'asc';
    sort.order = sort.field === column ? changeSort : ['speed', 'bearer'].includes(column) ? 'desc' : sort.order;
    sort.field = column;
    getArticles();
};

const resetFilters = () => {
    Object.keys(filters)
        .filter((key) => key !== 'productName')
        .forEach((key) => {
            filters[key].value = null;
        });

    showLowestCost.value = true;
    showQuoted.value = false;
    showContended.value = null;
    showManaged.value = null;
};

const checkProduct = (article) => {
    let item = quote.items.find((item) => item.product_code == article.code);
    let index = quote.items.findIndex((item) => item.product_code == article.code);
    article.type = 'Primary Product';

    if (index == -1) {
        add(article);
        clearTimeout(timeoutId);
    } else {
        remove(item);
    }
};

const hasPrimaryProduct = () => {
    return quote.items && quote.items.find((x) => x.data.type == 'Primary Product');
};

const removeFilter = (selectedFilter, value) => {
    if (Array.isArray(selectedFilter.value)) {
        let index = selectedFilter.value.findIndex((x) => x === value);
        selectedFilter.value.splice(index, 1);
    } else {
        selectedFilter.value = null;
    }
};

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    getArticles();
};

watch(
    [filters, showQuoted, showLowestCost, showContended, showManaged],
    _.debounce(function () {
        meta.page = 1;
        getArticles();
    }, 500)
);

emitter.on('primary-product:removed', () => getArticles(false, true));
window.addEventListener('beforeunload', () => {
    clearTimeout(timeoutId); // Cancel timeout on page navigation or refresh
});

onMounted(() => {
    getArticles(false, true);
});
</script>
<template>
    <div v-if="!hasPrimaryProduct()">
        <div class="row mb-2">
            <div class="col position-relative">
                <div class="row mb-3" v-if="errors.length">
                    <div class="col">
                        <InfoBox class="mb-4 py-0" severity="info">
                            <div class="d-flex justify-content-between">
                                <div class="small">
                                    <div class="mb-2 fw-bold">There were issues retrieving data from:</div>
                                    <div v-for="error in errors" class="mb-1">
                                        <span class="small fw-bold bg-light rounded-2 py-1 px-2 me-1 text-capitalize">{{
                                            error?.provider
                                        }}</span
                                        ><i>{{ error?.response }}</i>
                                    </div>
                                </div>
                                <div v-if="meta.isRequestCompleted" class="small d-flex align-items-center">
                                    <button class="btn btn-sm btn-outline-primary" @click="getArticles(true, true)">
                                        Get fresh data
                                        <font-awesome-icon :icon="['fas', 'refresh']" class="ps-2"></font-awesome-icon>
                                    </button>
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
                                    <font-awesome-icon
                                        v-if="!meta.isRequestCompleted"
                                        :icon="['fas', 'refresh']"
                                        class="fa-spin me-2"
                                    ></font-awesome-icon
                                    >Filters
                                    <span class="fw-normal">({{ meta.total }})</span>
                                </p>
                            </div>
                            <ul class="d-flex list-unstyled ms-2 mb-0">
                                <li>
                                    <template v-for="(selectedFilter, column) in selectedFilters">
                                        <Badge
                                            v-for="value in column === 'productName'
                                                ? []
                                                : (Array.isArray(selectedFilter.value)
                                                      ? selectedFilter.value
                                                      : [selectedFilter.value]
                                                  ).filter((v) => v !== null)"
                                            :value="
                                                ['speed', 'bearer'].includes(column)
                                                    ? 'Min. ' + _.capitalize(column) + ': ' + format.speed(value)
                                                    : ['contractTermMonths'].includes(column)
                                                      ? value + ' Months'
                                                      : value
                                            "
                                            class="px-3 py-1"
                                            :title="selectedFilter.name"
                                        >
                                            <template #close-button>
                                                <font-awesome-icon
                                                    :icon="['fas', 'x']"
                                                    role="button"
                                                    class="ms-1 fs-7"
                                                    @click="removeFilter(selectedFilter, value)"
                                                />
                                            </template>
                                        </Badge>
                                    </template>
                                </li>
                                <li v-if="quote.configs?.managed && quote.configs?.managed != 0">
                                    <Badge
                                        :value="
                                            quote.configs?.managed == 1
                                                ? 'Managed Only'
                                                : quote.configs?.managed == 2
                                                  ? 'Unmanaged Only'
                                                  : 'Managed and Unmanaged'
                                        "
                                        class="px-3 py-1"
                                    >
                                        <font-awesome-icon
                                            :icon="['fas', 'shield-alt']"
                                            class="ms-1 fs-7"
                                        ></font-awesome-icon>
                                    </Badge>
                                </li>
                            </ul>
                            <div
                                v-if="
                                    quote.configs?.speed ||
                                    quote.configs?.bearer ||
                                    quote.configs?.contract_length ||
                                    quote.configs?.managed !== '0'
                                "
                                class="d-flex align-self-end justify-content-around mb-2"
                            >
                                <tooltip-with-icon
                                    class="ms-2 mt-1"
                                    :info_text="'The current results are based on predefined filters. For a more complete view, please go back and select a broader filter selection.'"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div id="filters" v-if="showFilters" class="p-0 m-0 mt-2 mb-4 row">
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-provider" class="form-label">Provider</label>
                        <MultiSelect
                            id="provider-multiselect"
                            v-model="filters['providerName'].value"
                            style="width: 100%"
                            :options="Object.keys(filterList.providerName)"
                            placeholder="All"
                            size="small"
                            class="custom-multiselect"
                        >
                            <template #option="slotProps">
                                <div style="font-size: 14px; line-height: 1.5rem">
                                    {{ slotProps.option }} ( {{ filterList.providerName[slotProps.option] }} )
                                </div>
                            </template>
                        </MultiSelect>
                    </div>
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-subprovider" class="form-label">Subprovider</label>
                        <MultiSelect
                            id="subprovider-multiselect"
                            v-model="filters['connectivitySupplierGroup'].value"
                            style="width: 100%"
                            :options="Object.keys(filterList.connectivitySupplierGroup)"
                            placeholder="All"
                            size="small"
                            class="me-3 custom-multiselect"
                        >
                            <template #option="slotProps">
                                <div style="font-size: 14px; line-height: 1.5rem">
                                    {{ slotProps.option }} (
                                    {{ filterList.connectivitySupplierGroup[slotProps.option] }} )
                                </div>
                            </template>
                        </MultiSelect>
                    </div>
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-contract" class="form-label">Contract Length</label>
                        <MultiSelect
                            id="contract-length-multiselect"
                            v-model="filters['contractTermMonths'].value"
                            style="width: 100%"
                            :options="Object.keys(filterList.contractTermMonths)"
                            placeholder="All"
                            size="small"
                            class="me-3 custom-multiselect"
                        >
                            <template #option="slotProps">
                                <div style="font-size: 14px; line-height: 1.5rem">{{ slotProps.option }} Months</div>
                            </template>
                        </MultiSelect>
                    </div>
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-contract" class="form-label">Min. Bearer</label>
                        <Select
                            id="bearer-multiselect"
                            v-model="filters['bearer'].value"
                            style="width: 100%"
                            :options="filterList.bearer"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="All"
                            size="small"
                            class="me-3 custom-select"
                        >
                            <template #option="slotProps">
                                <div style="font-size: 14px; line-height: 1.5rem">
                                    {{ slotProps.option.label }}
                                </div>
                            </template>
                        </Select>
                    </div>
                    <div class="col-lg-2 col-12 p-0 pe-1">
                        <label for="filter-contract" class="form-label">Min. Speed</label>
                        <Select
                            id="speed-multiselect"
                            v-model="filters['speed'].value"
                            style="width: 100%"
                            :options="filterList.speed"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="All"
                            size="small"
                            class="me-3 custom-select"
                        >
                            <template #option="slotProps">
                                <div style="font-size: 14px; line-height: 1.5rem">
                                    {{ slotProps.option.label }}
                                </div>
                            </template>
                        </Select>
                    </div>
                    <div
                        class="col-lg-2 col-12 d-flex align-self-end justify-content-around"
                        style="margin-bottom: 2px"
                    >
                        <button class="btn btn-secondary btn-sm mt-2 me-2 w-100" @click="resetFilters()">Reset</button>
                        <div class="advanced-settings pt-2 w-100">
                            <button
                                id="btn-more-filters"
                                class="btn btn-secondary dropdown-toggle lh-1 w-100"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                data-bs-auto-close="outside"
                            >
                                <font-awesome-icon :icon="['fas', 'gear']"></font-awesome-icon>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end mt-1 p-1">
                                <li>
                                    <div class="checkbox d-flex py-1 px-2">
                                        <ToggleSwitch class="me-2" id="filter-lower-cost" v-model="showLowestCost">
                                            <template #handle>
                                                <font-awesome-icon
                                                    :icon="
                                                        showLowestCost
                                                            ? ['fas', 'circle-check']
                                                            : ['far', 'circle-xmark']
                                                    "
                                                    class="text-primary"
                                                />
                                            </template>
                                        </ToggleSwitch>
                                        <label class="form-check-label" for="filter-lower-cost">
                                            Show only lowest cost products
                                        </label>
                                    </div>
                                    <div class="checkbox d-flex py-1 px-2">
                                        <ToggleSwitch class="me-2" id="filter-show-quoted" v-model="showQuoted">
                                            <template #handle>
                                                <font-awesome-icon
                                                    :icon="
                                                        showQuoted ? ['fas', 'circle-check'] : ['far', 'circle-xmark']
                                                    "
                                                    class="text-primary"
                                                />
                                            </template>
                                        </ToggleSwitch>
                                        <label class="form-check-label" for="filter-show-quoted">
                                            Show only quoted items
                                        </label>
                                    </div>
                                    <div class="checkbox d-flex py-1 px-2">
                                        <TriStateToggle v-model="showContended" id="filter-show-contended">
                                            <template #handle>
                                                <font-awesome-icon
                                                    :icon="
                                                        showContended === true
                                                            ? ['fas', 'circle-check']
                                                            : showContended === false
                                                              ? ['far', 'circle-xmark']
                                                              : ['far', 'circle']
                                                    "
                                                    class="text-primary"
                                                />
                                            </template>
                                        </TriStateToggle>
                                        <label class="form-check-label" for="filter-show-contended">
                                            Show contended products
                                        </label>
                                    </div>
                                    <div class="checkbox d-flex py-1 px-2">
                                        <TriStateToggle v-model="showManaged" id="filter-show-managed">
                                            <template #handle>
                                                <font-awesome-icon
                                                    :icon="
                                                        showManaged === true
                                                            ? ['fas', 'circle-check']
                                                            : showManaged === false
                                                              ? ['far', 'circle-xmark']
                                                              : ['far', 'circle']
                                                    "
                                                    class="text-primary"
                                                />
                                            </template>
                                        </TriStateToggle>
                                        <label class="form-check-label" for="filter-show-managed">
                                            Show managed products
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div v-if="!meta.isRequestCompleted" data-provider="loading" class="position-relative">
                    <div class="cpq-progress-bar" style="position: absolute; z-index: 2">
                        <div class="cpq-bar"></div>
                    </div>
                </div>
                <div id="no-results" v-if="!articles.length">
                    <div class="alert alert-secondary mt-3">
                        No results match your filters.
                        <button @click="resetFilters" class="btn btn-primary btn-sm js-clear-filters ms-3">
                            Clear filters
                        </button>
                    </div>
                </div>
                <div v-if="articles.length" class="table-wrapper">
                    <table class="table-connectivity table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th
                                    id="table-header-product"
                                    class="ps-3"
                                    :style="{
                                        whiteSpace: 'nowrap',
                                        width: '380px'
                                    }"
                                    @click="orderBy('productName')"
                                >
                                    Product
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'productName'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    id="table-header-provider"
                                    class="text-center"
                                    :style="{ whiteSpace: 'nowrap' }"
                                    @click="orderBy('providerName')"
                                >
                                    Provider
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'providerName'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    id="table-header-subprovider"
                                    class="text-center"
                                    :style="{ whiteSpace: 'nowrap' }"
                                    @click="orderBy('connectivitySupplierName')"
                                >
                                    Subprovider
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'connectivitySupplierName'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    id="table-header-bearer"
                                    class="text-center"
                                    :style="{
                                        whiteSpace: 'nowrap'
                                    }"
                                    @click="orderBy('bearer')"
                                >
                                    Bearer
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'bearer'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    id="table-header-speed"
                                    class="text-center"
                                    :style="{
                                        whiteSpace: 'nowrap'
                                    }"
                                    @click="orderBy('speed')"
                                >
                                    Speed
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'speed'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    id="table-header-setup"
                                    class="text-center"
                                    :style="{
                                        whiteSpace: 'nowrap'
                                    }"
                                    @click="orderBy('priceInstall_spa')"
                                >
                                    Set Up
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'priceInstall_spa'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    id="table-header-monthly"
                                    class="text-center"
                                    :style="{
                                        whiteSpace: 'nowrap'
                                    }"
                                    @click="orderBy('priceMonthly_spa')"
                                >
                                    Monthly
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'priceMonthly_spa'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    id="table-header-ecc"
                                    class="text-center"
                                    :style="{
                                        whiteSpace: 'nowrap'
                                    }"
                                    @click="orderBy('priceECC')"
                                >
                                    ECC
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'priceECC'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    id="table-header-contract-term"
                                    class="text-center"
                                    :style="{
                                        whiteSpace: 'nowrap'
                                    }"
                                    @click="orderBy('contractTermMonths')"
                                >
                                    Contract Term
                                    <span class="text-center d-inline-block align-middle" style="width: 20px">
                                        <template v-if="sort.field == 'contractTermMonths'">
                                            <font-awesome-icon
                                                v-if="sort.order == 'desc'"
                                                :icon="['fa-solid', 'fa-sort-amount-up-alt']"
                                            />
                                            <font-awesome-icon v-else :icon="['fa-solid', 'fa-sort-amount-down-alt']" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :icon="['fa-solid', 'fa-sort']" />
                                        </template>
                                    </span>
                                </th>
                                <th
                                    v-if="!hasPrimaryProduct()"
                                    :style="{
                                        width: '130px'
                                    }"
                                ></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="connectivity-table-item"
                                v-for="(article, index) in articles"
                                style="text-align: center"
                            >
                                <td class="lh-sm text-left ps-3">
                                    {{ article.productName }}
                                    <span
                                        v-if="article?.additionalInfo?.['router']"
                                        v-tooltip
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-html="true"
                                        :data-bs-original-title="
                                            '<b>Primary Router Model:</b> ' + article.additionalInfo['router']
                                        "
                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
                                    >
                                        <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                    </span>
                                    <span
                                        v-if="article?.isContended"
                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-html="true"
                                        data-bs-original-title="<b>Contended Service:</b> Contended, wires only full fibre service with symmetrical upload and download speeds"
                                        v-tooltip
                                    >
                                        <font-awesome-icon :icon="['fas', 'server']"></font-awesome-icon>
                                    </span>
                                    <span
                                        v-if="article?.additionalInfo?.['Type'] == 'Fibre Flex 200+'"
                                        v-tooltip
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-html="true"
                                        data-bs-original-title="<b>Burstable Service:</b> This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-burstable"
                                    >
                                        <font-awesome-icon :icon="['fas', 'bolt']"></font-awesome-icon>
                                    </span>
                                    <span
                                        v-if="article?.additionalInfo?.['addon_ipv4']"
                                        v-tooltip
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        :data-bs-original-title="article?.additionalInfo?.['addon_ipv4']"
                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm addon-ipv4"
                                    >
                                        <font-awesome-icon :icon="['fas', 'network-wired']"></font-awesome-icon>
                                    </span>
                                </td>
                                <td>
                                    <img
                                        class="img-provider"
                                        @error="setAltImg"
                                        :src="`/images/providers/${format.toLowerCase(article.providerKey)}.png`"
                                        :alt="article.providerName"
                                        :title="article.providerName"
                                        :data-bs-original-title="article.providerName"
                                        v-tooltip
                                    />
                                </td>
                                <td>
                                    <img
                                        class="img-subprovider"
                                        @error="setAltImg"
                                        :src="`/images/providers/${format.toLowerCase(
                                            article.connectivitySupplierGroup
                                        )}.png`"
                                        :alt="article.connectivitySupplierName"
                                        :title="article.connectivitySupplierName"
                                        :data-bs-original-title="article.connectivitySupplierName"
                                        v-tooltip
                                    />
                                </td>
                                <td>{{ format.speed(article.bearer) }}</td>
                                <td>{{ format.speed(article.speed) }}</td>
                                <td :id="'line-' + index + '-price-install'">
                                    {{ format.GBP(article.priceInstall_spa) }}
                                    <span
                                        v-if="article?.additionalInfo?.['addon_install'] && !article.amortisation"
                                        :data-bs-original-title="article?.additionalInfo?.['addon_install']"
                                        data-bs-html="true"
                                        v-tooltip
                                    >
                                        <SvgIcon width="20" height="20" name="info" />
                                    </span>
                                </td>
                                <td :id="'line-' + index + '-price-monthly'">
                                    <template v-if="!article.amortisation">
                                        {{ format.GBP(article.priceMonthly_spa) }}
                                    </template>
                                    <span
                                        v-else
                                        :data-bs-original-title="
                                            '<b>Initial Setup Cost:</b> ' +
                                            format.GBP(article.amortisation_install) +
                                            (article?.additionalInfo?.['addon_install'] && article.amortisation
                                                ? '<br/>' + article?.additionalInfo?.['addon_install']
                                                : '') +
                                            '<br/><b>Monthly Amortisation:</b> ' +
                                            format.GBP(article.amortisation)
                                        "
                                        data-bs-html="true"
                                        v-tooltip
                                    >
                                        {{ format.GBP(article.priceMonthly_spa) }}
                                        <font-awesome-icon class="ms-1" :icon="['fas', 'coins']"></font-awesome-icon>
                                    </span>
                                </td>
                                <td>
                                    <template v-if="article.priceECC">
                                        {{ format.GBP(article.priceECC) }}
                                    </template>
                                    <span
                                        v-else
                                        title="Subject to Survey"
                                        class="h6"
                                        data-bs-original-title="Subject to Survey"
                                        v-tooltip
                                    >
                                        <font-awesome-icon :icon="['far', 'circle-question']"></font-awesome-icon>
                                    </span>
                                </td>
                                <td>{{ article.contractTermMonths }} Months</td>
                                <td class="text-end" v-if="!hasPrimaryProduct()">
                                    <button class="btn btn-outline-primary me-2 py-1" @click="checkProduct(article)">
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
            </div>
        </div>
    </div>

    <Dialog
        v-model:visible="showLoadingModal"
        modal
        :show-header="false"
        class="loading-modal px-4"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
    >
        <div class="panel">
            <div class="row">
                <div class="text-center">
                    <Spinner class="mt-5 mb-4 d-flex justify-content-center" />
                    <h4><strong>Finding your available products</strong></h4>
                    <h6>
                        This may take a few moments, the perfect <br />
                        chance to grab a coffee!
                    </h6>
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

    <QuoteConnectivityHardwareTable
        v-if="props.needsAddons && quote.items?.find((x) => x.data.type == 'Primary Product')"
        v-model="props.modelValue"
        productType="Primary Hardware Product"
        :currentStep="props.currentStep"
    ></QuoteConnectivityHardwareTable>
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
    opacity: 0.5;
}

.clear-filter > p {
    font-size: 14px;
}

.router-model,
.is-contended,
.is-burstable,
.addon-ipv4 {
    font-size: 0.75rem !important;
}

.loading-modal .cpq-bar-bg {
    height: 8px;
    border-radius: 4px;
    background-color: #d9d9d9;
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
