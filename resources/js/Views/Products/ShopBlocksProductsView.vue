<script setup>
import _ from "lodash";
import { Paginator } from "primevue";
import { quote } from "@/Composables/useQuote";
import { categories } from "@composables/custom/shopblocks";
import { computed, onMounted, reactive, ref, watch } from "vue";

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
const selectedItems = ref([]);
const meta = reactive({
  page: 1,
  total: 0,
  overallTotal: 0,
  first: 1,
  limit: 12,
  isLoading: false,
});
const search = ref(null);
const showCards = ref(false);

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

const currentCategory = ref(null);

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
  let filters = [];
  Object.keys(filters).forEach((key) => {
    filters.push({
      column: key,
      operator: filters[key].operator,
      value: filters[key].value ?? null,
    });
  });
  if (currentCategory.value) {
    let array = [currentCategory.value];
    let items = [];

    do {
      let current = array.pop();
      items.push(current);
      let temp = categories.value.filter((x) => x.parent_id == current.id);
      array.push(...temp);
    } while (array.length != 0);

    filters.push({
      column: "product_category_id",
      operator: "in",
      value: items.map((x) => x.id),
    });
  }

  let query = {
    page: meta.page,
    limit: meta.limit,
    filters: filters,
    order: sort.value.order,
    orderBy: sort.value.orderBy,
    columns: "model,name",
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
  filterList.models = res.data?.filters?.model
    ? Object.keys(res.data.filters.model)
        .sort()
        .reduce((acc, key) => {
          acc[key] = res.data.filters.model[key];
          return acc;
        }, {})
    : [];
};

const addSelected = (item) => {
  if (selectedItems.value.length >= 4) {
    notyf.error("Comparator is full");
    return;
  }
  if (selectedItems.value.find((x) => x.sku == item.value.sku)) {
    notyf.error("Already added");
    return;
  }

  selectedItems.value.push(item.value);
};

const handlePaginator = (ev) => {
  meta.limit = ev.rows;
  meta.page = ev.page + 1;
  getProductsShopBlocks();
};

onMounted(async () => {
  await getProductsShopBlocks();
});

watch([filters, sort], () => {
  getProductsShopBlocks();
});

watch(currentCategory, () => {
  getProductsShopBlocks();
});

watch(
  search,
  _.debounce(() => {
    if (!search.value) {
      meta.page = 0;
    }
    getProductsShopBlocks();
  }, 300)
);
</script>
<template>
  <div id="mobile_step_shopblocks" class="step">
    <div class="row justify-content-end">
      <div class="col-3"></div>
      <div class="col-9">
        <div class="d-flex justify-content-between mb-4">
          <div class="d-flex justify-content-end">
            <button
              class="btn border"
              :class="[showCards ? 'btn-dark' : '']"
              @click="() => (showCards = !showCards)"
              style="border-radius: 5px 0 0 5px"
            >
              <font-awesome-icon :icon="['fas', 'grip']"></font-awesome-icon>
            </button>
            <button
              class="btn border"
              :class="[showCards ? '' : 'btn-dark']"
              @click="() => (showCards = !showCards)"
              style="border-radius: 0 5px 5px 0"
            >
              <font-awesome-icon :icon="['fas', 'grip-lines']"></font-awesome-icon>
            </button>
          </div>

          <p class="bigger text-primary bold">STEP {{ step }}/{{ step }}</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-3">
        <div class="rounded border border-surface-200 dark:border-surface-700 p-3">
          <div class="mb-3">
            <label class="form-label">Search</label>
            <input type="search" class="form-control" placeholder="" v-model="search" />
          </div>
          <hr />
          <label class="form-label">Categories</label>
          <ShopBlocksCategoriesTree v-model="currentCategory"></ShopBlocksCategoriesTree>
        </div>
      </div>
      <div class="col">
        <template v-if="meta.isLoading">
          <ProductSkeleton v-for="item in [0, 1, 2, 3, 4]"></ProductSkeleton
        ></template>
        <template v-else>
          <div class="text-center pt-2 pb-4" v-if="!productsHardware.length">
            No Results Found
          </div>
          <div class="row" :class="[showCards ? 'row-cols-4' : 'row-cols-1']">
            <ProductCard
              v-for="(product, index) in productsHardware"
              @select="(item) => addSelected(item)"
              :hasSelect="true"
              :showAsColumn="showCards"
              :index="index"
              v-model="productsHardware[index]"
            >
            </ProductCard>
          </div>
          <Paginator
            v-model:first="meta.first"
            :rows="meta.limit"
            :totalRecords="meta.total"
            :rowsPerPageOptions="[12, 24, 48]"
            @page="handlePaginator"
          >
          </Paginator
        ></template>
      </div>
    </div>
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
    <ProductCompareShopblocks
      v-if="selectedItems.length"
      v-model="selectedItems"
    ></ProductCompareShopblocks>
  </div>
</template>
<style></style>
