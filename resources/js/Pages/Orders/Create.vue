<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue'


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
        router.post('/orders',
            {
                account_id: form.account?.id,
                address_position: form.position,
                address_city: form.address?.addressparts?.PostTown,
                address_data: form.address,
                address_line1: form.address?.label,
                address_postcode: form.address?.addressparts?.PostCode,
                solution_type_id: form.solution_type.id
            })
    } catch (ex) {
        alert(ex.response.data.message)
    }
}
const items = ref([
    { label: 'Home', url: '/dashboard' },
    { label: 'Create a Quote' },
]);


</script>

<template>

    <Head title="Create a Quote" />
    <AuthenticatedLayout>

        <Header title="Create a Quote" :breadcrumbs="items"></Header>
        <h5 class="font-bold mb-3">Start a Quote</h5>

        <form @submit.prevent="submit">
            <div class="row mb-3">
                <div class="col-6">
                    <div class="panel mb-3 ">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="font-bold mb-3">Select an account</h5>
                                <div class="mb-3">
                                    <label for="account_id" class="form-label">Account <span
                                            class="text-primary fw-bold">*</span></label>
                                    <select id="account_id" class="form-select" v-model="form.account">
                                        <option :value="null">Select an account</option>
                                        <option v-for="account in accounts" :value="account">{{ account.name }}</option>
                                    </select>
                                </div>
                                or create a <a id="link-to-create-account" href="/accounts/create"
                                    class="text-primary">new account</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template v-if="form.account">
                <OrdersSolutionView v-model="form" :solution_types="solution_types"></OrdersSolutionView>
                <div class="row mb-3 ">
                    <div class="col-6 d-flex justify-content-end">
                        <button id="btn-create-quote" class="btn btn-primary" type="submit" :disabled="form.solutionCheck">
                            <font-awesome-icon :icon="['fas', 'plus']" class="me-2"></font-awesome-icon>Create
                            quote
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
