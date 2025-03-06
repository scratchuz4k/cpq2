<script setup>
import { ref } from "vue";

const props = defineProps({
  preview: Boolean,
  columns: Array,
  item: [String, Number],
});
const showChildren = ref(true);

const colspan = props.columns.length;
</script>
<template>
  <tr
    class="item-row"
    :class="['custom-item-group']"
    :style="{ 'vertical-align': 'middle' }"
  >
    <td class="text-center" v-if="!preview">
      <button class="btn p-0 border-0" @click="() => (showChildren = !showChildren)">
        <font-awesome-icon
          v-if="!showChildren"
          :icon="['fas', 'plus']"
        ></font-awesome-icon>
        <font-awesome-icon v-else :icon="['fas', 'minus']"></font-awesome-icon>
      </button>
    </td>
    <td :colspan="colspan">
      <div class="d-flex">
        <div class="me-3 py-2">{{ item }}</div>
      </div>
    </td>
    <td class="text-center" v-if="!preview"></td>
  </tr>
  <slot v-if="showChildren"></slot>
</template>
