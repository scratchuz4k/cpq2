<script setup>
import { quote } from '@/Composables/useQuote';
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
});
const item = defineModel();

const textElement = ref(null);
const isOverflowing = ref(false);

const mainProduct = computed(() => {
    if (!item.value.data || item.value.data?.type === 'main_product') {
        return null;
    }
    return quote.items.find((x) => x.group === item.value.group && x.data?.type === 'main_product') || null;
});

const checkOverflow = () => {
    if (textElement.value) {
        isOverflowing.value = textElement.value.scrollWidth > textElement.value.clientWidth;
    }
};

const itemNameTooltip = computed(() => {
    return isOverflowing.value ? item.value[props.column.value] : null;
});

watch(
    () => item.value[props.column.value],
    () => {
        checkOverflow();
    },
    { immediate: true }
);

onMounted(() => {
    checkOverflow();
    window.addEventListener('resize', checkOverflow);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkOverflow);
});
</script>
<template>
    <div :class="!preview ? 'd-flex align-baseline' : ''">
        <p
            ref="textElement"
            class="small semi-bold w-100"
            :class="!preview ? 'truncate-name-line' : '*:'"
            v-tooltip
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-html="true"
            :data-bs-original-title="itemNameTooltip"
        >
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

        <span v-if="item.data && item.data?.bundle_items?.length > 0 && !preview" class="badge badge-bundle">
            Bundle
        </span>
    </div>
    <p v-if="mainProduct" class="main-product-reference">{{ mainProduct.name }}</p>
</template>

<style>
.router-model,
.is-contended,
.is-burstable,
.addon-ipv4 {
    font-size: 0.75rem !important;
}

.truncate-name-line {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 20px; /* or a fixed px value, e.g. 24px */
    height: calc(20px * 1); /* 3 lines × line-height */
}
</style>
