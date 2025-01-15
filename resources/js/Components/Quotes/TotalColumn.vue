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
  <template v-if="!isGroup">
    <p
      v-if="item.quantity * item.price == item[column.value]"
      :id="'total-' + index"
      class="small column_total"
    >
      {{ GBP.format(item.quantity * item.price) }}
    </p>

    <p
      v-else
      v-if="item.quantity * item.price != item[column.value]"
      :id="'total-' + index"
      v-tooltip
      data-bs-toggle="tooltip"
      data-bs-placement="bottom"
      :title="'Overall Cost: ' + GBP.format(item[column.value])"
      class="small column_total"
    >
      {{ GBP.format(item.quantity * item.price) }}
    </p>
  </template>
</template>
