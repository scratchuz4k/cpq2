<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
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
        <div class="row mb-3">
            <div class="col d-flex">
                <div class="me-3">
                    <font-awesome-icon class="fa-border" :icon="['fas', 'file-lines']"
                        style="font-size:30px;color:var(--secondary-light);border-color:var(--secondary-light);border-width:4px;border-radius:12px" />
                </div>
                <div style="padding-top:1px">
                    <p class="m-0">Q-{{ order.id }} // <span id="account_name">
                            {{ order.account?.name }}
                        </span>
                    </p>
                    <h1 class="m-0" style="font-size:18px;font-weight:bold">Quote</h1>
                </div>
            </div>
            <div class="col" style="text-align: right">
                <a :href="route('orders.download', { order: order.id })" class="btn btn-secondary mx-2">
                    <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
                    Download PDF
                </a>
                <button class="btn btn-primary">Email this quote
                    <font-awesome-icon :icon="['fas', 'arrow-right']"></font-awesome-icon></button>
            </div>
        </div>

        <component :is="solutionsViews[order.solution_type.code]" v-bind="{
            modelValue: order
        }"></component>

    </AuthenticatedLayout>
</template>
