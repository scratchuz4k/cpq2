<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue'
import QuotesSolutionView from "@/Views/Quotes/QuotesSolutionView.vue";


defineProps({
    accounts: Array,
    solution_types: Object,
});

const form = reactive({
    isLoading: false,
    account: null,
    solution_type: null,
    address: null,
    postCode: null,
    position: null,
    solutionCheck: true
})

const submit = () => {
    try {
        router.post('/quotes',
            {
                account_id: form.account?.id,
                address_position: form.position,
                address_city: form.address?.postTown,
                address_data: form.address,
                address_line1: form.address?.label,
                address_postcode: form.address?.postCode,
                solution_type_id: form.solution_type.id
            })
    } catch (ex) {
        alert(ex.response.data.message)
    }
}
const items = ref([
    { label: 'Home', url: '/dashboard' },
    { label: 'Create a quote' },
]);


</script>

<template>

    <Head title="Create a quote" />
    <AuthenticatedLayout>

        <Header title="Create a quote" :breadcrumbs="items"></Header>
        <h5 class="font-bold my-3">Select an account to start a quote</h5>

        <form @submit.prevent="submit">
            <div class="row mb-3">
                <div class="col-6">
                    <div class="panel mb-3 ">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="font-bold mb-3">Select an account to start a quote</h5>
                                <div class="mb-3">
                                    <label for="account_id" class="form-label">Account <span
                                            class="text-primary fw-bold">*</span></label>
                                    <InputSelect id="account_id" v-model="form.account" url="/accounts"
                                        :fields="['name', 'reference']" placeholder="Select an Account">
                                    </InputSelect>
                                </div>
                                or create a <a id="link-to-create-account" href="/accounts/create"
                                    class="text-primary">new account</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template v-if="form.account">
                <QuotesSolutionView v-model="form" :solution_types="solution_types"></QuotesSolutionView>
                <div class="row mb-3 ">
                    <div class="col-6 d-flex justify-content-end">
                        <button id="btn-create-quote" class="btn btn-primary" type="submit"
                            :disabled="form.solutionCheck">
                            Get prices
                            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                            <div v-if="form.isLoading" class="spinner-border  spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </div>
            </template>

        </form>
    </AuthenticatedLayout>
</template>
