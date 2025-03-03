<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';
import axios from "axios";

const toggle_is_active = async (data) => {

    let result = await swal.fire({
        title: 'Do you want to change the active status?'
    })

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return false
    }

    try {
        let res = await axios.put(`/api/apis/` + data.id, { is_active: !data.is_active})
        if (res) {
            emitter.emit("datatable-refresh");
        }
        notyf.success('Updated with Success')
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
    }
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Apis' },
]);

</script>
<template>

    <Head title="Api" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Api" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/apis/create" id="api-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Api
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :allow="{
                        update: true,
                        delete: true,
                        link: false,
                    }"
                    :columns="[
                        { data: 'id', title: '#', style: 'width:5%' },
                        { data: 'is_active', title: 'Active', style: 'width:5%'},
                        { data: 'code', title: 'Code', style: 'width:15%'},
                        { data: 'name', title: 'Name', style: 'width:20%'},
                        { data: 'auth_type', title: 'Auth Type', style: 'width:15%'},
                        { data: 'created_at', title: 'Created At', style: 'width:15%' },
                        { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                    ]"
                    :filters="{
                        'is_active': { type: 'checkbox' },
                    }"
                    route="/apis"
                >
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #is_active="{ data, value }">
                        <div role="button" @click="toggle_is_active(data)">
                            <font-awesome-icon
                                v-if="value"
                                :icon="['fas', 'circle-check']"
                                style="font-size: 1.125rem"
                                class="text-primary"
                            />
                            <font-awesome-icon
                                v-else
                                :icon="['far', 'circle-xmark']"
                                style="font-size: 1.125rem"
                                class="text-secondary"
                            />
                        </div>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
