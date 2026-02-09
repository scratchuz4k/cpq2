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
                    :class="btn.props.class"
                    :type="btn.props.type"
                    :disabled="btn.props.disabled"
                    @click="btn.onClick"
                >
                    <font-awesome-icon
                        v-if="btn.icon && btn.iconPosition === 'left'"
                        :icon="btn.icon"
                        :class="btn.iconClass"
                        class="me-2"
                    />
                    {{ btn.label }}
                    <font-awesome-icon
                        v-if="btn.icon && btn.iconPosition === 'right'"
                        :icon="btn.icon"
                        :class="btn.iconClass"
                        class="ms-2"
                    />
                </button>
            </template>
        </template>
    </AuthenticatedLayout>
</template>
