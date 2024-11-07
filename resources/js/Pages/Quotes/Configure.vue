<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {reactive, ref} from 'vue'


const props = defineProps({
    quote: Object,
    accounts: Array,
    solution_types: Object,
});

const form = reactive({
    isLoading: false,
    account: props.order.account ?? null,
    solution_type:  props.order.solution_type ?? null,
    address: null,
    postCode: null,
    position: null,
    solutionCheck: true
})

const submit = () => {
    try {
        router.put(`/quotes/${props.quote.id}`,
            {
                address_line1: form.address?.label,
                address_postcode: form.address?.addressparts?.PostCode,
                solution_type_id: form.solution_type?.id,
                address_position: form.position,
                address_city: form.address?.addressparts?.PostTown,
                address_data: form.address,
            })
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const items = ref([
    { label: 'Home', url: '/dashboard' },
    { label: 'Edit a quote' },
]);

</script>

<template>
    <AuthenticatedLayout :quote="quote">
        <form @submit.prevent="submit">
            <div class="row mb-3">
                <div class="col">
                    <Header title="Create a quote" :breadcrumbs="items"></Header>
                    <h5 class="font-bold my-3">Select an account to start a quote</h5>
                </div>
                <div class="col " style="text-align: right">
                    <button class="btn btn-primary" :disabled="form.solutionCheck" type="submit" @click="submit()">
                        Save Quote
                        <div v-if="form.isLoading" class="spinner-border  spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                </div>
            </div>
            <QuotesSolutionView v-model="form" :solution_types="solution_types"></QuotesSolutionView>
        </form>
    </AuthenticatedLayout>
</template>
