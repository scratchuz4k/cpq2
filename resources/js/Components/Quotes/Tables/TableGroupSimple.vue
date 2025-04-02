<script setup>
import { ref } from "vue";
import format from "@/Composables/format";

const props = defineProps({
  preview: Boolean,
  columns: Array,
  name: [String, Number],
  item: Object,
  group: String,
  groupName: String,
});
const showChildren = ref(true);

const colspan = props.columns.length;

const getFormat = (name, group) => {
  if (["discount", "margin", "total", "price"].includes(group)) {
    return GBP.format(name);
  }
  if (["speed", "bearer"].includes(group)) {
    return format.speed(name);
  }
  return name;
};
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
        <p class="m-0 me-3 py-2">
          <span v-if="groupName">{{ groupName }} - </span> {{ getFormat(name, group) }}
        </p>
      </div>
    </td>
    <td class="text-center" v-if="!preview"></td>
  </tr>
  <slot v-if="showChildren"></slot>
</template>
