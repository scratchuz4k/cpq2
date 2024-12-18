<script setup>
import { Link } from '@inertiajs/vue3';
import InfoBox from "@/Components/InfoBox.vue";

const props = defineProps({
    quote: Object,
})
</script>
<template>
    <div class="row mb-2">
        <div class="col">
            <InfoBox>
                <p class="mb-1">
                    <b>Quote-{{ quote?.id }} for {{ quote?.account.name }}</b>
                </p>
                <p class="mb-0 fw-medium" v-if="quote?.solution_type">
                    <span>
                        Solution Type: {{ quote?.solution_type.name }}
                    </span>
                    <span class="mx-3">|</span>
                    <span>
                        Address: {{ quote?.address_line1 }} <Link :href="route('quotes.configure', { quote: quote.id })" class="edit-quote-address text-primary">Edit</Link>
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
