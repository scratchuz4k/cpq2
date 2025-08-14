<script setup>
import { reactive, ref } from 'vue';
import WarningNote from '@/Components/WarningNote.vue';
import { setOrder, order } from '@composables/useOrders';

const props = defineProps({
    modelValue: Object,
    pricePlans: Object
});

setOrder(props.modelValue);

const warningProviders = ref([
    { its: order.items.some((item) => item.data?.providerName === 'ITS') },
    { pxc: order.items.some((item) => item.data?.providerName === 'PXC') }
]);

const showDate = (days = 0) => {
    const dateObj = new Date();
    dateObj.setDate(dateObj.getDate() + days);
    const currentDate = dateObj.getDate() + '/' + (dateObj.getMonth() + 1) + '/' + dateObj.getFullYear();
    return currentDate;
};
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
                    <p>{{ order.account?.name }}<br />{{ order.address_data?.label }}</p>
                    <p>Tel: +44 (0) 191 300 700</p>
                    <p>Email: sales@example.com</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col">
                    <h1 class="mb-1">Order Q-{{ order.id }}</h1>
                    <div class="row mb-3">
                        <div class="col">
                            <p>Quotation Date: {{ showDate() }}<br /></p>
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
                    <div>
                        <TableSimpleOrders
                            v-bind="{
                                pricePlans: props.pricePlans,
                                preview: true
                            }"
                        >
                        </TableSimpleOrders>
                        <TableTotalOrders :order="order"></TableTotalOrders>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
