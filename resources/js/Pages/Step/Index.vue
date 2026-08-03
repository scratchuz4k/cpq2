<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    solution_types: {
        type: Array,
        default: []
    }
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Steps' }]);
</script>
<template>
    <Head title="Steps" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Steps" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/steps/create" id="step-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                        New Step
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :table-props="{ id: 'steps-table' }"
                    :allow="{
                        update: true,
                        delete: true,
                        link: false
                    }"
                    :columns="[
                        { data: 'id', title: '#', style: 'width:5%' },
                        { data: 'is_active', title: 'Active', style: 'width:5%' },
                        { data: 'name', title: 'Name', style: 'width:25%' },
                        { data: 'solution_type.name', title: 'Solution Type', style: 'width:15%' },
                        { data: 'created_at', title: 'Created At', style: 'width:15%' },
                        { data: 'updated_at', title: 'Updated At', style: 'width:15%' }
                    ]"
                    :filters="{
                        'solution_type.name': {
                            type: 'options',
                            options: solution_types.map((x) => x.name)
                        }
                    }"
                    route="/steps"
                >
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #is_active="{ data, value }">
                        <ToggleActive :data="data" :value="value" route="steps" />
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
