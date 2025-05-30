<template>
  <div class="col" :style="{ flexDirection: showAsColumn ? 'column' : 'row' }">
    <div
      class="panel"
      :class="[!showAsColumn ? 'w-100 d-flex ' : 'p-3']"
      style="position: relative; top: 0; right: 0"
    >
      <div :class="[!showAsColumn ? 'me-4' : '']">
        <div class="d-flex justify-content-center align-items-middle">
          <a :href="proxyValue.url" target="_blank">
            <img
              :src="proxyValue.primary_image ?? '/images/no_image.png'"
              style="object-fit: contain"
              :style="{
                width: '140px',
                height: '140px',
              }"
            />
          </a>
        </div>
      </div>
      <div class="w-100">
        <div class="d-flex justify-content-between">
          <p
            class="bold truncate-1-lines me-3"
            :class="[showAsColumn ? 'truncate-2-lines' : '']"
          >
            {{ proxyValue.model.name }}
          </p>
          <div
            :class="[
              !showAsColumn
                ? 'd-flex justify-content-end'
                : 'position-absolute top-0 end-0 mt-2',
            ]"
          >
            <div class="me-3">
              <p
                v-if="proxyValue.stock > 0"
                class="badge text-bg-warning"
                style="--bs-bg-opacity: 0.5"
              >
                <span :id="'hardware-' + index + '-stock'">
                  {{ proxyValue.stock }}
                </span>
                in stock
              </p>
              <p v-else class="badge text-bg-danger" style="--bs-bg-opacity: 0.5">
                <font-awesome-icon :icon="['fa', 'warning']" />
                <span :id="'hardware-' + index + '-stock'"></span>
                OUT OF STOCK
              </p>
            </div>
            <div v-if="!showAsColumn">
              <p class="bold big">
                {{ format.GBP(proxyValue.price?.net_spa || 0) }}
              </p>
            </div>
          </div>
        </div>
        <p
          :class="[showAsColumn ? 'truncate-3-lines' : null]"
          v-html="proxyValue.name"
        ></p>
        <a style="cursor: pointer" @click="openModal()">More Info...</a>
        <div v-if="showAsColumn">
          <p class="bold big mt-3">
            {{ format.GBP(proxyValue.price?.net_spa || 0) }}
          </p>
        </div>
        <div class="mt-3">
          <div
            class="d-flex"
            :style="{
              flexDirection: showAsColumn ? 'column' : 'row',
            }"
          >
            <div
              :style="{
                width: showAsColumn ? '100%' : '150px',
              }"
            >
              <InputQuantity
                :id="'hardware-' + index"
                v-model="proxyValue.quantity"
                @change="(val) => checkQuantity(proxyValue, val, true)"
              />
            </div>

            <template v-if="!showAsColumn">
              <button
                :id="'btn-hardware-' + index + '-add-25'"
                class="btn btn-sm ms-4 btn-add-quantity"
                @click="checkQuantity(proxyValue, proxyValue.quantity + 25, true)"
              >
                +25
              </button>
              <button
                :id="'btn-hardware-' + index + '-add-50'"
                class="btn btn-sm ms-2 btn-add-quantity"
                @click="checkQuantity(proxyValue, proxyValue.quantity + 50, true)"
              >
                +50
              </button>
              <button
                :id="'btn-hardware-' + index + '-add-100'"
                class="btn btn-sm ms-2 me-4 btn-add-quantity"
                @click="checkQuantity(proxyValue, proxyValue.quantity + 100, true)"
              >
                +100
              </button>
            </template>
            <button
              v-if="hasSelect && !showAsColumn"
              class="btn btn-add-comparator"
              @click="select"
            >
              Add to Comparator
            </button>
          </div>
          <template v-if="hasSelect && showAsColumn">
            <hr />
            <button class="btn btn-add-comparator w-100" @click="select">
              Add to Comparator
            </button>
          </template>
        </div>
      </div>
    </div>
    <Dialog
      v-model:visible="showModal"
      modal
      :header="proxyValue.model.name"
      :style="{ width: '80rem' }"
    >
      <div class="panel h-100">
        <p class="bold">General</p>
        <hr />
        <table class="table">
          <tbody>
            <tr>
              <th>Sku</th>
              <td>
                {{ proxyValue.sku }}
              </td>
            </tr>
            <tr>
              <th>Model</th>
              <td>
                {{ proxyValue.model.name }}
              </td>
            </tr>
            <tr>
              <th>Name</th>
              <td>
                {{ proxyValue.name }}
              </td>
            </tr>
            <tr>
              <th>Description</th>
              <td>
                <div v-html="proxyValue.description"></div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-between align-items-end mb-3 mt-5">
        <p class="bold">Features</p>
        <div>
          <IconField>
            <InputIcon>
              <font-awesome-icon icon="fas fa-search" />
            </InputIcon>
            <InputText
              :id="'select-' + props.id + '-search'"
              v-model="searchInput"
              placeholder="Search"
            />
          </IconField>
        </div>
      </div>
      <hr />
      <div v-if="loadingFeatures" class="row">
        <div class="col text-center">Loading...</div>
      </div>
      <div v-else class="row">
        <div class="col">
          <table class="table">
            <tbody>
              <template v-for="group in temp">
                <tr>
                  <th colspan="2" style="background: whitesmoke">
                    <div
                      @click="() => (group.show = !group.show)"
                      style="cursor: pointer"
                    >
                      <div class="d-flex justify-content-between w-100">
                        <p>{{ group.name }}</p>
                        <div>
                          <font-awesome-icon
                            class="w-100"
                            v-if="group.show"
                            style="color: var(--color-purple)"
                            :icon="['fas', 'chevron-down']"
                          />
                          <font-awesome-icon
                            class="w-100"
                            v-else
                            style="color: var(--color-purple)"
                            :icon="['fas', 'chevron-up']"
                          />
                        </div>
                      </div>
                    </div>
                  </th>
                </tr>
                <tr v-if="group.show" v-for="row in group.features">
                  <th>{{ row.label }}</th>
                  <td>{{ row.items[0] }}</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </Dialog>
  </div>
</template>
<script setup>
import queryString from "query-string";
import Dialog from "primevue/dialog";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import { ref, computed, watch } from "vue";
import _ from "lodash";
import {
  updateQuoteItem,
  removeQuoteItem,
  createQuoteItem,
} from "@composables/useQuoteItem";
import { quote, getQuote } from "@/Composables/useQuote";
const props = defineProps({
  index: Number,
  hasSelect: Boolean,
  showAsColumn: Boolean,
  modelValue: Object,
});

const showModal = ref(false);
const loadingFeatures = ref(false);
const emit = defineEmits(["update:modelValue", "select"]);
const proxyValue = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

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

const select = () => {
  if (!props.hasSelect) {
    return;
  }
  emit("select", proxyValue);
};

const searchInput = ref("");
const temp = ref([]);
const getFeaturesTable = () => {
  temp.value = [];
  if (!proxyValue.value.features) {
    return;
  }
  let lowerSearch = searchInput.value.toLowerCase();
  let features = proxyValue.value.features.filter((obj) =>
    obj.label.toLowerCase().includes(lowerSearch)
  );

  features.forEach((e) => {
    let gIndex = temp.value.findIndex((t) => t.name == e.group);
    if (gIndex == -1) {
      temp.value.push({ name: e.group, show: true, features: [] });
      gIndex = temp.value.length - 1;
    }

    let index = temp.value[gIndex].features.findIndex((t) => t.key == e.key);
    if (index == -1) {
      temp.value[gIndex].features.push({ key: e.key, label: e.label, items: [] });
      index = temp.value[gIndex].features.length - 1;
    }
    temp.value[gIndex].features[index].items.push(e.value);
  });
};

const setAltImg = (event) => {
  event.target.src = "/images/no_image.png";
};
const openModal = async () => {
  loadingFeatures.value = true;
  showModal.value = !showModal.value;
  if (!proxyValue.value.features) {
    let query = queryString.stringify({
      filters: JSON.stringify([
        {
          column: "product_id",
          operator: "equals",
          value: proxyValue.value.id,
        },
      ]),
    });

    let res = await axios.get("/api/shop-blocks/product-features?" + query);
    proxyValue.value.features = res.data?.data.reduce(
      (acc, x) => acc.concat(x.items),
      []
    );
  }
  await getFeaturesTable();
  loadingFeatures.value = false;
};

watch(searchInput, () => {
  getFeaturesTable();
});
</script>
<style scoped>
.table tbody th {
  width: 30%;
}

.table tbody td {
  width: 70%;
}
.truncate-1-lines {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 2em; /* or a fixed px value, e.g. 24px */
  height: calc(2em * 1); /* 3 lines × line-height */
}

.truncate-2-lines {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 2em; /* or a fixed px value, e.g. 24px */
  height: calc(2em * 2); /* 3 lines × line-height */
}

.truncate-3-lines {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 2em; /* or a fixed px value, e.g. 24px */
  height: calc(2em * 3); /* 3 lines × line-height */
  font-size: 14px !important;
}

.shopblock-product:hover {
  cursor: pointer;
  box-shadow: var(--color-purple) 0px 1px 8px 0px;
}
</style>
