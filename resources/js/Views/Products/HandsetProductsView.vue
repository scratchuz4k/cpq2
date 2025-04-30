<script setup>
import _ from "lodash";
import Select from "primevue/select";
import MultiSelect from "primevue/multiselect";
import Paginator from "primevue/paginator";
import { quote, getQuote } from "@/Composables/useQuote";
import {
  updateQuoteItem,
  removeQuoteItem,
  createQuoteItem,
} from "@composables/useQuoteItem";
import { computed, onMounted, reactive, ref, watch } from "vue";
import InputQuantity from "@/Components/InputQuantity.vue";

const emit = defineEmits(["save", "update:modelValue"]);
const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit("update:modelValue", val);
  },
});

const props = defineProps({
  modelValue: Number,
  quote: Object,
});

const meta = reactive({
  page: 1,
  total: 0,
  overallTotal: 0,
  first: 1,
  limit: 5,
  isLoading: false,
});
const search = ref(null);

const productsHardware = ref([]);

const filters = reactive({
  model: {
    operator: "in",
    value: null,
  },
  name: {
    operator: "contains",
    value: null,
  },
});

const filterList = reactive({
  models: [],
  storage: [
    { label: "1TB", value: "1TB" },
    { label: "512 GB", value: "512 GB" },
    { label: "256 GB", value: "256 GB" },
    { label: "128 GB", value: "128 GB" },
    { label: "64 GB", value: "64 GB" },
    { label: "32 GB", value: "32 GB" },
    { label: "16 GB", value: "16 GB" },
  ],
});

const sorts = [
  {
    label: "Lowest Price",
    order: "asc",
    orderBy: "price.net",
  },
  {
    label: "Highest Price",
    order: "desc",
    orderBy: "price.net",
  },
];

const sort = ref(sorts[1]);
const step = ref(props.modelValue + 1);

const getProductsShopBlocks = async () => {
  meta.isLoading = true;
  let temp = [];
  Object.keys(filters).forEach((key) => {
    temp.push({
      column: key,
      operator: filters[key].operator,
      value: filters[key].value ?? null,
    });
  });
  temp.push({
    column: "product_category_id",
    operator: "equals",
    value: 22,
  });

  let query = {
    category_id: 22,
    page: meta.page,
    limit: meta.limit,
    filters: temp,
    order: sort.value.order,
    orderBy: sort.value.orderBy,
    search: search.value,
    account_id: quote.account_id,
  };

  let res = await axios.post("/api/external/get-articles-from-shop-blocks", query);
  productsHardware.value = res.data.data;
  productsHardware.value.forEach((e) => {
    let temp = quote.items.find((x) => x.product_code == e.code);
    e.quantity = temp ? temp.quantity : 0;
    e.contract_length = 1;
  });

  meta.first = (meta.page - 1) * meta.limit;
  meta.total = res.data.total;
  meta.overallTotal = res.data.overall_total;
  meta.isLoading = false;
  filterList.models = Object.keys(res.data.filters.model)
    .sort()
    .reduce((acc, key) => {
      acc[key] = res.data.filters.model[key];
      return acc;
    }, {});
};

const clearFilters = () => {
  Object.keys(filters).forEach((key) => (filters[key].value = null));
};

const handlePaginator = (ev) => {
  meta.limit = ev.rows;
  meta.page = ev.page + 1;
  getProductsShopBlocks();
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
    await createQuoteItem({
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
      await removeQuoteItem(item.id);
    } else {
      await updateQuoteItem(item.id, { quantity: product.quantity });
    }
  }

  await getQuote(quote.id);
}, 500);

onMounted(async () => {
  await getProductsShopBlocks();
});

watch([filters, sort], () => {
  getProductsShopBlocks();
});

watch(
  search,
  _.debounce(() => {
    getProductsShopBlocks();
  }, 300)
);
</script>
<template>
  <div id="mobile_step_shopblocks" class="step">
    <div class="d-flex justify-content-between mb-4">
      <h2 class="m-0">Finally, choose your add-ons</h2>
      <p class="bigger text-primary bold">STEP {{ step }}/{{ step }}</p>
    </div>
    <div class="d-flex justify-content-between mb-3">
      <h3>Handsets</h3>
      <div>
        <div class="input-group">
          <input
            type="search"
            class="form-control"
            placeholder="Search Handsets"
            v-model="search"
          />
          <button class="btn btn-primary" type="submit" id="button-addon2">
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
          </button>
        </div>
      </div>
    </div>
    <div class="panel mb-3">
      <div class="d-flex justify-content-between mb-2">
        <div class="d-flex">
          <h4>Filters</h4>
          <ul class="mb-0" style="list-style: none; display: flex; flex-wrap: wrap">
            <template v-for="filter in filters">
              <li class="bullet me-2 mb-2" v-for="(item, index) in filter.value">
                <p class="small">
                  {{ item }}
                  <span
                    @click="() => filter.value.splice(index, 1)"
                    style="cursor: pointer"
                  >
                    <font-awesome-icon :icon="['fas', 'xmark']"
                  /></span>
                </p>
              </li>
            </template>
          </ul>
        </div>
        <div>
          <button
            @click="clearFilters()"
            class="btn border-0 p-0 text-primary"
            type="button"
          >
            Clear filters
          </button>
        </div>
      </div>
      <div class="row">
        <div class="col-4">
          <label class="mb-1">Models</label>
          <MultiSelect
            v-model="filters.model.value"
            :options="Object.keys(filterList.models)"
            placeholder="All"
            filter
            class="w-100"
          >
          </MultiSelect>
        </div>
        <div class="col-4">
          <label class="mb-1">Storage</label>
          <MultiSelect
            v-model="filters.name.value"
            :options="filterList.storage"
            optionLabel="label"
            optionValue="value"
            placeholder="All"
            filter
            class="w-100"
          >
          </MultiSelect>
        </div>
        <div class="col-4">
          <label class="mb-1">Sort by</label>
          <Select
            v-model="sort"
            :options="sorts"
            optionLabel="label"
            placeholder="All"
            class="w-100"
          >
          </Select>
        </div>
      </div>
    </div>

    <div class="text-center pt-2 pb-4" v-if="!productsHardware.length">
      No Results Found
    </div>
    <div v-for="(product, index) in productsHardware" class="panel p-4 mb-3">
      <div class="row">
        <div class="col-2 d-flex justify-content-center">
          <a :href="product.url" target="_blank">
            <img :src="product.primary_image" style="width: 100px; object-fit: contain" />
          </a>
        </div>
        <div class="col-10">
          <div class="d-flex w-100 justify-content-between">
            <p class="big bold">{{ product.model }}</p>
            <div class="d-flex justify-content-end">
              <div class="me-3">
                <p
                  v-if="product.stock > 0"
                  class="badge text-bg-warning"
                  style="--bs-bg-opacity: 0.5"
                >
                  <span :id="'hardware-' + index + '-stock'">
                    {{ product.stock }}
                  </span>
                  in stock
                </p>
                <p v-else class="badge text-bg-danger" style="--bs-bg-opacity: 0.5">
                  <font-awesome-icon :icon="['fa', 'warning']" />
                  <span :id="'hardware-' + index + '-stock'"></span>
                  OUT OF STOCK
                </p>
              </div>
              <div>
                <p class="bold big">{{ GBP.format(product.price?.net_spa || 0) }}</p>
              </div>
            </div>
          </div>
          <p class="small">{{ product.sku }} - {{ product.name }}</p>
          <div class="pt-3">
            <p style="font-weight: 500">Quantity</p>
            <div class="d-flex align-items-center mb-2">
              <InputQuantity
                :id="'hardware-' + index"
                v-model="product.quantity"
                @change="(val) => checkQuantity(product, val, true)"
              />

              <button
                :id="'btn-hardware-' + index + '-add-25'"
                class="btn btn-sm ms-4 btn-add-quantity "
                @click="checkQuantity(product, product.quantity + 25, true)"
              >
                +25
              </button>
              <button
                :id="'btn-hardware-' + index + '-add-50'"
                class="btn btn-sm ms-2 btn-add-quantity "
                @click="checkQuantity(product, product.quantity + 50, true)"
              >
                +50
              </button>
              <button
                :id="'btn-hardware-' + index + '-add-100'"
                class="btn btn-sm ms-2 btn-add-quantity "
                @click="checkQuantity(product, product.quantity + 100, true)"
              >
                +100
              </button>
            </div>
          </div>
        </div>
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
          v-if="step > 0"
          id="btn-step-2-previous"
          class="btn text-primary step-nav btn-sm me-3"
          type="button"
          @click="() => proxyValue--"
        >
          <font-awesome-icon :icon="['fas', 'arrow-left']" /> Previous
        </button>
      </div>
      <div>
        <span id="total_add_ons">
          Add
          {{ productsHardware.reduce((acc, item) => (acc += item.quantity), 0) }}
          handsets
        </span>
        <button
          id="btn-step-2-next"
          class="ms-3 btn btn-primary step-nav"
          type="button"
          @click="emit('save')"
        >
          Next
          <font-awesome-icon :icon="['fas', 'arrow-right']" />
        </button>
      </div>
    </div>
  </div>
</template>
<style></style>
