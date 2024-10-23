<script setup>
import { Link } from '@inertiajs/vue3';
import InfoBox from "@/Components/InfoBox.vue";

const props = defineProps({
    order: Object,
})
</script>
<template>
    <div class="row mb-4">
        <div class="col">
            <InfoBox>
                <p class="mb-1">
                    <b>Quote-{{ order?.id }} for {{ order?.account.name }}</b>
                </p>
                <p class="mb-0 fw-medium" v-if="order?.solution_type">
                    <span>
                        Solution Type: {{ order?.solution_type.name }}
                        <Link :href="route('orders.configure', { order: order.id })" class="text-primary">Edit</Link>
                    </span>
                    |
                    <span>
                        Address: {{ order?.address_line1 }} <Link :href="route('orders.configure', { order: order.id })" class="text-primary">Edit</Link>
                    </span>
                </p>
            </InfoBox>
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
