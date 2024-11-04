<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    accounts: Array,
    solution_types: Object,
    quote: Object
});

const solutionsViews = {
    3: 'QuotesConnectivityTableView',
    4: 'QuotesMobileView',
    5: 'QuotesHardwareView',
}
</script>

<template>

    <Head :title="'Quote #' + quote.id" />
    <AuthenticatedLayout :quote="quote">
        <!-- <div class="row mb-3">
            <div class="col d-flex">
                <div class="me-3">
                    <font-awesome-icon class="fa-border" :icon="['fas', 'fa-wrench']"
                        style="font-size:30px;color:var(--secondary-light);border-color:var(--secondary-light);border-width:4px;border-radius:12px" />
                </div>
                <div style="padding-top:1px">
                    <p class="m-0">Q-{{ quote.id }} // <span id="account_name">
                            {{ quote.account?.name }}
                        </span>
                    </p>
                    <h1 class="m-0" style="font-size:18px;font-weight:bold">{{ quote.solution_type.code == 5 ?
                        'Configure Hardware' : 'Price' }}</h1>
                </div>
            </div>
            <div class="col d-flex justify-content-end">
                <div>
                    <a class="btn btn-primary" type="submit" :href="route('quotes.show', { quote: quote.id })">
                        Save Quote
                    </a>
                </div>
            </div>
        </div> -->

        <component :is="solutionsViews[quote.solution_type.code]" v-bind="{
            modelValue: quote
        }"></component>
    </AuthenticatedLayout>
</template>
