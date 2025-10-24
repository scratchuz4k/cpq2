<template>
    <div id="pcx-its-hardware" class="py-5">
        <div>
            <div class="d-flex mb-2">
                <p class="d-flex fs-5 bold me-2">
                    Hardware <span class="fw-normal ms-2">({{ meta.total }})</span>
                    <span v-if="checks.primaryHardwareRequired || checks.failoverHardwareRequired">*</span>
                </p>
                <div v-if="isLoading">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4" v-if="!isLoading && addons.length">
            <div class="col-3 mb-3" v-for="addon in addons">
                <div
                    class="card bg-light cursor-pointer"
                    :class="[quote.items.find((x) => x.product_code == addon.code) ? 'border-primary' : null]"
                    @click="checkProduct(addon)"
                >
                    <div class="card-body">
                        <h5 class="card-title">{{ addon.productType ?? 'Misc' }}</h5>
                        <p class="mb-4">{{ addon.productName }}</p>
                        <div class="d-flex justify-content-between">
                            <div>{{ format.GBP(addon.priceMonthly_spa) }}</div>
                            <div>{{ format.GBP(addon.priceInstall_spa) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else-if="!isLoading">
            <div class="col mb-3">No hardware found</div>
        </div>
    </div>
</template>
<script setup>
import { removeAddon, addAddon, addons, isLoading, checks } from '@/Composables/useConnectivityItem';
import { onMounted, reactive } from 'vue';
import { quote } from '@/Composables/useQuote';
import queryString from 'query-string';

const props = defineProps({
    modelValue: Object,
    currentStep: Number,
    productType: {
        defaul: 'Primary Hardware Product',
        type: String
    }
});

let timeoutId = null;
const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 10,
    isRequestCompleted: false
});

const getAddons = async () => {
    isLoading.value = true;
    let temp = [];
    temp.push({
        column: 'isPrimaryCircuitHardware',
        operator: 'equals',
        value: props.currentStep == 0 ? true : false
    });

    let query = queryString.stringify({
        quote_id: props.modelValue.id,
        filters: JSON.stringify(temp),
        account_id: props.modelValue.account_id
    });
    try {
        let res = await axios.get('/api/cloudmarket/products/addons?' + query);
        addons.value = res.data.data ?? [];
        checks.primaryHardwareRequired = res.data.options?.primaryHardwareRequired ?? false;
        checks.failoverHardwareRequired = res.data.options?.failoverHardwareRequired ?? false;
        meta.total = res.data.total;
        meta.overallTotal = res.data.overall_total;
        meta.isRequestCompleted = !!res.data.finished_at;
    } catch (error) {
        isLoading.value = false;
        meta.isRequestCompleted = true;
    }

    if (!meta.isRequestCompleted) {
        timeoutId = setTimeout(() => {
            getAddons();
        }, 4000);
    } else {
        isLoading.value = false;
    }
};
const checkProduct = (article) => {
    let item = quote.items.find((item) => item.product_code == article.code);
    let index = quote.items.findIndex((item) => item.product_code == article.code);
    article.type = props.productType;
    article.group = props.currentStep + 1;
    return index == -1 ? addAddon(article) : removeAddon(item);
};

window.addEventListener('beforeunload', () => {
    clearTimeout(timeoutId); // Cancel timeout on page navigation or refresh
});
onMounted(() => {
    getAddons();
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
