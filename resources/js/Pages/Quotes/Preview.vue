<script setup>
import TableTotal from "@/Components/Quotes/Tables/TableTotal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {  ref } from "vue";
import {  setQuote, quote } from "@composables/useQuote";
import { checkWarnings } from "@composables/warning.js";

const props = defineProps({
  quote: Object,
  pricePlans: Object,
});

setQuote(props.quote);

const solutionsViews = {
  Simple: "TableSimple",
  DealMaker: "TableDealMaker",
};

let component = props.quote.solution_type.configurations?.component;
let view =
  component && Object.keys(solutionsViews).includes(component)
    ? solutionsViews[component]
    : "TableDealMaker";

const showDate = (days = 0) => {
  const dateObj = new Date();
  dateObj.setDate(dateObj.getDate() + days);
  const currentDate =
    dateObj.getDate() + "/" + (dateObj.getMonth() + 1) + "/" + dateObj.getFullYear();
  return currentDate;
};

const warningProviders = ref([
    { its: quote.items.some((item) => item.data?.providerName === "ITS") },
    { pxc: quote.items.some((item) => item.data?.providerName === "PXC") },
  ]);

const items = ref([
  { label: "Home", url: "/dashboard" },
  {
    label: `Quote #${props.quote?.id}`,
    url: props.quote
      ? ["REGISTRY", "SUBMITTED"].includes(props.quote.status)
        ? "#"
        : `/quotes/${props.quote.id}/edit`
      : "#",
  },
  {
    label: `Summary`,
    url: props.quote
      ? ["REGISTRY", "SUBMITTED"].includes(props.quote.status)
        ? "#"
        : `/quotes/${props.quote.id}`
      : "#",
  },
  { label: "Preview PDF" },
]);
</script>

<template>
  <Head :title="'Preview Quote #' + quote.id" />
  <AuthenticatedLayout :quote="quote">
    <div class="row mb-3">
      <div class="col">
        <Header title="Create a quote" :breadcrumbs="items"></Header>
      </div>
      <div class="col" style="text-align: right">
        <a
          @click="checkWarnings(warningProviders, route('quotes.download', { quote: quote.id }))"
          class="btn btn-secondary mx-2"
        >
          <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
          Download PDF
        </a>
      </div>
    </div>
    <QuotePreview v-model="props.quote" :pricePlans="pricePlans"></QuotePreview>
  </AuthenticatedLayout>
</template>
<style scoped>
.w-5 {
  width: 5% !important;
}
.w-10 {
  width: 10% !important;
}
.w-20 {
  width: 20% !important;
}
</style>
