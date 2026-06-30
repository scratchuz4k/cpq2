<script setup>
import { ref } from 'vue';
import { setQuote, quote } from '@composables/useQuote';
import WarningNote from '@/Components/WarningNote.vue';
import format from '@/Composables/format.js';
import { usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const page = usePage();
const tenant = page.props.tenant;

const props = defineProps({
    modelValue: Object,
    pricePlans: Object
});

setQuote(props.modelValue);

const warningProviders = ref([
    { name: 'pxc', is_present: quote.items.some((item) => item.data?.providerName === 'PXC') }
]);

const showDate = (days = 0) => {
    const dateObj = new Date();
    dateObj.setDate(dateObj.getDate() + days);
    const currentDate = dateObj.getDate() + '/' + (dateObj.getMonth() + 1) + '/' + dateObj.getFullYear();
    return currentDate;
};

const solutionsViews = {
    Simple: 'TableSimple',
    DealMaker: 'TableDealMaker'
};

let component = props.modelValue.solution_type.configurations?.component;
let view = component && Object.keys(solutionsViews).includes(component) ? solutionsViews[component] : 'TableDealMaker';
</script>
<template>
    <div class="panel d-flex justify-content-center">
        <div id="print" class="page">
            <div class="row">
                <div class="col" id="header_left">
                    <AppLogo type="logo_docs" :class="'preview-logo'"></AppLogo>
                </div>
                <div class="col text-end" id="header_right">
                    <p>
                        {{ quote.account?.main_contact?.primary_contact_name ?? quote.account?.name }}
                        <br />
                        {{ quote.address_data?.label }}
                    </p>
                    <p v-if="quote.account?.main_contact?.phone">Tel: {{ quote.account.main_contact.phone }}</p>
                    <p v-if="quote.account?.main_contact?.email">Email: {{ quote.account.main_contact.email }}</p>
                </div>
            </div>
            <div class="mt-4 d-flex">
                <h1 class="mb-1 w-auto">Quote #{{ quote.id }}</h1>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <p>
                        Quotation Date: {{ dayjs(quote.created_at).format('DD/MM/YYYY') }}<br />
                        Valid Date:
                        {{ format.expireDate(quote.created_at, tenant.configurations?.quote_expiration_days) }}
                    </p>
                </div>
            </div>
            <div v-if="warningProviders.some((obj) => Object.values(obj).includes(true))">
                <div class="row">
                    <div class="col">
                        <InfoBox class="mb-4 p-0" severity="error">
                            <WarningNote :warnings="warningProviders" />
                        </InfoBox>
                    </div>
                </div>
            </div>
            <TableCustom
                v-if="quote.solution_type.configurations?.has_custom_table == 1"
                :preview="true"
                :blockedColumns="['margin']"
            ></TableCustom>
            <component
                v-else
                :is="view"
                v-bind="{
                    pricePlans: props.pricePlans,
                    preview: true
                }"
            ></component>
        </div>
    </div>
</template>
