<script setup>
import ShareLayout from "@/Layouts/ShareLayout.vue";
import { Head } from "@inertiajs/vue3";
const props = defineProps({
  model: Object,
  pricePlans: Object,
});
</script>
<template>
  <Head title="Share" />
  <ShareLayout>
    <div class="row mb-3">
      <div class="col"></div>
      <div class="col" style="text-align: right">
        <a
          :href="route('quotes.download', { quote: model.id })"
          class="btn btn-secondary mx-2"
        >
          <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
          Download PDF
        </a>
      </div>
    </div>
    <QuotePreview v-model="props.model" :pricePlans="pricePlans"></QuotePreview>
  </ShareLayout>
</template>
