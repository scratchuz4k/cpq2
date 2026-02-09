<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    quote: Object,
    pricePlans: Object
});

const solutionsViews = {
    Simple: 'QuotesTotalSimpleView',
    DealMaker: 'QuotesTotalDealMakerView'
};

let component = props.quote.solution_type.configurations?.component;
let view =
    component && Object.keys(solutionsViews).includes(component)
        ? solutionsViews[component]
        : 'QuotesTotalDealMakerView';
</script>

<template>
    <Head :title="'Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote" :show-step-action-bar="true">
        <component
            :is="view"
            v-bind="{
                modelValue: quote,
                pricePlans: props.pricePlans,
                preview: false
            }"
        ></component>
        <template #actionBar>
            <a
                v-if="!quote.is_blocked"
                id="btn-previous"
                class="btn text-primary me-auto"
                :href="route('quotes.edit', { quote: quote.id })"
            >
                <font-awesome-icon class="me-2" :icon="['fas', 'arrow-left']"></font-awesome-icon>
                Edit Quote
            </a>
            <!--        </template>-->
            <!--        <template #actionBarRight>-->
            <QuoteApprovals></QuoteApprovals>
            <ActivityDialog
                :filters="[
                    { entity: 'Quote', values: quote.id },
                    {
                        entity: 'QuoteItem',
                        values:
                            quote.items.map((x) => x.id).join(',') +
                            ',' +
                            quote.items_trashed.map((x) => x.id).join(',')
                    },
                    {
                        entity: 'QuoteDiscount',
                        values: quote.discounts.map((x) => x.id).join(',')
                    }
                ]"
            ></ActivityDialog>
            <ShareableQuote :disabled="quote.items.some((x) => !x.is_margin_valid)"></ShareableQuote>
            <a
                class="btn btn-outline-primary me-2"
                :href="route('quotes.preview', { quote: quote.id })"
                v-if="!quote.items.some((x) => !x.is_margin_valid)"
            >
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview
            </a>
            <button class="btn btn-outline-primary me-2" v-else disabled="true">
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview
            </button>
            <button hidden class="btn btn-outline-primary me-2" :disabled="quote.items.some((x) => !x.is_margin_valid)">
                <font-awesome-icon class="me-2" :icon="['fas', 'envelope']"> </font-awesome-icon>
                Email this quote
            </button>
        </template>
    </AuthenticatedLayout>
</template>

<style>
.form-control {
    font-size: 0.9rem;
    line-height: 1.5rem;
}
</style>
