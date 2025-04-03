<script setup>
import { temp } from "@composables/useQuoteItem";
import { quote } from "@composables/useQuote";
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
    <p class="small" v-if="!item.is_editing || quote.solution_type.code == 3">
      {{ item.price_plan?.code == "MONTHLY" && quote ? item[column.value] : "" }}
    </p>
    <input
      v-else-if="item.price_plan.code == 'MONTHLY'"
      :id="'input-' + code + '-' + index + '-contract_length'"
      class="form-control text-center p-0 px-1"
      type="number"
      v-model="temp.contract_length"
    />
  </template>
</template>
