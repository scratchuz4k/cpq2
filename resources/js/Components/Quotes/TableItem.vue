<script setup>
import { ref } from "vue";
import { updateQuoteItem, removeQuoteItem, temp } from "@composables/useQuoteItem";
import { getQuote, quote } from "@/Composables/useQuote";

const props = defineProps({
  code: String,
  index: Number,
  showToggle: Boolean,
  isChild: Boolean,
  preview: Boolean,
  columns: Array,
  groupColumns: Array,
  item: Object,
});
const item = defineModel("item");

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
    getQuote(quote.id);
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
    getQuote(quote.id);
  }
};

const cancel = (p) => {
  p.is_editing = !p.is_editing;
};

emitter.on("quote-items:toggle:is-editing", () => item.value.is_editing);
</script>
<template>
  <tr
    :class="['item-row', props.code + '-item', isChild ? 'child-bg' : '']"
    :style="{ 'vertical-align': 'middle' }"
  >
    <td class="text-center" v-if="showToggle && !preview"></td>
    <template v-for="column in columns">
      <td
        v-if="!groupColumns.find((x) => x.value == column.value)"
        :class="[`column-${column.value}-body`]"
      >
        <component
          :is="format.toCamelCase(column.value) + 'Column'"
          v-bind="{
            column: column,
            modelValue: item,
            isGroup: false,
            preview: preview,
            index: index,
            code: props.code,
          }"
        >
        </component>
      </td>
    </template>
    <td v-if="!preview" class="text-center p-0">
      <div class="d-flex justify-content-end">
        <template v-if="!item.is_editing">
          <font-awesome-icon
            :id="'btn-' + props.code + '-' + index + '-edit'"
            role="button"
            class="px-2 py-2 text-primary"
            :icon="['fas', 'edit']"
            @click="edit(item)"
          />
          <font-awesome-icon
            :id="'btn-' + props.code + '-' + index + '-remove'"
            role="button"
            class="px-2 py-2 text-secondary"
            :icon="['fas', 'trash']"
            @click="remove(item)"
          />
        </template>
        <template v-else>
          <font-awesome-icon
            :id="'btn-' + props.code + '-' + index + '-cancel'"
            role="button"
            class="px-2 py-2 text-secondary"
            :icon="['fas', 'cancel']"
            @click="cancel(item)"
          />
          <font-awesome-icon
            :id="'btn-' + props.code + '-' + index + '-save'"
            role="button"
            class="px-2 py-2 text-primary"
            :icon="['fas', 'save']"
            @click="save(item)"
          />
        </template>
      </div>
    </td>
  </tr>
</template>
<style>
.child-bg > td {
  background-color: whitesmoke;
}
</style>
