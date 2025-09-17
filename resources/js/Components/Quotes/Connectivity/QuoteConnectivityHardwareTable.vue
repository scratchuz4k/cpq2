<template>
    <div id="pcx-its-addons" class="py-5">
        <div>
            <div class="d-flex mb-2">
                <p class="d-flex fs-5 bold me-2">
                    Hardware <span class="fw-normal ms-2">({{ meta.total }})</span>
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
                    class="card cursor-pointer"
                    :class="[quote.items.find((x) => x.product_code == addon.code) ? 'border-primary border-2' : null]"
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
        <div v-else>
            <div class="col mb-3">No hardware found</div>
        </div>
    </div>
</template>
<script setup>
import { removeAddon, addAddon, addons, isLoading } from '@/Composables/useConnectivityItem';
import { watch, reactive } from 'vue';
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
        meta.total = res.data.total;
        meta.overallTotal = res.data.overall_total;
        meta.isRequestCompleted = !!res.data.finished_at;
    } catch (error) {
        isLoading.value = false;
        meta.isRequestCompleted = false;
    }

    if (!meta.isRequestCompleted) {
        timeoutId = setTimeout(() => {
            getAddons();
        }, 2000);
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

watch(
    () => [quote.items],
    () => {
        getAddons();
    }
);
window.addEventListener('beforeunload', () => {
    clearTimeout(timeoutId); // Cancel timeout on page navigation or refresh
});

getAddons();
</script>
<style>
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
</style>
