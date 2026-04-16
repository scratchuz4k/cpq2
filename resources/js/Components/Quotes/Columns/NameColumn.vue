<script setup>
import { quote } from '@/Composables/useQuote';
import { computed } from 'vue';

defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
});
const item = defineModel();

const mainProduct = computed(() => {
    if (!item.value.data || item.value.data?.type === 'main_product') {
        return null;
    }
    return quote.items.find((x) => x.group === item.value.group && x.data?.type === 'main_product') || null;
});
</script>
<template>
    <p class="small semibold">
        {{ item[column.value] }}
        <span
            v-if="item.data && item.data?.additionalInfo?.router"
            v-tooltip
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-html="true"
            :data-bs-original-title="'<b>Primary Router Model:</b> ' + item.data.additionalInfo['router']"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
        >
            <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
        </span>
        <!-- <span
            v-if="item.data?.isContended"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-html="true"
            data-bs-original-title="<b>Contended Service:</b> Contended, wires only full fibre service with symmetrical upload and download speeds"
            v-tooltip
        >
            <font-awesome-icon :icon="['fas', 'server']"></font-awesome-icon>
        </span> -->
        <span
            v-if="item.data?.additionalInfo?.['Type'] == 'Fibre Flex 200+'"
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
            v-if="item.data?.additionalInfo?.['addon_ipv4']"
            v-tooltip
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-html="true"
            :data-bs-original-title="item.data?.additionalInfo?.['addon_ipv4']"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm addon-ipv4"
        >
            <font-awesome-icon :icon="['fas', 'network-wired']"></font-awesome-icon>
        </span>
    </p>
    <p v-if="mainProduct" class="has-main-product">{{ mainProduct.name }}</p>
</template>

<style>
.router-model,
.is-contended,
.is-burstable,
.addon-ipv4 {
    font-size: 0.75rem !important;
}

.has-main-product {
    font-family: Font family/Body;
    font-weight: 500;
    line-height: 14px;
    font-style: Regular;
    font-size: 12px;
    letter-spacing: 0%;
    vertical-align: middle;
    color: #4e4e4e;
}
</style>
