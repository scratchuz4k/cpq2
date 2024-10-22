<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
    modelValue: Object
});

const solutionsViews = {
    3: 'OrdersConectivityTotalView',
    4: 'OrdersMobileTotalView',
    5: 'OrdersHardwareTotalView',
}
</script>

<template>

    <Head :title="'Quote #' + order.id" />
    <AuthenticatedLayout :order="order">
        <OrdersMobileTotalView v-bind="{
            modelValue: order,
            preview: false
        }">
        </OrdersMobileTotalView>
        <!-- <component :is="solutionsViews[order.solution_type.code]" ></component> -->
    </AuthenticatedLayout>
</template>
