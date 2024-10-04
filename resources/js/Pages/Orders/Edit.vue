<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    accounts: Array,
    solution_types: Object,
    order: Object
});

const solutionsViews = {
    3: 'OrdersConectivityTableView',
    4: 'OrdersMobileView',
    5: 'OrdersHardwareView',
}
</script>

<template>

    <Head :title="'Quote #' + order.id" />
    <AuthenticatedLayout  :order="order">
        <div class="row mb-3">
            <div class="col d-flex">
                <div class="me-3">
                    <font-awesome-icon class="fa-border" :icon="['fas', 'fa-wrench']"
                        style="font-size:30px;color:var(--secondary-light);border-color:var(--secondary-light);border-width:4px;border-radius:12px" />
                </div>
                <div style="padding-top:1px">
                    <p class="m-0">Q-{{ order.id }} // <span id="account_name">
                            {{ order.account?.name }}
                        </span>
                    </p>
                    <h1 class="m-0" style="font-size:18px;font-weight:bold">{{ order.solution_type.code == 5 ? 'Configure Hardware' : 'Price'}}</h1>
                </div>
            </div>
        </div>

        <component :is="solutionsViews[order.solution_type.code]" v-bind="{
            modelValue: order
        }"></component>
    </AuthenticatedLayout>
</template>
