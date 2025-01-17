<script setup>
import { setQuote, quote } from "@composables/useQuote";
const props = defineProps({
  modelValue: Object,
  pricePlans: Object,
});

setQuote(props.modelValue);

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
          <div style="width: 150px">
            <app-logo></app-logo>
          </div>
        </div>
        <div class="col text-end" id="header_right">
          <p>{{ quote.account?.name }}<br />{{ quote.address_data?.label }}</p>
          <p>Tel: +44 (0) 191 300 700</p>
          <p>Email: sales@example.com</p>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <h1 class="mb-3">Quote Q-{{ quote.id }}</h1>
          <div class="row mb-3">
            <div class="col">
              <p>
                Company address, city, state, post code<br />
                sales@example.com<br />
                +44 (0) 123 456 7890
              </p>
            </div>
            <div class="col">
              <p>
                Quote #{{ quote.id }}<br />
                Quotation Date: {{ showDate() }}<br />
                Valid Date: {{ showDate(7) }}
              </p>
            </div>
          </div>
          <div class="row mb-5">
            <div class="col">
              <p class="big bold">BILL FROM</p>
              <p>
                Name Surname<br />
                Company address, city, state, post code<br />
                sales@example.com<br />
                +44 (0) 123 456 7890
              </p>
            </div>
            <div class="col">
              <p class="big bold">BILL TO</p>
              <p>
                Name Surname<br />
                Company address, city, state, post code<br />
                contact@account.test<br />
                +44 (0) 234 567 8910
              </p>
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
      <div class="row">
        <div class="col">
          <p>Terms & Conditions:</p>
          <p>Lorem ipsum dolor sit amet...</p>
        </div>
      </div>
    </div>
  </div>
</template>
