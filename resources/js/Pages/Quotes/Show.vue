<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  quote: Object,
  pricePlans: Object,
});

const solutionsViews = {
  Simple: "QuotesTotalSimpleView",
  DealMaker: "QuotesTotalDealMakerView",
};

let component = props.quote.solution_type.configurations?.component;
let view =
  component && Object.keys(solutionsViews).includes(component)
    ? solutionsViews[component]
    : "QuotesTotalDealMakerView";
</script>

<template>
  <Head :title="'Quote #' + quote.id" />
  <AuthenticatedLayout :quote="quote">
    <component
      :is="view"
      v-bind="{
        modelValue: quote,
        pricePlans: props.pricePlans,
        preview: false,
      }"
    ></component>
  </AuthenticatedLayout>
</template>

<style >
.form-control {
  text-align: center;
  font-size: 0.9rem;
  line-height: 1.5rem;
}
</style>
