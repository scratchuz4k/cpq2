<script setup>
import { proceedOrder, rejectOrder } from '@/Composables/useOrders.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { setOrder, order } from '@composables/useOrders';

const page = usePage();
const props = defineProps({
    order: Object,
    pricePlans: Object
});

setOrder(props.order);

const showDate = (days = 0) => {
    const dateObj = new Date();
    dateObj.setDate(dateObj.getDate() + days);
    const currentDate = dateObj.getDate() + '/' + (dateObj.getMonth() + 1) + '/' + dateObj.getFullYear();
    return currentDate;
};

const orderTotal = (key) => {
    return order.items
        .filter((x) => x.price_plan && x.price_plan.code == key)
        .reduce((acc, item) => (acc += item.total), 0);
};

const items = ref([
    { label: 'Home', url: '/dashboard' },
    { label: `Orders`, url: '/orders' },
    { label: `Order #${order?.id} Preview` }
]);
</script>

<template>
    <Head :title="'Preview Order #' + order.id" />
    <AuthenticatedLayout :order="order">
        <div class="row mb-3">
            <div class="col">
                <Header title="Preview Order" :breadcrumbs="items"></Header>
            </div>
            <div class="col" style="text-align: right">
                <template v-if="order.status !== 'REGISTRY'">
                    <div class="badge text-bg-dark h2">
                        {{ order.status }}
                    </div>
                </template>
                <template v-else>
                    <button
                        id="reject-order-btn"
                        v-if="order.user_id === page.props.auth.user.id || order.user_id === null"
                        class="btn btn-secondary mx-2"
                        @click="rejectOrder(order)"
                    >
                        <font-awesome-icon :icon="['fas', 'x']"></font-awesome-icon>
                        Reject
                    </button>
                    <button
                        id="proceed-order-btn"
                        v-if="order.user_id === page.props.auth.user.id || order.user_id === null"
                        class="btn btn-primary mx-2"
                        @click="proceedOrder(order)"
                    >
                        <font-awesome-icon :icon="['fas', 'check']"></font-awesome-icon>
                        Proceed
                    </button>
                </template>
            </div>
        </div>
        <OrderPreview v-model="order" :pricePlans="pricePlans"></OrderPreview>
    </AuthenticatedLayout>
</template>
<style scoped>
.w-5 {
    width: 5% !important;
}

.w-10 {
    width: 10% !important;
}

.w-20 {
    width: 20% !important;
}
</style>
