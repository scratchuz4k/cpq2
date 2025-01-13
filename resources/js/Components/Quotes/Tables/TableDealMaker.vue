<script setup>
import { reactive } from "vue";
defineProps({
  pricePlans: Array,
  preview: Boolean,
});
const quote = defineModel();

const pricePlanskeys = { SINGLE: "one_off", MONTHLY: "recurring" };
const columns = quote.value.solution_type?.table?.columns ?? [];

const newLine = reactive({
  name: null,
  discount: 0,
  quantity: 0,
  price: 0,
});

const getLeafNodesWithChildren = (objects) => {
  let leaves = [];
  const findLeaves = (items) => {
    for (const item of items) {
      if (item.children && item.children.length > 0) {
        findLeaves(item.children);
      } else {
        leaves.push(item);
      }
    }
  };

  findLeaves(objects);
  return leaves;
};

const saveNewLine = async (item) => {
  try {
    let res = await axios.put(`/api/quotes/${quote.value.id}`, {
      items: [...quote.value.items, item],
    });
    Object.assign(quote, res.data);
    Object.assign(quote.value.items, getLeafNodesWithChildren(quote.value.items));
    item.show = false;
    Object.assign(item, {
      name: null,
      margin: 0,
      quantity: 0,
      price: 0,
    });
  } catch (ex) {
    notyf.error(ex.response.data.message);
  }
};

const quoteTotal = (key) => {
  return quote.value.items
    .filter((x) => x.price_plan && x.price_plan.code == key)
    .reduce((acc, item) => (acc += item.total), 0);
};
</script>
<template>
  <div class="mb-5" v-for="pricePlan in pricePlans">
    <div
      class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center"
    >
      <h3>{{ pricePlan.name }} charges</h3>
      <h3 id="recurring_total">{{ GBP.format(quoteTotal(pricePlan.code)) }}</h3>
    </div>
    <table :id="'table-' + pricePlan.code" class="table">
      <thead>
        <tr>
          <th v-for="column in columns" :class="[`column-${column.value}-header`]">
            {{ column.name }}
          </th>
          <th style="width: 60px" class="text-center"></th>
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
            :columns="columns"
            :preview="preview"
            :parentColumns="[]"
            :code="pricePlanskeys[pricePlan.code]"
          >
          </TableItem>
        </template>
      </tbody>
      <tfoot v-if="!preview">
        <tr v-if="newLine.show && newLine.price_plan_code == pricePlan.code">
          <th>
            <input
              :id="'input-new-' + pricePlanskeys[pricePlan.code] + '-name'"
              class="form-control p-0 px-1"
              type="text"
              v-model="newLine.name"
            />
          </th>
          <th class="text-center">
            <input
              v-if="newLine.price_plan_code == 'MONTHLY'"
              :id="'input-new-' + pricePlanskeys[pricePlan.code] + '-contract_length'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="newLine.contract_length"
            />
          </th>
          <th class="text-center">
            <input
              :id="'input-new-' + pricePlanskeys[pricePlan.code] + '-qty'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="newLine.quantity"
            />
          </th>
          <th>
            <input
              :id="'input-new-' + pricePlanskeys[pricePlan.code] + '-price'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="newLine.price"
            />
          </th>
          <th></th>
          <th class="text-center">
            <input
              :id="'input-new-' + pricePlanskeys[pricePlan.code] + '-discount'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="newLine.discount"
            />
          </th>
          <th class="text-right"></th>
          <th class="text-right">
            <div class="d-flex justify-content-center">
              <font-awesome-icon
                :id="'btn-new-' + pricePlanskeys[pricePlan.code] + '-cancel'"
                role="button"
                class="px-2 py-3 text-secondary"
                :icon="['fas', 'cancel']"
                @click="() => (newLine.show = false)"
              />
              <font-awesome-icon
                :id="'btn-new-' + pricePlanskeys[pricePlan.code] + '-add'"
                :icon="['fas', 'save']"
                role="button"
                class="px-2 py-3 text-primary"
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

<style scoped>
.table {
  font-size: 14px !important;
  vertical-align: middle;
}

tr td,
tr th {
  padding: 0px 10px !important;
}

tr td:last-child,
tr th:last-child {
  padding: 0px !important;
}

thead {
  vertical-align: middle !important;
}

thead tr th {
  padding: 10px !important;
  background: #e8e8e8;
  font-weight: normal;
  font-size: 12px !important;
}

/* For Chrome, Safari, Edge, Opera */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* For Firefox */
input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}

[role="button"]:hover {
  opacity: 0.75;
}

.reject-reasons-table tr th,
.reject-reasons-table tr td {
  padding: 1rem !important;
}

.form-control {
  font-size: 0.9rem;
  line-height: 1.5rem;
}

.commission-calculator .input-group input {
  border-right: none;
  background-color: #dfe3e6;
  border-color: #bdbdbd;
}

.commission-calculator .input-group .input-group-text {
  background-color: #dfe3e6;
  color: #646464;
  border-color: #bdbdbd;
}

.custom-pre {
  font-family: inherit;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.p-dialog-header {
  justify-content: right !important;
}
</style>
