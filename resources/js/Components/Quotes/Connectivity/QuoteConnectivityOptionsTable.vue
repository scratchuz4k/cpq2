<template>
    <div class="py-5">
        <div>
            <div class="d-flex mb-2">
                <p class="fs-5 bold me-2">
                    Options
                    <span class="fw-normal">({{ meta.total }})</span>
                </p>
                <div v-if="isLoading">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4" v-if="options.length">
            <div class="col-3 mb-3" v-for="addon in options">
                <div
                    class="card bg-light cursor-pointer h-100"
                    :class="[quote.items.find((x) => x.product_code == addon.code) ? 'border-primary' : null]"
                    @click="checkProduct(addon)"
                >
                    <div class="card-body d-flex" style="flex-direction: column; justify-content: space-between">
                        <div class="">
                            <p class="badge rounded-pill bg-primary text-white mb-2">{{ addon.category }}</p>
                            <h5 class="card-title mb-4">{{ addon.productName }}</h5>
                        </div>
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
        <div v-else-if="!isLoading">
            <div class="col mb-3">No Options found</div>
        </div>
    </div>
</template>
<script setup>
import { removeAddon, addAddon } from '@/Composables/useConnectivityItem';
import { watch, reactive, ref } from 'vue';
import { quote } from '@/Composables/useQuote';
import queryString from 'query-string';

const props = defineProps({
    modelValue: Object,
    currentStep: Number
});

const isLoading = ref(false);
const options = ref([]);
let timeoutId = null;
const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 10,
    isRequestCompleted: false
});

const getOptions = async () => {
    isLoading.value = true;
    let temp = [];
    let query = queryString.stringify({
        quote_id: props.modelValue.id,
        filters: JSON.stringify(temp),
        account_id: props.modelValue.account_id
    });
    try {
        let res = await axios.get('/api/cloudmarket/products/options?' + query);

        options.value = res.data.data ?? [];
        meta.total = res.data.total;
        meta.overallTotal = res.data.overall_total;
        meta.isRequestCompleted = !!res.data.finished_at;
    } catch (error) {
        meta.isRequestCompleted = false;
    }

    isLoading.value = false;
};
const checkProduct = (article) => {
    let item = quote.items.find((item) => item.product_code == article.code);
    let index = quote.items.findIndex((item) => item.product_code == article.code);
    article.type = 'Option Product';
    article.group = props.currentStep + 1;
    return index == -1 ? addAddon(article) : removeAddon(item);
};

watch(
    () => [quote.items],
    () => {
        getOptions();
    }
);
window.addEventListener('beforeunload', () => {
    clearTimeout(timeoutId); // Cancel timeout on page navigation or refresh
});

getOptions();
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
