<script setup>
const props = defineProps({
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
      v-if="item.quantity * item.price == item[column.value] || preview"
      :id="'total-' + index"
      class="small column_total"
    >
      {{ GBP.format(item.quantity * item.price) }}
    </p>

    <div
      v-else
      v-if="item.quantity * item.price != item[column.value]"
      :id="'total-' + index"
      class="column_total cpq-tooltip"
    >
      {{ GBP.format(item.quantity * item.price) }}
      <span class="cpq-tooltip-text">
        Overall Cost:<br />{{ GBP.format(item[props.column.value]) }}
      </span>
    </div>
  </template>
</template>
