<script setup>
import { getQuote } from '@/Composables/useQuote';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import QuotesDefaultView from '@/Views/Quotes/Edit/QuotesDefaultView.vue';

const props = defineProps({
    quote: Object
});

const solutionsViews = {
    3: 'QuotesConnectivityView',
    4: 'QuotesMobileView',
    5: 'QuotesHardwareView'
};

getQuote(props.quote.id);

const items = ref([
    { label: 'Home', url: '/' },
    { label: props.quote ? `Quote #${props.quote?.id}` : 'Create a quote' }
]);
</script>

<template>
    <Head :title="'Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote">
        <Header title="Create a quote" :breadcrumbs="items"></Header>
        <component
            v-if="Object.keys(solutionsViews).includes(quote.solution_type.code)"
            :is="solutionsViews[quote.solution_type.code]"
            v-bind="{
                modelValue: props.quote
            }"
        ></component>
        <QuotesDefaultView v-else v-bind="{ modelValue: props.quote }"> </QuotesDefaultView>
    </AuthenticatedLayout>
</template>
