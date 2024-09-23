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
    solutionType: null,
    address: null,
    postCode: null,
    position: null,
    solutionCheck: true
})

const step = ref(0)

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
                solution_type_id: form.solutionType?.code
            })
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

</script>

<template>

    <Head title="Create a Quote" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between" style="justify-content: space-between;">
            <h1>Create a Quote<span>.</span></h1>
            <!-- <Steps v-model="step" :steps="[
                'Account',
                'Solution',
                'Quote',
                'Resume',
            ]">
            </Steps> -->
        </div>

        <div class="panel">
            <form @submit.prevent="submit">
                <template v-if="step == 0">
                    <div class="row mb-3">
                        <div class="col d-flex justify-content-end">
                            <button class="btn btn-primary" :disabled="form.account == null" type="button"
                                @click="() => step++">Continue</button>
                        </div>
                    </div>
                    <OrdersAccountView v-model="form" :accounts="accounts"></OrdersAccountView>
                </template>
                <template v-if="step == 1">
                    <div class="row mb-3">
                        <div class="col d-flex  justify-content-end">
                            <button class="btn btn-primary" type="button" @click="() => step--">Back</button>
                            <button class="btn btn-primary mx-2" type="submit" :disabled="form.solutionCheck">
                                Create
                                <div v-if="form.isLoading" class="spinner-border  spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    <OrdersSolutionView v-model="form" :solution_types="solution_types"></OrdersSolutionView>
                </template>
            </form>
        </div>
    </AuthenticatedLayout>
</template>