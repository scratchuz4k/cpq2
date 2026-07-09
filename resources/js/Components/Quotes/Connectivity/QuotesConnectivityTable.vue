<script setup>
import _ from 'lodash';
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import { ref, reactive, computed, watch, onMounted } from 'vue';
import MultiSelect from 'primevue/multiselect';
import Dialog from 'primevue/dialog';
import format from '@/Composables/format.js';
import Spinner from '@/Components/Spinner.vue';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import TriStateToggle from '@/Components/TriStateToggle.vue';
import { remove, add } from '@/Composables/useConnectivityItem';
import { quote } from '@/Composables/useQuote';
import SvgIcon from '@/Components/Custom/SvgIcon.vue';
import NewDatatable from '@/Components/Datatables/NewDatatable.vue';

const props = defineProps({
    modelValue: Object,
    needsAddons: Boolean,
    currentStep: Number,
    productType: {
        type: String,
        default: 'Primary Product'
    },
    route: {
        type: String,
        default: 'cloudmarket/products'
    },
    hardwareProductType: {
        type: String,
        default: 'Primary Hardware Product'
    }
});

const hasProduct = () => {
    return quote.items && quote.items.find((x) => x.data.type == props.productType);
};

const showLoadingModal = ref(false);
const showLowestCost = ref(true);
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
    showContended.value = null;
};

const checkProduct = (article) => {
    let item = quote.items.find((item) => item.product_code == article.code);
    let index = quote.items.findIndex((item) => item.product_code == article.code);
    article.type = props.productType;

    if (props.productType !== 'Primary Product') {
        const primaryProduct = quote.items.find((x) => x.data.type == 'Primary Product');
        article.contractTermMonths = article.contractTermMonths ?? primaryProduct?.contract_length;
    }

    if (index == -1) {
        add(article);
    } else {
        remove(item);
    }
};

const productDisplay = (value, data) => {
    const parsed = format.productName(value);
    return {
        name: parsed.name,
        router: data?.additionalInfo?.['router'] ?? parsed.router
    };
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
    errors.value = tempMeta.errors ?? [];
    meta.total = tempMeta.total;
    meta.overallTotal = tempMeta.overall_total;
    meta.isRequestCompleted = tempMeta.is_request_completed;

    if (meta.isRequestCompleted) {
        showLoadingModal.value = false;
    }

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
    (() => {
        const tableId = `cloudmarket-products-${props.productType.split(' ')[0].toLowerCase()}`;
        const builder = new DatatableOptions(props.route, tableId)
            .setSort('asc', 'priceMonthly_spa')
            .addColumn('productName', 'Name', { isSticky: true })
            .addColumn('providerName', 'Provider')
            .addColumn('connectivitySupplierGroup', 'Subprovider')
            .addColumn('isManaged', 'Managed')
            .addColumnFilter('isManaged', 'checkbox')
            .addColumn('bearer', 'Bearer')
            .addColumn('speed', 'Speed')
            .addColumn('priceInstall_spa', 'Set Up', { isSearchable: false })
            .addColumn('priceMonthly_spa', 'Monthly', { isSearchable: false })
            .addColumn('priceECC', 'ECC')
            .addColumn('contractTermMonths', 'Contract Term');

        if (props.productType === 'Primary Product') {
            builder.addColumn('isContended', 'Contended');
            builder.addColumnFilter('isContended', 'checkbox');
        }

        return builder
            .addColumn('connectivityType', 'Connectivity')
            .addColumn('technologyType', 'Technology')
            .addColumnFilter('technologyType', 'multiselect', ['Fibre', 'Copper', 'Wireless'])
            .addColumn('actions', '', { style: { position: 'sticky', right: 0, width: '60px' } });
    })()
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
    if (showLowestCost.value) {
        options.addQuery('*', 'lowest-cost', 1);
    }
    if (showContended.value !== null) {
        options.addQuery('isContended', 'equals', showContended.value ? 1 : 0);
    }
};

watch([filters, showLowestCost, showContended], rebuildQuery, { deep: true, immediate: true });

// Drives the generic filter-picker loop in the template. `counted` fields render their raw
// facet keys with a result count per option; the rest render pre-labelled {label, value} options
// (either static, like Managed, or built server-side, like the min. bearer/speed/contract-length lists).
const filterFields = [
    {
        key: 'isManaged',
        label: 'Managed',
        control: MultiSelect,
        options: [
            { label: 'Yes', value: '1' },
            { label: 'No', value: '0' },
            { label: 'N/A', value: 'null' }
        ]
    },
    { key: 'providerName', label: 'Provider', control: MultiSelect, counted: true },
    { key: 'connectivitySupplierGroup', label: 'Subprovider', control: MultiSelect, counted: true },
    { key: 'bearer', label: 'Min. Bearer', control: Select },
    { key: 'formattedSpeed', label: 'Min. Speed', control: Select },
    { key: 'contractTermMonths', label: 'Contract Length', control: MultiSelect },
    { key: 'connectivityType', label: 'Connectivity', control: MultiSelect, counted: true, excludeNull: true },
    { key: 'technologyType', label: 'Technology', control: MultiSelect, counted: true, excludeNull: true }
];

const filterOptions = (field) => {
    if (field.options) {
        return field.options;
    }
    if (field.counted) {
        const keys = Object.keys(filterList[field.key]);
        return field.excludeNull ? keys.filter((key) => key !== 'null') : keys;
    }
    return filterList[field.key];
};

const getFreshData = async () => {
    clearCache.value = true;
    await tableRef.value?.getData();
    clearCache.value = false;
};

onMounted(() => {
    showLoadingModal.value = hasProduct() ? false : true;
});

emitter.on(`${props.productType.split(' ')[0].toLowerCase()}-product:removed`, () => tableRef.value?.getData());
</script>
<template>
    <div v-if="!hasProduct()">
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
                            <div v-for="field in filterFields" :key="field.key" class="col-lg-3 col-12 px-2 mb-2">
                                <label :for="field.key + '-filter'" class="form-label mb-0">{{ field.label }}</label>
                                <component
                                    :is="field.control"
                                    :id="field.key + '-filter'"
                                    v-model="filters[field.key].value"
                                    style="width: 100%"
                                    :options="filterOptions(field)"
                                    :optionLabel="field.counted ? undefined : 'label'"
                                    :optionValue="field.counted ? undefined : 'value'"
                                    placeholder="All"
                                    size="small"
                                    class="me-3 custom-multiselect"
                                >
                                    <template #option="slotProps">
                                        <div style="font-size: 14px; line-height: 1.5rem">
                                            {{ field.counted ? slotProps.option : slotProps.option.label }}
                                            <template v-if="field.counted">
                                                ( {{ filterList[field.key][slotProps.option] }} )
                                            </template>
                                        </div>
                                    </template>
                                </component>
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
                        :poll-interval="hasProduct() ? null : 15000"
                        @update:meta="(meta) => setMeta(meta)"
                    >
                        <template #productName="{ value, data }">
                            <template v-for="display in [productDisplay(value, data)]" :key="data.code">
                                <p class="small overflow-hidden text-truncate" :title="display.name">
                                    {{ display.name }}
                                    <span
                                        v-if="display.router"
                                        v-tooltip
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-html="true"
                                        :data-bs-original-title="'<b>Primary Router Model:</b> ' + display.router"
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
                                        (data?.additionalInfo?.['addon_install'] && data.amortisation
                                            ? '<br/>' + data?.additionalInfo?.['addon_install']
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
                            <p class="small">{{ value ? value + ' Months' : 'N/A' }}</p>
                        </template>
                        <template #connectivityType="{ value, data, index }">
                            <p class="small">{{ value ?? 'N/A' }}</p>
                        </template>
                        <template #technologyType="{ value, data, index }">
                            <p class="small">{{ value ?? 'N/A' }}</p>
                        </template>
                        <template #actions="{ value, data, index }">
                            <div class="text-end">
                                <button
                                    v-if="!quote.items.find((x) => x.product_code == data.code)"
                                    class="btn btn-outline-primary btn-connectivity"
                                    @click="checkProduct(data)"
                                >
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

    <QuoteConnectivityAddonsTable
        v-if="props.needsAddons && hasProduct()"
        v-model="props.modelValue"
        :currentStep="props.currentStep"
        :productType="props.hardwareProductType"
        route="cloudmarket/products/addons"
        title="Hardware"
        :pollInterval="4000"
        :showCategoryBadge="false"
        filterPrimaryCircuitHardware
        showRequiredIndicator
        elementId="pcx-its-hardware"
    >
    </QuoteConnectivityAddonsTable>
</template>

<style scoped>
tr.active td {
    background-color: var(--light-teal);
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
    padding: 0 5px !important;
    margin: 0 !important;
}

.btn-connectivity:hover {
    background: none !important;
    background-color: none !important;
    color: var(--color-primary) !important;
    border: 0 !important;
}
</style>
