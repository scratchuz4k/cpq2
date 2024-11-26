<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from "vue";

const props = defineProps({
    quote: Object,
});

const items = ref([
    { label: 'Home', url: '/dashboard' },
    { label: `Quote #${props.quote?.id}`, url: props.quote ? `/quotes/${props.quote.id}/edit` : '#' },
    { label: `Summary`, url: props.quote ? `/quotes/${props.quote.id}` : '#' },
    { label: 'Preview PDF' }
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
                <a :href="route('quotes.download', { quote: quote.id })" class="btn btn-secondary mx-2">
                    <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
                    Download PDF
                </a>
            </div>
        </div>
        <QuotePreview v-model="props.quote"></QuotePreview>
    </AuthenticatedLayout>
</template>
