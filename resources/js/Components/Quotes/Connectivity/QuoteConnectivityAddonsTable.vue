<template>
    <div :id="elementId" class="py-5">
        <div>
            <div class="d-flex mb-2">
                <p class="d-flex fs-5 bold me-2">
                    {{ title }} <span class="fw-normal ms-2">({{ meta.total }})</span>
                    <span
                        v-if="
                            showRequiredIndicator && (checks.primaryHardwareRequired || checks.failoverHardwareRequired)
                        "
                        class="text-primary fw-bold ps-1"
                        >*</span
                    >
                </p>
                <div v-if="isLoadingLocal">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4" v-if="items.length">
            <div class="col-3 mb-3" v-for="addon in items">
                <div
                    class="card bg-light cursor-pointer h-100"
                    :class="[quote.items.find((x) => x.product_code == addon.code) ? 'border-primary' : null]"
                    @click="checkProduct(addon)"
                >
                    <div class="card-body" :class="{ 'd-flex flex-column justify-content-between': showCategoryBadge }">
                        <template v-if="showCategoryBadge">
                            <p class="badge rounded-pill bg-primary text-white mb-2">{{ addon.category }}</p>
                            <h5 class="card-title mb-4">{{ addon.productName }}</h5>
                        </template>
                        <template v-else>
                            <h5 class="card-title">{{ addon.productType ?? 'Misc' }}</h5>
                            <p class="mb-4">{{ addon.productName }}</p>
                        </template>
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="bold">Monthly</p>
                                <p>{{ format.GBP(addon.priceMonthly_spa) }}</p>
                            </div>
                            <div>
                                <p class="bold">Set up</p>
                                <p>{{ format.GBP(addon.priceInstall_spa) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else-if="!isLoadingLocal">
            <div class="col mb-3">No {{ title }} found</div>
        </div>
    </div>
</template>
<script setup>
import { removeAddon, addAddon, addons as sharedAddons, isLoading, checks } from '@/Composables/useConnectivityItem';
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import { quote } from '@/Composables/useQuote';
import queryString from 'qs';

const props = defineProps({
    modelValue: Object,
    currentStep: Number,
    route: {
        type: String,
        required: true
    },
    title: {
        type: String,
        default: 'Options'
    },
    productType: {
        type: String,
        default: 'Option Product'
    },
    pollInterval: {
        type: Number,
        default: 2000
    },
    showCategoryBadge: {
        type: Boolean,
        default: true
    },
    backfillContractTermMonths: {
        type: Boolean,
        default: false
    },
    filterPrimaryCircuitHardware: {
        type: Boolean,
        default: false
    },
    // Also whether this instance's response updates the shared `checks` flags used by the
    // wizard's continue-button gating - only one instance should own this at a time.
    showRequiredIndicator: {
        type: Boolean,
        default: false
    },
    elementId: {
        type: String,
        default: null
    }
});

let timeoutId = null;
// Per-instance list + loading flag - Options and Assignments render side by side, so they
// each need their own state rather than sharing the `addons`/`isLoading` singletons.
const items = ref([]);
const isLoadingLocal = ref(false);
const meta = reactive({
    total: 0,
    overallTotal: 0,
    isRequestCompleted: false
});

const getItems = async () => {
    isLoadingLocal.value = true;
    if (props.showRequiredIndicator) {
        isLoading.value = true;
    }
    items.value = [];

    let filters = [];
    if (props.filterPrimaryCircuitHardware) {
        filters.push({ column: 'isPrimaryCircuitHardware', operator: 'equals', value: props.currentStep === 0 });
    }

    let query = queryString.stringify({
        quote_id: props.modelValue.id,
        filters,
        account_id: props.modelValue.account_id
    });

    try {
        let res = await axios.get(`/api/${props.route}?` + query);
        items.value = res.data.data ?? [];

        // Only the hardware step's response carries this - and only the hardware step's list
        // needs to be visible to QuotesConnectivityView's nav-blocking check via the shared `addons`.
        if (props.showRequiredIndicator) {
            sharedAddons.value = items.value;
            checks.primaryHardwareRequired = res.data.options?.primaryHardwareRequired ?? false;
            checks.failoverHardwareRequired = res.data.options?.failoverHardwareRequired ?? false;
        }

        meta.total = res.data.total;
        meta.overallTotal = res.data.overall_total;
        meta.isRequestCompleted = !!res.data.finished_at;
    } catch (ex) {
        meta.isRequestCompleted = true;
        notyf.error(ex.response?.data?.message ?? 'Something went wrong while loading data.');
    }

    if (!meta.isRequestCompleted) {
        timeoutId = setTimeout(getItems, props.pollInterval);
    } else {
        isLoadingLocal.value = false;
        if (props.showRequiredIndicator) {
            isLoading.value = false;
        }
    }
};

const checkProduct = (article) => {
    if (isLoading.value) return;

    let item = quote.items.find((x) => x.product_code == article.code);
    let index = quote.items.findIndex((x) => x.product_code == article.code);
    article.type = props.productType;
    article.group = props.currentStep + 1;

    if (props.backfillContractTermMonths) {
        const primaryProduct = quote.items.find((x) => x.data.type == 'Primary Product');
        article.contractTermMonths = article.contractTermMonths ?? primaryProduct?.renewal_terms_value;
    }

    if (index == -1) {
        addAddon(article);
    } else {
        removeAddon(item);
    }
};

window.addEventListener('beforeunload', () => {
    clearTimeout(timeoutId); // Cancel timeout on page navigation or refresh
});

onMounted(() => {
    getItems();
});

onUnmounted(() => {
    clearTimeout(timeoutId);
});
</script>
<style scoped>
.blink {
    transition: all ease-in-out;
    animation: blink-animation 1s linear infinite;
}

@keyframes blink-animation {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.3;
    }
}
.border-primary {
    background-color: var(--color-purple-light) !important;
    border-color: var(--color-purple) !important;
    box-shadow:
        inset 0 0 1px #fff,
        inset 0 1px 7px #ebebeb,
        0 3px 6px -3px #bbb;
}
</style>
