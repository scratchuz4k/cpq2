<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue'


const props = defineProps({
    order: Object,
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

const submit = () => {
    try {
        router.put(`/orders/${props.order.id}`,
            {
                address_line1: form.address?.label,
                address_postcode: form.address?.addressparts?.PostCode,
                solution_type_id: form.solutionType?.id,
                address_position: form.position,
                address_city: form.address?.addressparts?.PostTown,
                address_data: form.address,
            })
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

</script>

<template>
    <Head title="Create a Quote" />
    <AuthenticatedLayout :order="order">
        <form @submit.prevent="submit">
            <div class="row mb-3">
                <div class="col d-flex">
                    <div class="me-3">
                        <font-awesome-icon class="fa-border" :icon="['fas', 'wrench']"
                            style="font-size:30px;color:var(--secondary-light);border-color:var(--secondary-light);border-width:4px;border-radius:12px" />
                    </div>
                    <div style="padding-top:1px">
                        <p class="m-0">Q-{{ order.id }} // <span id="account_name">
                                {{ order.account?.name }}
                            </span>
                        </p>
                        <h1 class="m-0" style="font-size:18px;font-weight:bold">Configure</h1>
                    </div>
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
            <div class="panel">
                <OrdersSolutionView v-model="form" :solution_types="solution_types"></OrdersSolutionView>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
