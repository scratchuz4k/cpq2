<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {ref} from "vue";

const props = defineProps({
    accounts: Array,
    solution_types: Object,
    order: Object
});

const solutionsViews = {
    3: 'OrdersConectivityTableView',
    4: 'OrdersMobileView',
    5: 'OrdersHardwareView',
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: props.order ? `Quote #${props.order?.id}` : 'Create a quote'},
]);
</script>

<template>

    <Head :title="'Quote #' + order.id" />
    <AuthenticatedLayout :order="order">
        <Header title="Create a quote" :breadcrumbs="items"></Header>
        <component :is="solutionsViews[order.solution_type.code]" v-bind="{
            modelValue: order
        }"></component>
    </AuthenticatedLayout>
</template>
