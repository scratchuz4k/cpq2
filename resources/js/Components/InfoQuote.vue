<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    quote: Object,
})
</script>
<template>
    <div class="info-box-primary d-flex mb-4">
        <div class="py-3 px-3">
            <font-awesome-icon :icon="['fas', 'circle-info']" />
        </div>
        <div class="py-3">
            <h4>Customer Details</h4>
            <p class="mb-0">
                <b>Name:</b> {{ quote.account?.name }}<br>

                <template v-if="quote?.address_line1">
                    <b>Address:</b> {{ quote?.address_line1 }}<br>
                </template>
                <template v-if="quote.account?.primary_contact_name">
                    <b>Primary Contact:</b> {{ quote.account?.primary_contact_name }}<br>
                </template>
                <template v-if="quote.account?.email">
                    <b>Email:</b> {{ quote.account?.email }}<br>
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
