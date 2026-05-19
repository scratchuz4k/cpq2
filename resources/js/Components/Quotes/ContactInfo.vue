<script setup>
import { quote } from '@/Composables/useQuote.js';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const contactInfo = computed(() =>
    [
        quote.account?.main_contact?.phone ? `Contact Number: ${quote.account.main_contact.phone}` : null,
        quote.account?.main_contact?.email ? `Email: ${quote.account.main_contact.email}` : null
    ]
        .filter(Boolean)
        .join(' | ')
);
</script>
<template>
    <div class="row my-4">
        <h5>
            {{ `Quote ${quote.id} for ${quote.account?.main_contact?.primary_contact_name ?? quote?.account?.name}` }}
        </h5>
        <p v-if="quote.account?.main_contact" class="fw-medium">
            <span v-if="contactInfo">{{ contactInfo }}</span>
            <template v-if="quote.address_line1">
                <span v-if="contactInfo"> | </span>
                Address: {{ quote.address_line1 }}
                <Link :href="route('quotes.configure', { quote: quote.id })" class="edit-quote-address text-primary">
                    Edit
                </Link>
            </template>
        </p>
    </div>
</template>
