<script setup>
import { ref } from "vue";
import { setQuote, quote } from "@composables/useQuote";
const props = defineProps({
  modelValue: Object,
  pricePlans: Object,
});

setQuote(props.modelValue);

const warningProviders = ref([
  { its: quote.items.some((item) => item.data?.providerName === "ITS") },
  { pxc: quote.items.some((item) => item.data?.providerName === "PXC") },
]);


const showDate = (days = 0) => {
  const dateObj = new Date();
  dateObj.setDate(dateObj.getDate() + days);
  const currentDate =
    dateObj.getDate() + "/" + (dateObj.getMonth() + 1) + "/" + dateObj.getFullYear();
  return currentDate;
};

const solutionsViews = {
  Simple: "TableSimple",
  DealMaker: "TableDealMaker",
};

let component = props.modelValue.solution_type.configurations?.component;
let view =
  component && Object.keys(solutionsViews).includes(component)
    ? solutionsViews[component]
    : "TableDealMaker";
</script>
<template>
  <div class="panel d-flex justify-content-center">
    <div id="print" class="page">
      <div class="row">
        <div class="col" id="header_left">
          <div class="preview-logo">
            <app-logo></app-logo>
          </div>
        </div>
        <div class="col text-end" id="header_right">
          <p>{{ quote.account?.name }}<br />{{ quote.address_data?.label }}</p>
          <p>Tel: +44 (0) 191 300 700</p>
          <p>Email: sales@example.com</p>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col">
          <h1 class="mb-1">Quote Q-{{ quote.id }}</h1>
          <div class="row mb-3">
            <div class="col">
              <p>
                Quotation Date: {{ showDate() }}<br />
                Valid Date: {{ showDate(30) }}
              </p>
            </div>
          </div>
        <div v-if="warningProviders.some((obj) => Object.values(obj).includes(true))">
            <div class="row">
            <div class="col">
                <InfoBox class="mb-4 p-0" severity="error">
                <div class="small">
                    <div class="mb-2 fw-bold" style="color:#dc3545">Important Information:</div>
                    <div v-for="provider in warningProviders" class="mb-2">
                    <div v-if="Object.values(provider)[0]" style="color:#dc3545; line-height: 1.2">
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
        <div>
            <component
              :is="view"
              v-bind="{
                pricePlans: props.pricePlans,
                preview: true,
              }"
            ></component>
            <TableTotal :quote="quote"></TableTotal>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col">
            <p class="small">Terms & Conditions:</p>
        </div>
      </div>
    </div>
  </div>
</template>
