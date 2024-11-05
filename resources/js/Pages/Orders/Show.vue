<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {ref} from "vue";
import InfoConnectivity from "@/Components/InfoConnectivity.vue";

const props = defineProps({
    order: Object,
    modelValue: Object
});

const solutionsViews = {
    3: 'OrdersConectivityTotalView',
    4: 'OrdersMobileTotalView',
    5: 'OrdersHardwareTotalView',
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Create a quote' },
]);
</script>

<template>

    <Head :title="'Quote #' + order.id" />
    <AuthenticatedLayout :order="order">
        <component :is="solutionsViews[order.solution_type.code]" v-bind="{
            modelValue: order,
            preview: false
        }"></component>
    </AuthenticatedLayout>
</template>
