<script setup>
import { quote } from '@/Composables/useQuote.js';
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import format from '@/Composables/format.js';

const page = usePage();
const quote_expiration_days = page.props.tenant.configurations?.quote_expiration_days;

const props = defineProps({
    showExpireData: {
        required: false,
        default: false
    }
});

const contactInfo = computed(() =>
    [
        quote.account?.main_contact?.phone ? `Contact Number: ${quote.account.main_contact.phone}` : null,
        quote.account?.main_contact?.email ? `Email: ${quote.account.main_contact.email}` : null,
        props.showExpireData
            ? `This quote will expire on: ${format.expireDate(quote.created_at, quote_expiration_days)}`
            : null
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
