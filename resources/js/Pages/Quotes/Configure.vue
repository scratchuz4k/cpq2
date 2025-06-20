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
    account: props.quote.account ?? null,
    solution_type:  props.quote.solution_type ?? null,
    address: props.quote.address_data ?? null,
    postCode: props.quote.address_postcode ?? null,
    position: props.quote.address_position ?? null,
    solutionCheck: true
})

const submit = () => {
    try {
        router.put(`/quotes/${props.quote.id}`,
            {
                address_line1: form.address?.label,
                address_postcode: form.address?.postCode,
                solution_type_id: form.solution_type?.id,
                address_position: form.position,
                address_city: form.address?.postTown,
                address_data: form.address,
            })
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const items = ref([
    { label: 'Home', url: '/dashboard' },
    { label: `Quote #${props.quote?.id}`},
]);

</script>

<template>
    <AuthenticatedLayout :quote="quote">
        <form @submit.prevent="submit">
            <div class="row mb-3">
                <div class="col">
                    <Header title="Edit quote" :breadcrumbs="items"></Header>
                    <h5 class="font-bold my-3">Select an account to start a quote</h5>
                </div>
            </div>
            <QuotesSolutionView v-model="form" :solution_types="solution_types" :is_updating_quote="true"></QuotesSolutionView>
            <div class="row mb-3 ">
                <div class="col-lg-6 d-flex justify-content-end">
                    <button id="btn-create-quote" class="btn btn-primary" type="submit" :disabled="!form.address" @click="submit()">
                        Get prices
                        <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                        <div v-if="form.isLoading" class="spinner-border  spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
