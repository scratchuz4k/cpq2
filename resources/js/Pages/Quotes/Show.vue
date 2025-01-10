<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from "vue";

const props = defineProps({
    quote: Object,
    pricePlans: Object,
    modelValue: Object
});

const solutionsViews = {
    3: 'QuotesDealMakerView', //QuotesConnectivityTotalView
    4: 'QuotesDealMakerView',
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
