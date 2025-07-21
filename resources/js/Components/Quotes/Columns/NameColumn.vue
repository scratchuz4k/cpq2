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
            title="Primary Router Model"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
        >
            <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
            {{ item.data.additionalInfo['router'] }}
        </span>
        <span
            v-if="item.data?.isContended"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Contended, wires only full fibre service with symmetrical upload and download speeds"
            v-tooltip
        >
            Contended Service
        </span>
        <span
            v-if="item.data?.additionalInfo?.['Type'] == 'Fibre Flex 200+'"
            v-tooltip
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
        >
            Burstable Service
        </span>
    </p>
</template>

<style>
.is-contended,
.router-model {
    font-size: 0.625rem !important;
}
</style>
