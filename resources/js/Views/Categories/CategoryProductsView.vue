<script setup>
import queryString from "query-string";
import { computed, reactive, ref, watch } from "vue";
import { quote, getQuote } from "@composables/useQuote";
import _ from "lodash";
import {
  updateQuoteItem,
  removeQuoteItem,
  createQuoteItem,
} from "@composables/useQuoteItem";
import Paginator from "primevue/paginator";
import { Skeleton } from "primevue";

const meta = reactive({
  page: 1,
  total: 0,
  overallTotal: 0,
  first: 1,
  limit: 5,
  isLoading: false,
});

const props = defineProps({
  category: Object,
  account: Object,
  modelValue: Number,
  index: Number,
  total_categories: Number,
});

const emit = defineEmits(["update:modelValue"]);
const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit("update:modelValue", val);
  },
});

const products = ref([]);

const getProducts = async () => {
  meta.isLoading = true;
  let query = queryString.stringify({
    page: meta.page,
    limit: meta.limit,
    filters: JSON.stringify([
      {
        column: "categories.id",
        operator: "equals",
        value: props.category.id,
      },
    ]),
    account_id: props.account.id,
  });
  let res = await axios.get("/api/products" + "?" + query);
  res.data.data.forEach((e) => {
    let temp = quote.items.find((x) => x.product_code == e.code);
    e.quantity = temp ? temp.quantity : 0;
    e.contract_length = 12;
  });
  products.value = res.data.data;

  meta.first = (meta.page - 1) * meta.limit;
  meta.total = res.data.total;
  meta.overallTotal = res.data.overall_total;
  meta.isLoading = false;
};

const handlePaginator = (ev) => {
  meta.limit = ev.rows;
  meta.page = ev.page + 1;
  getProducts();
};

const checkQuantity = _.debounce(async (product, temp) => {
  if (product.stock <= temp) {
    temp = product.stock;
  }
  product.quantity = temp;
  if (product.quantity == 0) {
    return;
  }

  let item = quote.items.find((x) => x.product_code == product.code);
  if (!item) {
    createQuoteItem({
      quote_id: quote.id,
      product_code: product.code,
      product_id: product.id,
      quantity: product.quantity,
      contract_length: product.contract_length,
      name: product.name,
      raw_price: product.price ? product.price.net : 0,
      price: product.price ? product.price.net_spa : 0,
      price_plan_id: product.price_plan_id,
    });
  } else {
    if (product.quantity == 0) {
      removeQuoteItem(item.id);
    } else {
      updateQuoteItem(item.id, { quantity: product.quantity });
    }
  }

  getQuote(quote.id);
}, 200);

getProducts();
</script>

<template>
  <div v-if="proxyValue == props.index" :id="'step-' + (props.index + 1)" class="step">
    <div class="d-flex justify-content-between mb-3">
      <h2>Select some products</h2>
      <p class="bigger bold text-primary">
        STEP {{ props.index + 1 }}/{{ total_categories }}
      </p>
    </div>
    <div v-if="!meta.isLoading">
      <div v-for="(product, index) in products" class="mobile-item mobile-product mb-3">
        <div class="d-flex justify-content-between">
          <div class="d-flex align-items-center mb-2">
            <h4 class="m-0">{{ product.name }}</h4>
            <p class="m-0 ms-2">
              {{ product.description }} /
              {{ format.GBP(product.price?.net_spa || 0) }}
            </p>
          </div>
          <font-awesome-icon :icon="['fa', 'circle-info']" />
        </div>
        <div class="mobile-product-quantity">
          <label>Quantity</label>
          <div class="d-flex align-items-center pb-3">
            <div :style="{ width: '150px' }">
              <InputQuantity
                :id="'bolt-' + index"
                v-model="product.quantity"
                @change="(ev) => checkQuantity(product, ev)"
              />
            </div>

            <button
              :id="'btn-plan-' + index + '-add-25'"
              type=" button"
              class="btn btn-sm ms-4 btn-add-custom-quantity"
              @click="checkQuantity(product, product.quantity + 25)"
            >
              +25
            </button>
            <button
              :id="'btn-plan-' + index + '-add-50'"
              type=" button"
              class="btn btn-sm mx-2 btn-add-custom-quantity"
              @click="checkQuantity(product, product.quantity + 50)"
            >
              +50
            </button>
            <button
              :id="'btn-plan-' + index + '-add-100'"
              type=" button"
              class="btn btn-sm btn-add-custom-quantity"
              @click="checkQuantity(product, product.quantity + 100)"
            >
              +100
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-else v-for="n in meta.limit" class="mobile-item mobile-product mb-3">
      <div class="d-flex justify-content-between mb-3">
        <div class="d-flex">
          <Skeleton width="10rem" height="1.7rem" class="mb-2 me-2"></Skeleton>
          <Skeleton width="10rem" height="1.7rem" class="mb-2"></Skeleton>
        </div>
        <Skeleton shape="circle" size="1.5rem" class="mb-2"></Skeleton>
      </div>
      <Skeleton width="5rem" class="mb-2 me-2"></Skeleton>
      <div class="d-flex">
        <Skeleton width="10rem" height="1.7rem" class="mb-2 me-3"></Skeleton>
        <Skeleton width="4rem" height="1.7rem" class="mb-2 me-2"></Skeleton>
        <Skeleton width="4rem" height="1.7rem" class="mb-2 me-2"></Skeleton>
        <Skeleton width="4rem" height="1.7rem" class="mb-2 me-2"></Skeleton>
      </div>
    </div>
    <Paginator
      v-model:first="meta.first"
      :rows="meta.limit"
      :totalRecords="meta.total"
      :rowsPerPageOptions="[5, 10, 20]"
      @page="handlePaginator"
    >
    </Paginator>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div>
        <button
          v-if="props.index > 0"
          :id="'btn-step-{{ props.index + 1}}-previous'"
          class="btn text-primary step-nav btn-sm me-3"
          type="button"
          @click="() => proxyValue--"
        >
          <font-awesome-icon :icon="['fas', 'arrow-left']" /> Previous
        </button>
      </div>
      <div>
        <span id="total_mobile_users">
          Add {{ products.reduce((acc, x) => (acc = acc + x.quantity), 0) }}
          {{ category.name }}
        </span>
        <button
          :id="'btn-step-' + (props.index + 1) + '-next'"
          class="ms-3 btn btn-primary step-nav"
          type="button"
          @click="() => proxyValue++"
        >
          Next
          <font-awesome-icon :icon="['fas', 'arrow-right']" />
        </button>
      </div>
    </div>
  </div>
</template>

<style></style>
