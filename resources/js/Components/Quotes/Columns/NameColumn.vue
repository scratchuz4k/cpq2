<script setup>
defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
});
const item = defineModel();
</script>
<template>
    <p class="small">
        {{ item[column.value] }}
        <span
            v-if="item.data && item.data?.additionalInfo['router']"
            v-tooltip
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            :title="'Primary Router Model: ' + item.data.additionalInfo['router']"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
        >
            <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
        </span>
        <span
            v-if="item.data?.isContended"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Contended Service: Contended, wires only full fibre service with symmetrical upload and download speeds"
            v-tooltip
        >
            <font-awesome-icon :icon="['fas', 'server']"></font-awesome-icon>
        </span>
        <span
            v-if="item.data?.additionalInfo?.['Type'] == 'Fibre Flex 200+'"
            v-tooltip
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Burstable Service: This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
        >
            <font-awesome-icon :icon="['fas', 'bolt']"></font-awesome-icon>
        </span>

        <span
            v-if="item.data?.additionalInfo?.['addon_ipv4']"
            v-tooltip
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            :title="item.data?.additionalInfo?.['addon_ipv4']"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
        >
            <font-awesome-icon :icon="['fas', 'network-wired']"></font-awesome-icon>
        </span>
    </p>
</template>

<style>
.is-contended,
.router-model {
    font-size: 0.625rem !important;
}
</style>
