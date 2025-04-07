<script setup>
import { ref, createApp } from "vue";
import Dialog from "primevue/dialog";
import { createOrder } from "@/Composables/useOrders.js";
import { setQuote, quote } from "@composables/useQuote";
import { usePage } from "@inertiajs/vue3";
import WarningNote from "@/Components/WarningNote.vue";
import {checkWarnings} from "@composables/warning.js"

const props = defineProps({
  modelValue: Object,
  pricePlans: Array,
  preview: Boolean,
});

const page = usePage()
const tenant = page.props.tenant

let showModal = ref(false);
const isLoading = ref(false);
setQuote(props.modelValue);

const warningProviders = ref([
  { its: quote.items.some((item) => item.data?.providerName === "ITS") },
  { pxc: quote.items.some((item) => item.data?.providerName === "PXC") },
]);

const pricePlansDiscounts = ref([]);
props.pricePlans.forEach((e) => {
  let discount = props.modelValue.discounts.find((x) => x.entity?.code == e.code);
  pricePlansDiscounts.value.push({
    ...e,
    value: discount ? discount.discount : 0,
    entity: "price_plans",
  });
});

const saveQuote = async () => {
  isLoading.value = true;
  if (quote.discount === "") {
    isLoading.value = false;
    return;
  }
  try {
    let res = await axios.put(`/api/quotes/${quote.id}`, {
      discounts: [...pricePlansDiscounts.value],
    });
    setQuote(res.data);
    isLoading.value = false;
  } catch (ex) {
    isLoading.value = false;
    notyf.error(ex.response.data.message);
    isLoading.value = false;
  }
};

const resetQuote = async () => {
  isLoading.value = true;
  let result = await swal.fire({
    title: "Are you sure?",
  });
  if (["cancel", "backdrop"].includes(result.dismiss)) {
    isLoading.value = false;
    return;
  }

  quote.items.forEach((e) => {
    e.discount = 0;
  });
  pricePlansDiscounts.value.forEach((e) => (e.value = 0));
  try {
    let res = await axios.put(`/api/quotes/${quote.id}`, {
      discounts: [...pricePlansDiscounts.value],
      items: quote.items,
    });
    setQuote(res.data);
    isLoading.value = false;
  } catch (ex) {
    notyf.error(ex.response.data.message);
  }
};

const quoteTotal = (key) => {
  return quote.items
    .filter((x) => x.price_plan && x.price_plan.code == key)
    .reduce((acc, item) => (acc += item.total), 0);
};

const quoteQuantityPrice = (price_plan) => {
  let total = 0;
  let tax = 0;
  quote.items.forEach((e) => {
    if (e.price_plan && e.price_plan.code == price_plan) {
      total += e.price * e.quantity;
      tax += e.price * e.quantity * e.tax;
    }
  });
  return total + tax;
};

const disableButtonSendToOrder = ref(false);
const createOrderAndRedirect = async (id) => {
  disableButtonSendToOrder.value = true;
  const res = await createOrder(id);
  if (res) {
    window.location = "/dashboard";
  }
  disableButtonSendToOrder.value = false;
};

const items = ref([
  { label: "Home", url: "/" },
  {
    label: `Quote #${quote?.id}`,
    url: quote
      ? ["REGISTRY", "SUBMITTED"].includes(quote.status)
        ? "#"
        : `/quotes/${quote.id}/edit`
      : "#",
  },
  { label: "Summary" },
]);
</script>
<template>
  <div class="panel panel-mobile">
    <div class="row justify-content-between">
      <div class="col-lg-8 mb-5 price-summary">
        <Header title="Build your deal" :breadcrumbs="items"></Header>
        <div v-if="warningProviders.some((obj) => Object.values(obj).includes(true))">
          <div class="row">
            <div class="col">
              <InfoBox class="mb-4 py-0" severity="danger">
                <WarningNote :warnings="warningProviders" />
              </InfoBox>
            </div>
          </div>
        </div>
        <TableCustom
          v-if="quote.solution_type.configurations?.has_custom_table == 1"
        ></TableCustom>
        <TableDealMaker
          v-else
          :pricePlans="pricePlans"
          :preview="preview"
        ></TableDealMaker>

        <div class="mb-5">
          <div>
            <p class="payment-info border-bottom pb-3 mb-3 big bold">
              <span class="text-primary">1 payment </span> of
              {{ GBP.format(quoteTotal("SINGLE")) }}<br />
              followed by
              <span class="text-primary"> monthly payments</span> of
              {{ GBP.format(quoteQuantityPrice("MONTHLY")) }}
            </p>
          </div>
          <div class="d-flex justify-content-between">
            <a id="btn-previous" class="btn text-primary" :href="route('quotes.edit', { quote: quote.id }) + '?step=2'">
              <font-awesome-icon class="me-2" :icon="['fas', 'arrow-left']"></font-awesome-icon>
              Previous</a>
            <div>
              <ShareableQuote></ShareableQuote>
              <a class="btn btn-outline-primary me-2" @click="checkWarnings(warningProviders, route('quotes.preview', { quote: quote.id }))">
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview
              </a>
              <button hidden class="btn btn-outline-primary me-2">
                <font-awesome-icon class="me-2" :icon="['fas', 'envelope']">
                </font-awesome-icon>
                Email this quote
              </button>
              <button :hidden="!(tenant.configurations.orders_flow_is_active === '1')"
                :disabled="disableButtonSendToOrder" class="btn btn-primary" @click="createOrderAndRedirect(quote.id)">
                <font-awesome-icon class="me-2" :icon="['fas', 'thumbtack']">
                </font-awesome-icon>Send to Order
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 ps-xl-5">
        <InfoBox>
          <div style="font-size: 14px">
            <b style="font-size: 16px">Quote {{ quote.id }} for {{ quote.account.name }}</b>
            <br />
            Contact Number: 0124567890<br />
            Email: firstname.lastname@gmail.com
          </div>
        </InfoBox>
        <InfoBox v-if="quote.orders.length > 0">
          <h6>
            An Order related to this Quote was
            <span class="badge text-bg-secondary">{{ quote.status }}</span>
          </h6>
          <button class="btn btn-sm btn-primary" @click="showModal = true">
            See Rejection Notes
          </button>
        </InfoBox>
        <div class="price-mobile-sidebar total-quote-discount px-4 pt-4 pb-2 mb-3">
          <h3 class="mb-3">Total Quote Discount</h3>
          <div v-for="pricePlan in pricePlansDiscounts" class="d-flex justify-content-between align-items-center mb-3">
            <label class="form-label m-0">{{ pricePlan.name }}</label>

            <div class="input-group p-0 w-50">
              <input :id="`input-global-${pricePlan.code}-discount`" type="number" class="form-control"
                v-model="pricePlan.value" />
              <span class="input-group-text">%</span>
            </div>
          </div>
          <button id="btn-apply-global-discount" class="btn btn-secondary w-100 mb-2" @click="saveQuote()"
            :disabled="isLoading">
            Apply to quote
          </button>
          <button id="btn-reset-quote" class="btn w-100" @click="resetQuote()" :disabled="isLoading">
            Reset quote
          </button>
        </div>
        <div class="price-mobile-sidebar commission-calculator px-4 pt-4 pb-2 mb-3">
          <h3 class="mb-3">Commission Calculator</h3>
          <p class="mb-2" style="font-size: 14px">
            Currently, you will make <b>{{ GBP.format(quote.commission_value) }}</b>
            commission and
            <b>{{ quote.margin.toFixed(2) }} %</b>
            margin on this quote.
          </p>
          <div class="p-3 mb-3" style="background: #e9edf0">
            <div class="mb-3">
              <label class="form-label">Margin</label>
              <div class="input-group">
                <input id="input-margin-order" class="form-control" :value="quote.margin.toFixed(2) + ' %'" disabled />
                <span class="input-group-text"><font-awesome-icon :icon="['fas', 'lock']"></font-awesome-icon></span>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Commission
                <b>(<span id="order-tax-commission"> {{ quote.commission_rate }}</span>%)</b>
              </label>
              <div class="input-group">
                <input id="input-commission_value-order" class="form-control"
                  :value="GBP.format(quote.commission_value)" disabled />
                <span class="input-group-text"><font-awesome-icon :icon="['fas', 'lock']"></font-awesome-icon></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Dialog v-model:visible="showModal" modal :style="{ width: '40rem' }" :draggable="false">
      <template #header>
        <h2>Rejected Reasons</h2>
      </template>
      <div class="panel px-3">
        <div class="row">
          <table class="table reject-reasons-table">
            <thead>
              <tr>
                <th style="width: 5%">Order</th>
                <th>Note</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in quote.orders">
                <td>#{{ order.id }}</td>
                <td>
                  <pre class="custom-pre">{{ order.note }}</pre>
                </td>
              </tr>
            </tbody>
          </table>
          <button class="btn btn-primary" @click="showModal = false">Close</button>
        </div>
      </div>
    </Dialog>
  </div>
</template>
<style>
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
  margin: 0;
}
</style>
