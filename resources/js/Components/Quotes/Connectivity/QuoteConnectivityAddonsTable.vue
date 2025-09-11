<template>
    <div id="pcx-its-addons" class="py-5">
        <div>
            <div class="d-flex mb-2">
                <p class="fs-5 bold me-2">
                    Hardware
                    <span class="fw-normal">({{ meta.total }})</span>
                </p>
            </div>
        </div>
        <div class="row mb-4" v-if="addons.length">
            <div class="col-3 mb-3" v-for="addon in addons">
                <div
                    class="card cursor-pointer"
                    :class="[quote.items.find((x) => x.product_code == addon.code) ? 'border-primary border-2' : null]"
                    @click="checkProduct(addon)"
                >
                    <div class="card-body">
                        <h5 class="card-title">{{ addon.productType ?? 'Misc' }}</h5>
                        <p>{{ addon.productName }}</p>
                        <p class="card-text small mb-4">
                            {{ addon.productReference }}
                        </p>
                        <div class="d-flex justify-content-between">
                            <div>{{ format.GBP(addon.priceMonthly_spa) }}</div>
                            <div>{{ format.GBP(addon.priceInstall_spa) }}</div>
                        </div>
                    </div>
                </div>
            </div>
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
    currentStep: Number
});

const meta = reactive({
    page: 1,
    total: 0,
    overallTotal: 0,
    first: 1,
    limit: 10
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
    } catch (error) {}
    isLoading.value = false;
};
const checkProduct = (article) => {
    let item = quote.items.find((item) => item.product_code == article.code);
    let index = quote.items.findIndex((item) => item.product_code == article.code);
    article.type = 'Addon Product';
    article.group = props.currentStep + 1;
    return index == -1 ? addAddon(article) : removeAddon(item);
};

watch(
    () => [quote.items],
    () => {
        getAddons();
    }
);

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
