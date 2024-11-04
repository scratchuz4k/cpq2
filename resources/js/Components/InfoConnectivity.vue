<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    quote: Object,
})
</script>
<template>
    <div class="row mb-4">
        <div class="col">
            <div class="price_header d-flex justify-content-between">
                <div>
                    <p class="mb-0">
                        <b>Solution Type:</b> {{ quote?.solution_type.name }}
                        <Link :href="route('quotes.configure', { quote: quote.id })" class="btn btn-sm btn-secondary py-0">Edit</Link>
                    </p>
                    <p class="mb-0" v-if="quote?.address_line1">
                        <b>Address:</b> {{ quote?.address_line1 }}
                        <Link :href="route('quotes.configure', { quote: quote.id })" class="btn btn-sm btn-secondary py-0">Edit address</Link>
                    </p>
                </div>
            </div>
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
