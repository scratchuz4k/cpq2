<script setup>
import { reactive } from "vue";
import { quote, getQuote } from "@composables/useQuote";
import { createQuoteItem } from "@/Composables/useQuoteItem";

const props = defineProps({
  pricePlans: Array,
  preview: Boolean,
});

const pricePlanskeys = { SINGLE: "one_off", MONTHLY: "recurring" };
let columns = quote.solution_type?.table?.columns ?? [];

if (props.preview) {
  columns = columns.filter((x) => {
    return x.value != "margin";
  });
}

const newLine = reactive({
  show: false,
  name: null,
  discount: 0,
  quantity: 1,
  price: 0,
  contract_length: 1,
  price_plan_code: null,
});

const saveNewLine = async () => {
  if (await createQuoteItem({ quote_id: quote.id, ...newLine })) {
    getQuote(quote.id);
    newLine.show = false;
    Object.assign(newLine, {
      name: null,
      margin: 0,
      quantity: 0,
      price: 0,
      discount: 0,
    });
  }
};

const quoteTotal = (key) => {
  return quote.items
    .filter((x) => x.price_plan && x.price_plan.code == key)
    .reduce((acc, item) => (acc += item.quantity * item.price), 0);
};

const toggleNewLine = (key) => {
  newLine.show = true;
  newLine.contract_length = 1;
  newLine.price_plan_code = key;
};

const filterColumns = (columns, code) => {
  if (code == "SINGLE") {
    return columns.filter((x) => x.value != "contract_length");
  }
  return columns;
};

const editableColumns = (columns, code) => {
  let temp = filterColumns(columns, code);
  let editable = ["name", "price", "discount", "contract_length", "quantity"];
  temp.forEach((e) => {
    e.is_editable = editable.includes(e.value);
  });
  return temp;
};
</script>
<template>
  <div class="mb-5" v-for="pricePlan in pricePlans">
    <div
      class="mobile-price-group-heading mt-3 d-flex justify-content-between align-items-center"
    >
      <h3>{{ pricePlan.name }} charges</h3>
      <h3 :id="pricePlanskeys[pricePlan.code] + '-total'">
        {{ GBP.format(quoteTotal(pricePlan.code)) }}
      </h3>
    </div>
    <table :id="'table-' + pricePlanskeys[pricePlan.code]" class="table-dealmaker table table-bordered">
      <thead>
        <tr>
          <th
            v-for="column in filterColumns(columns, pricePlan.code)"
            :class="[`column-${column.value}-header`]"
          >
            {{ column.name }}
          </th>
          <th style="width: 60px" class="text-center" v-if="!preview"></th>
        </tr>
      </thead>
      <tbody>
        <template
          v-for="(item, index) in quote.items.filter(
            (x) => x.price_plan.code == pricePlan.code
          )"
        >
          <TableItem
            :item="item"
            :index="index"
            :columns="filterColumns(columns, pricePlan.code)"
            :preview="preview"
            :groupColumns="[]"
            :code="pricePlanskeys[pricePlan.code]"
          >
          </TableItem>
        </template>
      </tbody>
      <tfoot v-if="!preview">
        <tr v-if="newLine.show && newLine.price_plan_code == pricePlan.code">
          <th v-for="column in editableColumns(columns, pricePlan.code)">
            <input
              v-if="column.is_editable"
              :id="'input-new-' + pricePlanskeys[pricePlan.code] + '-' + column.value"
              class="form-control p-0 px-1"
              :class="[column.value != 'name' ? 'text-center' : '']"
              type="text"
              v-model="newLine[column.value]"
            />
          </th>
          <th class="text-right">
            <div class="d-flex justify-content-center">
              <font-awesome-icon
                :id="'btn-new-' + pricePlanskeys[pricePlan.code] + '-cancel'"
                role="button"
                class="px-2 py-2 text-secondary"
                :icon="['fas', 'cancel']"
                @click="() => (newLine.show = false)"
              />
              <font-awesome-icon
                :id="'btn-new-' + pricePlanskeys[pricePlan.code] + '-add'"
                :icon="['fas', 'save']"
                role="button"
                class="px-2 py-2 text-primary"
                @click="saveNewLine(newLine)"
              />
            </div>
          </th>
        </tr>
        <tr v-else>
          <th :colspan="columns.length + 1">
            <div
              role="button"
              :id="'btn-' + pricePlanskeys[pricePlan.code] + '-add'"
              class="d-flex justify-content-center align-items-center px-2 py-1"
              style="font-size: 12px"
              @click="toggleNewLine(pricePlan.code)"
            >
              <span>Add New</span>
              <font-awesome-icon class="ms-2" :icon="['fas', 'plus']" />
            </div>
          </th>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<style scoped></style>
