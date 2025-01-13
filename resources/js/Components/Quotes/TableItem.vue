<script setup>
import { ref } from "vue";
import { updateQuoteItem, removeQuoteItem, temp } from "@composables/useQuoteItem";

const props = defineProps({
  code: String,
  index: Number,
  isParent: Boolean,
  showToggle: Boolean,
  preview: Boolean,
  columns: Array,
  parentColumns: Array,
  item: Object,
});
const item = ref(props.item);
const showChildren = ref(true);

const edit = (p) => {
  temp.quantity = p.quantity;
  temp.discount = p.discount;
  temp.contract_length = p.contract_length;
  emitter.emit("quote-items:toggle:is-editing");
  item.value.is_editing = true;
};

const save = async (p) => {
  item.value.quantity = temp.quantity;
  item.value.discount = temp.discount;
  item.value.contract_length = temp.contract_length;
  if (await updateQuoteItem(item.value.id, item.value)) {
    item.value.is_editing = false;
    emitter.emit("quote:refresh");
  }
};

const remove = async (p) => {
  let id = p.id;
  let result = null;
  if (p.parent_id) {
    result = await swal.fire({
      title: "Are you sure?",
      text:
        "This line contains other associated lines, removing it deletes all associated lines",
    });
    id = p.parent_id;
  } else {
    result = await swal.fire({
      title: "Are you sure?",
    });
  }

  if (["cancel", "backdrop"].includes(result.dismiss)) {
    return;
  }

  if (await removeQuoteItem(id)) {
    debugger
    emitter.emit("quote:refresh");
  }
};

const cancel = (p) => {
  p.is_editing = !p.is_editing;
};

emitter.on("quote-items:toggle:is-editing", () => item.value.is_editing);

const colspan = props.columns.length - props.parentColumns.length;
</script>
<template>
  <tr
    v-if="isParent"
    class="item-row"
    :class="[isParent ? 'child-bg' : '', props.code + '-item-parent']"
    :style="{ 'vertical-align': 'middle' }"
  >
    <td class="text-center" v-if="showToggle && !preview">
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
        <template v-for="(column, index) in parentColumns">
          <div class="me-3">
            <component
              :is="format.toCamelCase(column.value) + 'Column'"
              v-bind="{
                column: column,
                modelValue: item,
                preview: preview,
                index: index,
                code: props.code,
                isParent: isParent,
              }"
            >
            </component>
          </div>
        </template>
      </div>
    </td>
    <td :class="['text-center p-0']">
      <template v-if="preview">
        {{ index + 1 }}
      </template>
    </td>
  </tr>
  <tr
    v-else
    :class="['item-row', props.code + '-item']"
    :style="{ 'vertical-align': 'middle' }"
  >
    <td class="text-center" v-if="showToggle && !preview"></td>
    <template v-for="column in columns">
      <td
        v-if="!parentColumns.find((x) => x.value == column.value)"
        :class="[`column-${column.value}-body`]"
      >
        <component
          :is="format.toCamelCase(column.value) + 'Column'"
          v-bind="{
            column: column,
            modelValue: item,
            isParent: isParent,
            preview: preview,
            index: index,
            code: props.code,
          }"
        >
        </component>
      </td>
    </template>
    <td class="text-center p-0">
      <template v-if="!preview">
        <div class="d-flex justify-content-end">
          <template v-if="!item.is_editing && item.id">
            <font-awesome-icon
              :id="'btn-' + props.code + '-' + index + '-edit'"
              role="button"
              class="px-2 py-3 text-primary"
              :icon="['fas', 'edit']"
              @click="edit(item)"
            />
            <font-awesome-icon
              :id="'btn-' + props.code + '-' + index + '-remove'"
              role="button"
              class="px-2 py-3 text-secondary"
              :icon="['fas', 'trash']"
              @click="remove(item)"
            />
          </template>
          <template v-else>
            <font-awesome-icon
              :id="'btn-' + props.code + '-' + index + '-cancel'"
              role="button"
              class="px-2 py-3 text-secondary"
              :icon="['fas', 'cancel']"
              @click="cancel(item)"
            />
            <font-awesome-icon
              :id="'btn-' + props.code + '-' + index + '-save'"
              role="button"
              class="px-2 py-3 text-primary"
              :icon="['fas', 'save']"
              @click="save(item)"
            />
          </template>
        </div>
      </template>
      <template v-else-if="!preview">
        {{ index + 1 }}
      </template>
    </td>
  </tr>

  <slot v-if="showChildren"></slot>
</template>
<style scoped>
.child-bg > td {
  background-color: whitesmoke;
}
</style>
