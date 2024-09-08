<script setup>
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue'
import axios from 'axios';
import OrdersTable from '@/Components/OrdersTable.vue';
DataTable.use(DataTablesCore);

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
})

const step = ref(0)

</script>

<template>

    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="d-flex justify-content-between" style="justify-content: space-between;">
            <h1>Create a Quote<span>.</span></h1>
            <Steps v-model="step" :steps="[
                'Account',
                'Solution',
                'Quote',
                'Extras',
                'Resume',
            ]">
            </Steps>
        </div>

        <div class="panel">
            <template v-if="step == 0">
                <div class="row mb-3">
                    <div class="col d-flex justify-content-end">
                        <button class="btn btn-primary" :disabled="form.account_id == null" type="button"
                            @click="() => step++">Continue</button>
                    </div>
                </div>
                <OrdersAccountView v-model="form" :accounts="accounts"></OrdersAccountView>
            </template>
            <template v-if="step == 1">
                <div class="row mb-3">
                    <div class="col d-flex  justify-content-end">
                        <button class="btn btn-primary" type="button" @click="() => step--">Back</button>
                        <button class="btn btn-primary mx-2" type="button" :disabled="solutionCheck"
                            @click="() => step++">
                            Continue
                            <div v-if="form.isLoading" class="spinner-border  spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </div>
                </div>
                <OrdersSolutionView v-model="form" :solution_types="solution_types"></OrdersSolutionView>
            </template>
            <template v-if="step == 2">
                <div class="row mb-3">
                    <div class="col d-flex  justify-content-end">
                        <button class="btn btn-primary " type="button" @click="() => step--">Back</button>
                        <button class="btn btn-primary mx-2" type="button" @click="() => step++">Continue</button>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col">
                        <div id="price_header">
                            <p class="mb-0">
                                <b>Solution Type:</b> {{ form.solution_type }}
                            </p>
                            <p class="mb-0" v-if="form.address?.label">
                                <b>Address:</b> {{ form.address?.label }}
                            </p>
                        </div>
                    </div>
                </div>
                <OrdersTable v-model="form"></OrdersTable>
            </template>
            <template v-if="step == 3">
                <div class="row mb-3">
                    <div class="col d-flex  justify-content-end">
                        <button class="btn btn-primary " type="button" @click="() => step--">Back</button>
                        <button class="btn btn-primary mx-2" type="button" @click="() => step++">Continue</button>
                    </div>
                </div>
            </template>
            <template v-if="step == 4">
                <div class="row mb-3">
                    <div class="col d-flex  justify-content-end">
                        <button class="btn btn-primary " type="button" @click="() => step--">Back</button>
                        <button class="btn btn-primary mx-2" type="button" @click="">Finish</button>
                    </div>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>