<script setup>
import _ from "lodash";
import { reactive, ref } from "vue";
import InputQuantity from "@/Components/InputQuantity.vue";
import { createOrder } from "@/Composables/useOrders.js";
import { setQuote, quote, getLeafNodesWithChildren } from "@composables/useQuote";
import {checkWarnings} from "@composables/warning.js";

const props = defineProps({
  modelValue: Object,
  pricePlans: Array,
  preview: Boolean,
});

setQuote(props.modelValue);

let temp = getLeafNodesWithChildren(quote.items);
const warningProviders = ref([
  { its: temp.some((item) => item.data?.providerName === "ITS") },
  { pxc: temp.some((item) => item.data?.providerName === "PXC") },
]);

const items = ref([
  { label: "Home", url: "/dashboard" },
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
  <Header title="Create a quote" :breadcrumbs="items"></Header>
  <InfoConnectivity v-if="!preview" :quote="quote"></InfoConnectivity>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <p class="fs-4 me-3 mb-0"><b>Your quote</b></p>
      <p class="mb-0 text-primary bold fs-5 pt-1">STEP 2/2</p>
    </div>
    <div class="d-flex justify-content-between align-items-center">
      <ShareableQuote></ShareableQuote>
      <button hidden class="btn btn-outline-primary me-2" @click="createOrder(quote.id)">
        <font-awesome-icon class="me-2" :icon="['fas', 'thumbtack']"> </font-awesome-icon
        >Send to Order
      </button>
      <button
        v-if="modelValue.items.length == 0"
        class="btn btn-outline-primary me-2"
        type="button"
        disabled
      >
        <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
        Preview PDF
      </button>
      <a
        v-else
        class="btn btn-outline-primary me-2"
        type="submit"
        @click="checkWarnings(warningProviders, route('quotes.preview', { quote: quote.id }))"
      >
        <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
        Preview PDF
      </a>
      <a :href="route('dashboard')" class="btn btn-primary" type="submit">
        Save and close
      </a>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col">
      <!-- Product List -->
      <!-- In a real application, this would be dynamically generated based on the selected solution type -->
      <div v-if="warningProviders.some((obj) => Object.values(obj).includes(true))">
        <div class="row">
          <div class="col">
            <InfoBox class="mb-4 py-0" severity="danger">
              <div class="small">
                <div class="mb-2 fw-bold">Important Information</div>
                <div v-for="provider in warningProviders" class="mb-2">
                  <div v-if="Object.values(provider)[0]">
                    <span
                      class="small fw-bold bg-light rounded-2 py-1 px-2 me-1 text-capitalize"
                    >
                      {{ Object.keys(provider)[0].toUpperCase() }}
                    </span>
                    <i>
                      Please note, quote references for
                      {{ Object.keys(provider)[0].toUpperCase() }} quotes cannot currently
                      be used to place an order. To obtain a valid quote for ordering
                      please generate the quote directly on the
                      {{ Object.keys(provider)[0].toUpperCase() }}
                      portal.
                    </i>
                  </div>
                </div>
              </div>
            </InfoBox>
          </div>
        </div>
      </div>
      <TableSimple :preview="preview"></TableSimple>

      <div class="row justify-content-end">
        <div class="col-6">
          <TableTotal :quote="quote"></TableTotal>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="d-flex justify-content-end align-items-center">
      <ShareableQuote></ShareableQuote>
      <button hidden class="btn btn-outline-primary me-2" @click="createOrder(quote.id)">
        <font-awesome-icon class="me-2" :icon="['fas', 'thumbtack']"> </font-awesome-icon
        >Send to Order
      </button>
      <button
        v-if="modelValue.items.length == 0"
        class="btn btn-outline-primary me-2"
        type="button"
        disabled
      >
        <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
        Preview PDF
      </button>
      <a
        v-else
        class="btn btn-outline-primary me-2"
        type="submit"
        @click="checkWarnings(warningProviders, route('quotes.preview', { quote: quote.id }))"
      >
        <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
        Preview PDF
      </a>
      <a :href="route('dashboard')" class="btn btn-primary" type="submit">
        Save and close
      </a>
    </div>
  </div>
</template>

<style>
.delete-btn {
  all: unset;
  height: 25px;
  color: var(--purple);

  &:hover {
    color: var(--red) !important;
  }
}

.cpq-qty {
  button {
    border: 2px solid var(--grey);
    border-radius: 8px;

    &:hover {
      background-color: var(--purple);
      border-color: var(--purple);
      color: white;
    }

    &.decrease {
      border-top-right-radius: 0px;
      border-bottom-right-radius: 0px;
    }

    &.increase {
      border-top-left-radius: 0px;
      border-bottom-left-radius: 0px;
    }
  }

  .cpq-qty-input {
    border-width: 2px !important;
    border-color: var(--grey);
  }
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Remove arrows in Firefox */
input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>
