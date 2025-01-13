<script setup>
defineProps({
  code: String,
  column: Object,
  isGroup: Boolean,
  preview: Boolean,
  index: Number,
});
const item = defineModel();
</script>
<template>
  <p class="small">
    {{ item[column.value] }}
    <span
      v-if="item.data && item.data?.additionalInfo['Primary Router Model']"
      v-tooltip
      data-bs-toggle="tooltip"
      data-bs-placement="top"
      title="Primary Router Model"
      class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
    >
      <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
      {{ item.data.additionalInfo["Primary Router Model"] }}
    </span>
    <span
      v-if="item.data?.isContended"
      class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
      data-bs-toggle="tooltip"
      data-bs-placement="top"
      title="Contended"
      v-tooltip
    >
      <font-awesome-icon :icon="['fas', 'network-wired']" />
    </span>
  </p>
</template>
