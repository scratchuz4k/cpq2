<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    quote: Object,
});

const solutionsViews = {
    3: "QuotesConnectivityTableView",
    4: "QuotesMobileView",
    5: "QuotesHardwareView",
};

const items = ref([
    { label: "Home", url: "/" },
    { label: props.quote ? `Quote #${props.quote?.id}` : "Create a quote" },
]);
</script>

<template>

    <Head :title="'Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote">
        <Header title="Create a quote" :breadcrumbs="items"></Header>
        <component :is="solutionsViews[quote.solution_type.code]" v-bind="{
            modelValue: quote,
        }"></component>
    </AuthenticatedLayout>
</template>
