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
    { name: 'its', is_present: quote.items.some((item) => item.data?.providerName === 'ITS') },
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
                    <div class="preview-logo">
                        <app-logo></app-logo>
                    </div>
                </div>
                <div class="col text-end" id="header_right">
                    <p>{{ quote.account?.name }}<br />{{ quote.address_data?.label }}</p>
                    <p>Tel: +44 (0) 191 300 700</p>
                    <p>Email: sales@example.com</p>
                </div>
            </div>
            <div class="d-flex">
                <h1 class="mb-1 w-auto">Quote Q-{{ quote.id }}</h1>
                <span
                    v-if="quote.status === 'EXPIRED'"
                    class="badge border border-secondary text-secondary opacity-75 ms-4 lh-sm my-auto"
                >
                    {{ quote.status }}
                </span>
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
            <TableTotal :isSimpleView="true"></TableTotal>
        </div>
    </div>
</template>
