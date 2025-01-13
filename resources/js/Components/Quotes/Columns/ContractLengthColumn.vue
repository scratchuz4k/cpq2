<script setup>
import { temp } from "@composables/useQuoteItem";
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
    <p class="small" v-if="!item.is_editing">{{ item[column.value] }}</p>
    <input
      v-else
      :id="'input-' + code + '-' + index + '-contract_length'"
      class="form-control text-center p-0 px-1"
      type="number"
      v-model="temp.contract_length"
    />
  </template>
</template>
