<template>
  <div class="col" :style="{ flexDirection: showAsColumn ? 'column' : 'row' }">
    <div class="panel" :class="[!showAsColumn ? 'w-100 d-flex ' : 'p-3']">
      <div
        :class="[!showAsColumn ? 'me-4' : '']"
        style="position: relative; top: 0; right: 0"
      >
        <a :href="proxyValue.url" target="_blank">
          <img
            :src="proxyValue.primary_image"
            style="object-fit: cover"
            :style="{
              width: showAsColumn ? '100%' : '120px',
              height: showAsColumn ? '140px' : '120px',
            }"
          />
        </a>
        <div v-if="showAsColumn" class="m-0" style="position: absolute; top: 0; right: 0">
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
      </div>
      <div class="w-100">
        <div class="d-flex justify-content-between">
          <p class="big bold" :class="[showAsColumn ? 'truncate-2-lines' : '']">
            {{ proxyValue.model }}
          </p>
          <div v-if="!showAsColumn" class="d-flex justify-content-end">
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
            <div>
              <p class="bold big">
                {{ GBP.format(proxyValue.price?.net_spa || 0) }}
              </p>
            </div>
          </div>
        </div>
        <p :class="[showAsColumn ? 'truncate-3-lines' : null]">
          {{ proxyValue.sku }} - {{ proxyValue.name }}
        </p>
        <div v-if="showAsColumn">
          <p class="bold big mt-3">
            {{ GBP.format(proxyValue.price?.net_spa || 0) }}
          </p>
        </div>
        <div class="mt-2">
          <p style="font-weight: 500">Quantity</p>
          <div
            class="d-flex mb-2"
            :style="{
              flexDirection: showAsColumn ? 'column' : 'row',
            }"
          >
            <InputQuantity
              :id="'hardware-' + index"
              v-model="proxyValue.quantity"
              @change="(val) => checkQuantity(proxyValue, val, true)"
            />

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
              class="btn btn-add-quantity"
              @click="select"
            >
              Add to Compator
            </button>
          </div>
          <template v-if="hasSelect && showAsColumn">
            <hr />
            <button class="btn btn-add-quantity w-100" @click="select">
              Add to Compator
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed } from "vue";
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

const select = () => {
  if (!props.hasSelect) {
    return;
  }
  emit("select", proxyValue);
};
</script>
<style>
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
