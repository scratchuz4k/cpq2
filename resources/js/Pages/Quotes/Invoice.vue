<script setup>
import TableTotal from "@/Components/Quotes/Tables/TableTotal.vue";
import { onMounted, ref } from "vue";
import { setQuote, quote } from "@composables/useQuote";

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

onMounted(() => {
  window.download();
});

const items = ref([
  { label: "Home", url: "/dashboard" },
  {
    label: `Quote #${props.quote?.id}`,
    url: props.quote
      ? ["REGISTRY", "ORDERED"].includes(props.quote.status)
        ? "#"
        : `/quotes/${props.quote.id}/edit`
      : "#",
  },
  {
    label: `Summary`,
    url: props.quote
      ? ["REGISTRY", "ORDERED"].includes(props.quote.status)
        ? "#"
        : `/quotes/${props.quote.id}`
      : "#",
  },
  { label: "Preview PDF" },
]);
</script>

<template>
  <!-- <Head :title="'Preview Quote #' + quote.id" /> -->
  <div class="container-fluid">
    <div class="row" id="header">
      <div class="col" id="header_left">
        <img
          src="https://img.logoipsum.com/331.svg"
          alt="Sample Logo"
          class="img-fluid"
        />
        <p>Insert Tagline</p>
      </div>
      <div class="col text-end" id="header_right">
        <p>{{ quote.account?.name }}<br />{{ quote.address_data?.label }}</p>
        <p>Tel: +44 (0) 191 300 700</p>
        <p>Email: sales@example.com</p>
      </div>
    </div>
    <div class="cpq-summary row px-3" id="main">
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
        <div class="row mb-4">
          <div class="col">
            <h2>BILL FROM</h2>
            <p>
              Name Surname<br />
              Company address, city, state, post code<br />
              sales@example.com<br />
              +44 (0) 123 456 7890
            </p>
          </div>
          <div class="col">
            <h2>BILL TO</h2>
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
          <div class="row justify-content-end" style="min-height: 50vh">
            <div class="col-6">
              <TableTotal :quote="quote"></TableTotal>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row px-3 mb-4" id="terms">
      <div class="col">
        <p>Terms & Conditions:</p>
        <p>Lorem ipsum dolor sit amet...</p>
      </div>
    </div>
    <div class="row mb-2" id="footer">
      <div class="col">
        <img src="https://img.logoipsum.com/331.svg" />
        <p>Insert Tagline</p>
      </div>
      <div class="col text-end">
        <p>Lorem ipsum dolor sit amet...</p>
      </div>
    </div>
  </div>
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
.badge.is-contended {
  font-size: 10px;
}
</style>
