<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
})
</script>
<template>
    <div class="info-box d-flex mb-4">
        <div class="py-3 px-3">
            <font-awesome-icon :icon="['fas', 'circle-info']" />
        </div>
        <div class="py-3">
            <h4>Customer Details</h4>
            <p class="mb-0">
                <b>Name:</b> {{ order.account?.name }}<br>

                <template v-if="order?.address_line1">
                    <b>Address:</b> {{ order?.address_line1 }}<br>
                </template>
                <template v-if="order.account?.primary_contact_name">
                    <b>Primary Contact:</b> {{ order.account?.primary_contact_name }}<br>
                </template>
                <template v-if="order.account?.email">
                    <b>Email:</b> {{ order.account?.email }}<br>
                </template>
            </p>
        </div>
    </div>
</template>
<style>
.price_header {
    background: var(--tertiary-light);
    border-radius: var(--radius);
    padding: 15px;
}
</style>