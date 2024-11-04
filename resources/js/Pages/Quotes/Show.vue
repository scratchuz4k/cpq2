<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    quote: Object,
    modelValue: Object
});

const solutionsViews = {
    3: 'QuotesConnectivityTotalView',
    4: 'QuotesMobileTotalView',
    5: 'QuotesHardwareTotalView',
}
</script>

<template>

    <Head :title="'Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote">
        <component :is="solutionsViews[quote.solution_type.code]" v-bind="{
            modelValue: quote,
            preview: false
        }"></component>
    </AuthenticatedLayout>
</template>
