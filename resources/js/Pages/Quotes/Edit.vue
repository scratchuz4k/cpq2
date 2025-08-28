<script setup>
import { getQuote } from '@/Composables/useQuote';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    quote: Object
});

const solutionsViews = {
    3: 'QuotesConnectivityTableView',
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
                modelValue: quote
            }"
        ></component>
        <div v-else class="py-3">
            <p class="text-center font-bold">Solution not implemented yet</p>
            <p class="text-center">Come back later</p>
        </div>
    </AuthenticatedLayout>
</template>
