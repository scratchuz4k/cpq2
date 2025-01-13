<script setup>
import { reactive } from "vue";
defineProps({
  pricePlans: Array,
  preview: Boolean,
});
const quote = defineModel();

const pricePlanskeys = { "One-Off": "SINGLE", Recurring: "MONTHLY" };
const columns = quote.value.solution_type?.table?.columns ?? [];

const temp = reactive({
  contract_length: 1,
  discount: 0,
  quantity: 0,
});

const recurringNewLine = reactive({
  name: null,
  contract_length: 1,
  discount: 0,
  quantity: 0,
  price: 0,
  price_plan_code: "MONTHLY",
});

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

const edit = (p) => {
  temp.quantity = p.quantity;
  temp.discount = p.discount;
  temp.contract_length = p.contract_length;
  quote.value.items.forEach((e) => (e.is_editing = false));
  p.is_editing = !p.is_editing;
};

const save = async (p) => {
  p.quantity = temp.quantity;
  p.discount = temp.discount;
  p.contract_length = temp.contract_length;
  try {
    let res = await axios.put(`/api/quotes/${quote.value.id}`, {
      items: quote.value.items,
    });
    Object.assign(quote, res.data);
    Object.assign(quote.value.items, getLeafNodesWithChildren(quote.value.items));
    p.is_editing = !p.is_editing;
  } catch (ex) {
    notyf.error(ex.response.data.message);
  }
};

const cancel = (p) => {
  p.is_editing = !p.is_editing;
};

const remove = async (p) => {
  let id = p.id;
  let result = null;
  if (p.parent_id) {
    result = await swal.fire({
      title: "Are you sure?",
      text:
        "This line contains other associated lines, removing it dees all associated lines",
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
  try {
    await axios.delete(`/api/quote-items/${id}`);
    router.get(`/quotes/${quote.value.id}`);
  } catch (ex) {
    notyf.error(ex.response.data.message);
  }
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
            :code="pricePlan.code"
          >
          </TableItem>
        </template>
      </tbody>
      <tfoot v-if="!preview">
        <tr v-if="newLine.show && newLine.price_plan_code == pricePlan.code">
          <th>
            <input
              :id="'input-new-' + pricePlan.code + '-name'"
              class="form-control p-0 px-1"
              type="text"
              v-model="newLine.name"
            />
          </th>
          <th class="text-center">
            <input
              v-if="newLine.price_plan_code == 'MONTHLY'"
              :id="'input-new-' + pricePlan.code + '-contract_length'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="newLine.contract_length"
            />
          </th>
          <th class="text-center">
            <input
              :id="'input-new-' + pricePlan.code + '-qty'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="newLine.quantity"
            />
          </th>
          <th>
            <input
              :id="'input-new-' + pricePlan.code + '-price'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="newLine.price"
            />
          </th>
          <th></th>
          <th class="text-center">
            <input
              :id="'input-new-' + pricePlan.code + '-discount'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="newLine.discount"
            />
          </th>
          <th class="text-right"></th>
          <th class="text-right">
            <div class="d-flex justify-content-center">
              <font-awesome-icon
                :id="'btn-new-' + pricePlan.code + '-cancel'"
                role="button"
                class="px-2 py-3 text-secondary"
                :icon="['fas', 'cancel']"
                @click="() => (newLine.show = false)"
              />
              <font-awesome-icon
                :id="'btn-new-' + pricePlan.code + '-add'"
                :icon="['fas', 'save']"
                role="button"
                class="px-2 py-3 text-primary"
                @click="saveNewLine(newLine)"
              />
            </div>
          </th>
        </tr>
        <tr v-else>
          <th colspan="10">
            <div
              role="button"
              :id="'btn-' + pricePlan.code + '-add'"
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
  <!-- <div class="mb-5">
    <div
      class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center"
    >
      <h3>One off charges</h3>
      <h3 id="one_off_total">{{ GBP.format(quoteTotal("SINGLE")) }}</h3>
    </div>
    <table id="table-one_off" class="table">
      <thead>
        <tr>
          <th style="width: 38%">Product</th>
          <th class="text-center" style="width: 8%">Qty</th>
          <th class="text-right" style="width: 11%">Your Price</th>
          <th class="text-right" style="width: 10%">Margin</th>
          <th class="text-center" style="width: 10%">Discount</th>
          <th class="text-right" style="width: 13%">Sell Price</th>
          <th class="text-right" style="width: 10%"></th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(p, i) in quote.items.filter(
            (x) => !x.price_plan || x.price_plan?.code == 'SINGLE'
          )"
          class="one_off-item bg-light-grey"
        >
          <td>{{ p.name }}</td>
          <td class="text-center">
            <span v-if="!p.is_editing" :id="'one_off-' + i + '-quantity'">
              x{{ p.quantity }}
            </span>
            <input
              :id="'input-one_off-' + i + '-quantity'"
              v-else
              class="form-control text-center p-0 px-1"
              type="number"
              min="0"
              max="100"
              v-model="temp.quantity"
            />
          </td>
          <td class="text-right">{{ GBP.format(p.original_price) }}</td>
          <td class="text-right">
            <span :id="'one_off-' + i + '-margin'">
              {{ GBP.format(p.margin) }}
            </span>
          </td>
          <td class="text-center">
            <span v-if="!p.is_editing" :id="'one_off-' + i + '-discount'">
              {{ p.discount }} %
            </span>
            <input
              v-else
              :id="'input-one_off-' + i + '-discount'"
              class="form-control text-center p-0 px-1"
              type="number"
              min="0"
              max="100"
              v-model="temp.discount"
            />
          </td>
          <td class="text-right fw-bold">
            <span :id="'input-one_off-' + i + '-price'">
              {{ GBP.format(p.price) }}
            </span>
          </td>
          <td class="text-right">
            <div class="d-flex justify-content-center">
              <template v-if="!p.is_editing">
                <font-awesome-icon
                  :id="'btn-one_off-' + i + '-edit'"
                  role="button"
                  class="px-2 py-3 text-primary"
                  :icon="['fas', 'edit']"
                  @click="edit(p)"
                />
                <font-awesome-icon
                  :id="'btn-one_off-' + i + '-remove'"
                  role="button"
                  class="px-2 py-3 text-secondary"
                  :icon="['fas', 'trash']"
                  @click="remove(p)"
                />
              </template>
              <template v-else>
                <font-awesome-icon
                  :id="'btn-one_off-' + i + '-cancel'"
                  role="button"
                  class="px-2 py-3 text-secondary"
                  :icon="['fas', 'cancel']"
                  @click="cancel(p)"
                />
                <font-awesome-icon
                  :id="'btn-one_off-' + i + '-save'"
                  role="button"
                  class="px-2 py-3 text-primary"
                  :icon="['fas', 'save']"
                  @click="save(p)"
                />
              </template>
            </div>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr v-if="oneOffNewLine.show">
          <th>
            <input
              :id="'input-new-one_off-name'"
              class="form-control p-0 px-1"
              type="text"
              v-model="oneOffNewLine.name"
            />
          </th>
          <th class="text-center">
            <input
              :id="'input-new-one_off-qty'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="oneOffNewLine.quantity"
            />
          </th>
          <th>
            <input
              :id="'input-new-one_off-price'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="oneOffNewLine.price"
            />
          </th>
          <th class="text-center"></th>
          <th class="text-center">
            <input
              :id="'input-new-one_off-discount'"
              class="form-control text-center p-0 px-1"
              type="number"
              v-model="oneOffNewLine.discount"
            />
          </th>
          <th class="text-right"></th>
          <th class="text-right">
            <div class="d-flex justify-content-center">
              <font-awesome-icon
                :id="'btn-new-one_off-cancel'"
                role="button"
                class="px-2 py-3 text-secondary"
                :icon="['fas', 'cancel']"
                @click="() => (oneOffNewLine.show = false)"
              />
              <font-awesome-icon
                id="btn-new-one_off-add"
                :icon="['fas', 'save']"
                role="button"
                class="px-2 py-3 text-primary"
                @click="saveNewLine(oneOffNewLine)"
              />
            </div>
          </th>
        </tr>
        <tr v-else>
          <th colspan="10">
            <div
              role="button"
              id="btn-one_off-add"
              class="d-flex justify-content-center align-items-center px-2 py-1"
              style="font-size: 12px"
              @click="() => (oneOffNewLine.show = true)"
            >
              <span>Add New</span>
              <font-awesome-icon class="ms-2" :icon="['fas', 'plus']" />
            </div>
          </th>
        </tr>
      </tfoot>
    </table>
  </div> -->
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
