<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from "vue";
import InfoConnectivity from "@/Components/InfoConnectivity.vue";

const props = defineProps({
    quote: Object,
    pricePlans: Object,
    modelValue: Object
});

const solutionsViews = {
    3: 'QuotesMobileTotalView',
    4: 'QuotesMobileTotalView',
    5: 'QuotesHardwareTotalView',
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Create a quote' },
]);
</script>

<template>

    <Head :title="'Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote">
        <component :is="solutionsViews[quote.solution_type.code]" v-bind="{
            modelValue: quote,
            pricePlans: props.pricePlans,
            preview: false
        }"></component>
    </AuthenticatedLayout>
</template>
