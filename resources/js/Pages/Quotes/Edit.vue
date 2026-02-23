<script setup>
import { getQuote } from '@/Composables/useQuote';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import QuotesDefaultView from '@/Views/Quotes/Edit/QuotesDefaultView.vue';
import { actionBarButtons } from '@/Composables/useQuoteSteps.js';

const props = defineProps({
    quote: Object
});

const solutionsViews = {
    3: 'QuotesConnectivityView',
    4: 'QuotesMobileView',
    5: 'QuotesHardwareView',
    6: 'QuotesCloudAuditView'
};

getQuote(props.quote.id);

const items = ref([
    { label: 'Home', url: '/' },
    { label: props.quote ? `Quote #${props.quote?.id}` : 'Create a quote' }
]);
</script>

<template>
    <Head :title="'Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote" :show-step-action-bar="true">
        <Header title="Create a quote" :breadcrumbs="items"></Header>
        <component
            v-if="Object.keys(solutionsViews).includes(quote.solution_type.code)"
            :is="solutionsViews[quote.solution_type.code]"
            v-bind="{
                modelValue: props.quote
            }"
        ></component>
        <QuotesDefaultView v-else v-bind="{ modelValue: props.quote }"> </QuotesDefaultView>
        <template #actionBar>
            <template v-if="actionBarButtons.length" v-for="btn in actionBarButtons" :key="btn.label">
                <button
                    v-if="btn.btn === 'back'"
                    :disabled="btn.disabled ?? false"
                    :id="btn.id ?? 'btn-step-previous'"
                    class="btn btn-outline-dark step-nav me-3 me-auto"
                    type="button"
                    @click="btn.onClick"
                >
                    <font-awesome-icon :icon="['fas', 'arrow-left']" />
                    {{ btn.label ?? 'Previous' }}
                </button>

                <button
                    v-else-if="btn.btn === 'next'"
                    :disabled="btn.disabled"
                    :id="btn.id ?? 'btn-step-next'"
                    class="ms-3 btn btn-primary step-nav ms-auto"
                    type="button"
                    @click="btn.onClick"
                >
                    {{ btn.label ?? 'Next' }}
                    <font-awesome-icon v-if="btn.icon" :icon="btn.icon" :class="btn.iconClass" class="ms-2" />
                    <font-awesome-icon v-else :icon="['fas', 'arrow-right']" />
                </button>
            </template>
        </template>
    </AuthenticatedLayout>
</template>
