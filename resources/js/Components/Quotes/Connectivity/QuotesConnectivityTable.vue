<script setup>
import _ from 'lodash';
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import { ref, reactive, computed, watch } from 'vue';
import MultiSelect from 'primevue/multiselect';
import Dialog from 'primevue/dialog';
import format from '@/Composables/format.js';
import Spinner from '@/Components/Spinner.vue';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import TriStateToggle from '@/Components/TriStateToggle.vue';
import { remove, add } from '@/Composables/useConnectivityItem';
import { quote, getQuote } from '@/Composables/useQuote';
import SvgIcon from '@/Components/Custom/SvgIcon.vue';
import NewDatatable from '@/Components/Datatables/NewDatatable.vue';

const props = defineProps({
    modelValue: Object,
    needsAddons: Boolean,
    currentStep: Number
});

const showLoadingModal = ref(true);
const showLowestCost = ref(true);
const showQuoted = ref(false);
const showFilters = ref(true);
const showContended = ref(null);
const errors = ref([]);
const selectedFilters = ref([]);
const clearCache = ref(false);
const tableRef = ref(null);

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
    formattedSpeed: {
        operator: 'greater_than_or_equal_to',
        value: quote.configs?.speed ?? null,
        name: 'Speed'
    },
    isManaged: {
        operator: 'in',
        value: ['1', 'null'],
        name: 'Managed'
    },
    connectivityType: {
        operator: 'in',
        value: null,
        name: 'Connectivity'
    },
    technologyType: {
        operator: 'in',
        value: null,
        name: 'Technology'
    }
});

const filterList = reactive({
    providerName: [],
    connectivitySupplierGroup: [],
    contractTermMonths: [],
    bearer: [],
    formattedSpeed: [],
    connectivityType: [],
    technologyType: []
});

const meta = reactive({
    total: 0,
    overallTotal: 0,
    isRequestCompleted: false
});

const setAltImg = (event) => {
    event.target.src = '/images/no_image.png';
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
};

const checkProduct = (article) => {
    let item = quote.items.find((item) => item.product_code == article.code);
    let index = quote.items.findIndex((item) => item.product_code == article.code);
    article.type = 'Primary Product';

    if (index == -1) {
        add(article);
    } else {
        remove(item);
    }
};

const hasPrimaryProduct = () => {
    return quote.items && quote.items.find((x) => x.data.type == 'Primary Product');
};

const formatBadgeLabel = (column, value) => {
    if (column === 'isManaged') {
        return 'Managed: ' + (value === '1' ? 'Yes' : value === '0' ? 'No' : 'N/A');
    }
    if (['formattedSpeed', 'bearer'].includes(column)) {
        return 'Min. ' + (column === 'formattedSpeed' ? 'Speed' : _.capitalize(column)) + ': ' + format.speed(value);
    }
    if (column === 'contractTermMonths') {
        return value + ' Months';
    }
    return value;
};

const removeFilter = (selectedFilter, value) => {
    if (Array.isArray(selectedFilter.value)) {
        let index = selectedFilter.value.findIndex((x) => x === value);
        selectedFilter.value.splice(index, 1);
    } else {
        selectedFilter.value = null;
    }
};

// Facets whose options must always include the quote's preselected config value
// (even if the current result set has no products at that value), so the user can see why it's filtered.
const rangeFacets = [
    {
        key: 'formattedSpeed',
        configValues: () => (quote.configs?.formattedSpeed ? [quote.configs.formattedSpeed] : []),
        label: format.speed
    },
    {
        key: 'bearer',
        configValues: () => (quote.configs?.bearer ? [quote.configs.bearer] : []),
        label: format.speed
    },
    {
        key: 'contractTermMonths',
        configValues: () => (quote.configs?.contract_length ? quote.configs.contract_length.split(',') : []),
        label: (value) => value + ' Months'
    }
];

const buildFacetOptions = ({ key, configValues, label }) => {
    configValues().forEach((value) => {
        if (value && !Object.prototype.hasOwnProperty.call(filterList[key], value)) {
            filterList[key][value] = 0;
        }
    });

    filterList[key] = Object.keys(filterList[key])
        .filter((value) => value !== 'null')
        .map((value) => ({ label: label(value), value }));
};

const setMeta = (tempMeta) => {
    const previousOverallTotal = meta.overallTotal;
    showLoadingModal.value = tempMeta.is_request_completed;
    errors.value = tempMeta.errors ?? [];
    meta.total = tempMeta.total;
    meta.overallTotal = tempMeta.overall_total;
    meta.isRequestCompleted = tempMeta.is_request_completed;

    if (previousOverallTotal > 0 && previousOverallTotal !== meta.overallTotal) {
        notyf.success('New products added!');
    }

    Object.assign(filterList, tempMeta.filters);
    rangeFacets.forEach(buildFacetOptions);

    selectedFilters.value = Object.fromEntries(
        Object.entries(filters).filter(([key, filter]) => filter.value !== null)
    );
};

const options = reactive(
    new DatatableOptions('cloudmarket/products', 'cloudmarket-products')
        .setSort('asc', 'priceMonthly_spa')
        .addColumn('productName', 'Name', { isSticky: true })
        .addColumn('providerName', 'Provider')
        .addColumn('connectivitySupplierGroup', 'Subprovider')
        .addColumn('isManaged', 'Managed')
        .addColumn('bearer', 'Bearer')
        .addColumn('speed', 'Speed')
        .addColumn('priceInstall_spa', 'Set Up')
        .addColumn('priceMonthly_spa', 'Monthly')
        .addColumn('priceECC', 'ECC')
        .addColumn('contractTermMonths', 'Contract Term')
        .addColumn('isContended', 'Contended')
        .addColumn('connectivityType', 'Connectivity')
        .addColumn('technologyType', 'Technology')
        .addColumn('actions', '', { style: { position: 'sticky', right: 0, width: '60px' } })
);

const params = computed(() => ({
    quote_id: props.modelValue.id,
    account_id: props.modelValue.account_id,
    clear_cache: clearCache.value ? 1 : 0
}));

const rebuildQuery = () => {
    options.clearQuery();
    Object.keys(filters).forEach((key) => {
        options.addQuery(key, filters[key].operator, filters[key].value ?? null);
    });
    if (showQuoted.value) {
        let items = quote.items.length
            ? quote.items.filter((item) => item.product_code).map((item) => item.product_code)
            : ['#'];
        options.addQuery('code', 'in', items);
    }
    if (showLowestCost.value) {
        options.addQuery('*', 'lowest-cost', 1);
    }
    if (showContended.value !== null) {
        options.addQuery('isContended', 'equals', showContended.value ? 1 : 0);
    }
};

watch([filters, showQuoted, showLowestCost, showContended], rebuildQuery, { deep: true, immediate: true });

const getFreshData = async () => {
    showLoadingModal.value = true;
    clearCache.value = true;
    await tableRef.value?.getData();
    clearCache.value = false;
};

emitter.on('primary-product:removed', () => tableRef.value?.getData());
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
                                    <button class="btn btn-sm btn-outline-primary" @click="getFreshData()">
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
                                    >Products
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
                                            :value="formatBadgeLabel(column, value)"
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
                            </ul>
                            <div
                                v-if="quote.configs?.speed || quote.configs?.bearer || quote.configs?.contract_length"
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
                <div id="filters" v-if="showFilters" class="p-0 m-0 mt-2 mb-3 row">
                    <div class="col-lg-10 col-12 px-1">
                        <div class="row">
                            <div class="col-lg-3 col-12 px-2 mb-2">
                                <label for="filter-managed" class="form-label mb-0">Managed</label>
                                <MultiSelect
                                    id="managed-multiselect"
                                    v-model="filters['isManaged'].value"
                                    style="width: 100%"
                                    :options="[
                                        { label: 'Yes', value: '1' },
                                        { label: 'No', value: '0' },
                                        { label: 'N/A', value: 'null' }
                                    ]"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="All"
                                    size="small"
                                    class="me-3 custom-multiselect"
                                >
                                    <template #option="slotProps">
                                        <div style="font-size: 14px; line-height: 1.5rem">
                                            {{ slotProps.option.label }}
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div class="col-lg-3 col-12 px-2 mb-2">
                                <label for="filter-provider" class="form-label mb-0">Provider</label>
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
                            <div class="col-lg-3 col-12 px-2 mb-2">
                                <label for="filter-subprovider" class="form-label mb-0">Subprovider</label>
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
                            <div class="col-lg-3 col-12 px-2 mb-2">
                                <label for="filter-contract" class="form-label mb-0">Min. Bearer</label>
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
                            <div class="col-lg-3 col-12 px-2 mb-2">
                                <label for="filter-contract" class="form-label mb-0">Min. Speed</label>
                                <Select
                                    id="speed-multiselect"
                                    v-model="filters['formattedSpeed'].value"
                                    style="width: 100%"
                                    :options="filterList.formattedSpeed"
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
                            <div class="col-lg-3 col-12 px-2 mb-2">
                                <label for="filter-contract" class="form-label mb-0">Contract Length</label>
                                <MultiSelect
                                    id="contract-length-multiselect"
                                    v-model="filters['contractTermMonths'].value"
                                    style="width: 100%"
                                    :options="filterList.contractTermMonths"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="All"
                                    size="small"
                                    class="me-3 custom-multiselect"
                                >
                                    <template #option="slotProps">
                                        <div style="font-size: 14px; line-height: 1.5rem">
                                            {{ slotProps.option.label }}
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div class="col-lg-3 col-12 px-2 mb-2">
                                <label for="filter-connectivityType" class="form-label mb-0">Connectivity</label>
                                <MultiSelect
                                    id="connectivityType-multiselect"
                                    v-model="filters['connectivityType'].value"
                                    style="width: 100%"
                                    :options="Object.keys(filterList.connectivityType).filter((key) => key !== 'null')"
                                    placeholder="All"
                                    size="small"
                                    class="me-3 custom-multiselect"
                                >
                                    <template #option="slotProps">
                                        <div style="font-size: 14px; line-height: 1.5rem">
                                            {{ slotProps.option }} (
                                            {{ filterList.connectivityType[slotProps.option] }} )
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div class="col-lg-3 col-12 px-2 mb-2">
                                <label for="filter-technologyType" class="form-label mb-0">Technology</label>
                                <MultiSelect
                                    id="technologyType-multiselect"
                                    v-model="filters['technologyType'].value"
                                    style="width: 100%"
                                    :options="Object.keys(filterList.technologyType).filter((key) => key !== 'null')"
                                    placeholder="All"
                                    size="small"
                                    class="me-3 custom-multiselect"
                                >
                                    <template #option="slotProps">
                                        <div style="font-size: 14px; line-height: 1.5rem">
                                            {{ slotProps.option }} ( {{ filterList.technologyType[slotProps.option] }} )
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-12 align-self-start justify-content-around mt-3 mb-2">
                        <div class="row">
                            <div class="col-12 d-flex align-self-end justify-content-around" style="margin-bottom: 2px">
                                <button class="btn btn-secondary btn-sm mt-2 me-2 w-100" @click="resetFilters()">
                                    Reset
                                </button>
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
                                                <ToggleSwitch
                                                    class="me-2"
                                                    id="filter-lower-cost"
                                                    v-model="showLowestCost"
                                                >
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
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!meta.isRequestCompleted" data-provider="loading" class="position-relative">
                    <div class="cpq-progress-bar" style="position: absolute; z-index: 2">
                        <div class="cpq-bar"></div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <NewDatatable
                        ref="tableRef"
                        :options="options"
                        :params="params"
                        :poll-interval="hasPrimaryProduct() ? null : 15000"
                        @update:meta="(meta) => setMeta(meta)"
                    >
                        <template #productName="{ value, data }">
                            <p class="small" :title="value">
                                {{ value }}
                                <span
                                    v-if="data?.additionalInfo?.['router']"
                                    v-tooltip
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-html="true"
                                    :data-bs-original-title="
                                        '<b>Primary Router Model:</b> ' + data.additionalInfo['router']
                                    "
                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
                                >
                                    <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                </span>
                                <span
                                    v-if="data?.isContended"
                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-html="true"
                                    data-bs-original-title="Contended Service"
                                    v-tooltip
                                >
                                    <font-awesome-icon :icon="['fas', 'server']"></font-awesome-icon>
                                </span>
                                <span
                                    v-if="data?.additionalInfo?.['Type'] == 'Fibre Flex 200+'"
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
                                    v-if="data?.additionalInfo?.['addon_ipv4']"
                                    v-tooltip
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    :data-bs-original-title="data?.additionalInfo?.['addon_ipv4']"
                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm addon-ipv4"
                                >
                                    <font-awesome-icon :icon="['fas', 'network-wired']"></font-awesome-icon>
                                </span>
                            </p>
                        </template>
                        <template #isManaged="{ value, data }">
                            <p class="small">
                                <span
                                    v-if="
                                        data.isManaged === null &&
                                        data.providerKey === 'pxc' &&
                                        data.isContended === false
                                    "
                                    title="This product is unmanaged and will become managed once a router is selected. For PXC Ethernet circuits routers are free of charge on 3 or 5 year terms."
                                    data-bs-original-title="This product is unmanaged and will become managed once a router is selected. For PXC Ethernet circuits routers are free of charge on 3 or 5 year terms."
                                    v-tooltip
                                >
                                    {{ data.isManaged == true ? 'Yes' : data.isManaged === false ? 'No' : 'N/A' }}
                                    <SvgIcon class="h6 mt-1" width="20" height="20" name="info" />
                                </span>
                                <span
                                    v-else-if="
                                        data.isManaged === true &&
                                        data.providerKey === 'its' &&
                                        data.isContended === false
                                    "
                                    title="ITS managed service includes 24/7 monitoring and a 6-hour fix SLA, however no router is included. Instead, ITS-provided fibre includes an ONT (optical network terminal) while third-party fibre (e.g. Openreach) uses the carrier's own Network Termination Equipment (NTE)."
                                    data-bs-original-title="ITS managed service includes 24/7 monitoring and a 6-hour fix SLA, however no router is included. Instead, ITS-provided fibre includes an ONT (optical network terminal) while third-party fibre (e.g. Openreach) uses the carrier's own Network Termination Equipment (NTE)"
                                    v-tooltip
                                >
                                    {{ data.isManaged == true ? 'Yes' : data.isManaged === false ? 'No' : 'N/A' }}
                                    <SvgIcon class="h6 mt-1" width="20" height="20" name="info" />
                                </span>
                                <span v-else>
                                    {{ data.isManaged == true ? 'Yes' : data.isManaged === false ? 'No' : 'N/A' }}
                                </span>
                            </p>
                        </template>
                        <template #providerName="{ value, data }">
                            <img
                                class="img-provider"
                                @error="setAltImg"
                                :src="`/images/providers/${format.toLowerCase(data.providerKey)}.png`"
                                :alt="value"
                                :title="value"
                                :data-bs-original-title="value"
                                v-tooltip
                            />
                        </template>
                        <template #connectivitySupplierGroup="{ value, data }">
                            <img
                                class="img-subprovider"
                                @error="setAltImg"
                                :src="`/images/providers/${format.toLowerCase(value)}.png`"
                                :alt="data.connectivitySupplierName"
                                :title="data.connectivitySupplierName"
                                :data-bs-original-title="data.connectivitySupplierName"
                                v-tooltip
                            />
                        </template>
                        <template #bearer="{ value, data }">
                            <p class="small">{{ format.speed(value) }}</p>
                        </template>
                        <template #speed="{ value, data }">
                            <p class="small">{{ format.speed(value) }}</p>
                        </template>
                        <template #isContended="{ value, data }">
                            <p class="small">
                                {{ data.isContended == true ? 'Yes' : data.isContended === false ? 'No' : 'N/A' }}
                            </p>
                        </template>

                        <template #priceInstall_spa="{ value, data, index }">
                            <p class="small" :id="'line-' + index + '-price-install'">
                                {{ format.GBP(value) }}
                                <span
                                    v-if="data?.additionalInfo?.['addon_install'] && !data.amortisation"
                                    :data-bs-original-title="data?.additionalInfo?.['addon_install']"
                                    data-bs-html="true"
                                    v-tooltip
                                >
                                    <SvgIcon width="20" height="20" name="info" />
                                </span>
                            </p>
                        </template>
                        <template #priceMonthly_spa="{ value, data, index }">
                            <p :id="'line-' + index + '-price-monthly'" class="small">
                                <template v-if="!data.amortisation">
                                    {{ format.GBP(value) }}
                                </template>
                                <span
                                    v-else
                                    :data-bs-original-title="
                                        '<b>Initial Setup Cost:</b> ' +
                                        format.GBP(data.amortisation_install) +
                                        (article?.additionalInfo?.['addon_install'] && data.amortisation
                                            ? '<br/>' + article?.additionalInfo?.['addon_install']
                                            : '') +
                                        '<br/><b>Monthly Amortisation:</b> ' +
                                        format.GBP(data.amortisation)
                                    "
                                    data-bs-html="true"
                                    v-tooltip
                                >
                                    {{ format.GBP(value) }}
                                    <font-awesome-icon class="ms-1" :icon="['fas', 'coins']"></font-awesome-icon>
                                </span>
                            </p>
                        </template>
                        <template #priceECC="{ value, data, index }">
                            <p class="small" v-if="value">
                                {{ format.GBP(value) }}
                            </p>
                            <p
                                v-else
                                title="Subject to Survey"
                                class="h6"
                                data-bs-original-title="Subject to Survey"
                                v-tooltip
                            >
                                <font-awesome-icon :icon="['far', 'circle-question']"></font-awesome-icon>
                            </p>
                        </template>
                        <template #contractTermMonths="{ value, data, index }">
                            <p class="small">{{ value }} Months</p>
                        </template>
                        <template #connectivityType="{ value, data, index }">
                            <p class="small">{{ value ?? 'N/A' }}</p>
                        </template>
                        <template #technologyType="{ value, data, index }">
                            <p class="small">{{ value ?? 'N/A' }}</p>
                        </template>
                        <template #actions="{ value, data, index }">
                            <div class="text-end">
                                <button class="btn btn-outline-primary btn-connectivity" @click="checkProduct(data)">
                                    <font-awesome-icon :icon="['fas', 'plus']" />
                                    Add
                                </button>
                            </div>
                        </template>
                    </NewDatatable>
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
    >
    </QuoteConnectivityHardwareTable>
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

.btn-connectivity {
    border: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
}

.btn-connectivity:hover {
    background: none !important;
    background-color: none !important;
    color: var(--color-primary) !important;
    border: 0 !important;
}
</style>
