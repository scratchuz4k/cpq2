<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from "@inertiajs/vue3";
import { ref } from 'vue';

const props = defineProps({
    operators: Array
})
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: "Products", url: "/products" },
    { label: 'Blacklist' },
]);

</script>
<template>
    <Head title="Blacklist" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Blacklist" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/blacklist/create" id="blacklist-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New Item
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable :allow="{
                    update: true,
                    delete: true,
                    link: false,
                }" :columns="[
                    { data: 'id', title: '#', style: 'width:1%' },
                    { data: 'productName', title: 'Name', style: 'width:10%' },
                    { data: 'providerName', title: 'Provider', style: 'width:5%' },
                    { data: 'connectivitySupplierGroup', title: 'Subprovider', style: 'width:5%' },
                    { data: 'bearer', title: 'Bearer', style: 'width:5%' },
                    { data: 'speed', title: 'Speed', style: 'width:5%' },
                    { data: 'contractTermMonths', title: 'Contract Term', style: 'width:29%' },
                    { data: 'created_at', title: 'created At', style: 'width:15%' },
                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' }
                ]" route="/blacklist">
                    <template #bearer="{ value }">
                        <span
                            v-if="!Array.isArray(value.value)"
                            v-tooltip
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            :title="operators.find(op => op.name === value?.operator)
                                ?.description + ' ' + value.value + ' ' + value.unit
                            "
                        >
                            {{ value.value }} {{ value.unit }}
                        </span>

                        <span
                            v-else
                            v-tooltip
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            :title="operators.find(op => op.name === value?.operator)
                                ?.description + ' ' + value.value[0] + ' and ' + value.value[1] + ' ' + value.unit
                            "
                        >
                            {{ value.value[0] }} to {{ value.value[1] }} {{ value.unit }}
                        </span>

                    </template>
                    <template #speed="{ value }">
                        <span
                            v-if="!Array.isArray(value.value)"
                            v-tooltip
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            :title="operators.find(op => op.name === value?.operator)
                                ?.description + ' ' + value.value + ' ' + value.unit
                            "
                        >
                            {{ value.value }} {{ value.unit }}
                        </span>

                        <span
                            v-else
                            v-tooltip
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            :title="operators.find(op => op.name === value?.operator)
                                ?.description + ' ' + value.value[0] + ' and ' + value.value[1] + ' ' + value.unit
                            "
                        >
                            {{ value.value[0] }} to {{ value.value[1] }} {{ value.unit }}
                        </span>

                    </template>
                    <template #contractTermMonths="{ value }">
                        {{ value ? value + ' months' : '' }}
                    </template>
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
