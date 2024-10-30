<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {ref} from "vue";
import InfoConnectivity from "@/Components/InfoConnectivity.vue";

const props = defineProps({
    order: Object,
    modelValue: Object
});

const solutionsViews = {
    3: 'OrdersConectivityTotalView',
    4: 'OrdersMobileTotalView',
    5: 'OrdersHardwareTotalView',
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Create a quote' },
]);
</script>

<template>

    <Head :title="'Quote #' + order.id" />
    <AuthenticatedLayout :order="order">
        <Header title="Create a quote" :breadcrumbs="items"></Header>
        <template v-if="order.solution_type.code==3">
            <InfoConnectivity :order="order"></InfoConnectivity>

            <div class="d-flex justify-content-between align-items-center mb-5">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="fs-4 me-3 mb-0"><b>Your quote</b></p>
                    <p class="mb-0 text-primary bold fs-5 pt-1">STEP 2/2</p>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <a class="btn btn-outline-primary me-2" type="submit" :href="route('orders.preview', { order: order.id })">
                        <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                        Preview PDF
                    </a>
                    <a class="btn btn-primary" type="submit" >
                        Save and close
                    </a>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <component :is="solutionsViews[order.solution_type.code]" v-bind="{
                        modelValue: order,
                        preview: false
                    }"></component>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <div class="d-flex justify-content-between align-items-center">
                    <a class="btn btn-outline-primary me-2" type="submit" :href="route('orders.preview', { order: order.id })">
                        <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                        Preview PDF
                    </a>
                    <a class="btn btn-primary" type="submit" href="#">
                        Save and close
                    </a>
                </div>
            </div>
        </template>
        <template v-else>
            <component :is="solutionsViews[order.solution_type.code]" v-bind="{
                modelValue: order,
                preview: false
            }"></component>
        </template>
    </AuthenticatedLayout>
</template>
