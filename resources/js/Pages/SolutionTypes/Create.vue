<script setup>
import Select from 'primevue/select';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import ToggleSwitch from 'primevue/toggleswitch';

const page = usePage();
const props = defineProps({
    solutions: Array
});

const solution = reactive({
    tenant_id: page.props.tenant.id,
    solution_type_id: null,
    is_active: true
});

const saveSolution = () => {
    router.post('/solution-types', solution, {
        onSuccess: (page) => {
            notyf.success('Created with Success');
        },
        onError: (errors) => {
            let string = '';
            Object.keys(errors).forEach((key) => {
                string += errors[key] + '<br>';
            });
            notyf.error(string);
        }
    });
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
    { label: 'Solution Types', url: '/solution-types' },
    { label: 'New Solution Type' }
]);
</script>
<template>
    <Head title="Solution Type" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Solution Type" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-solution-type" @click="saveSolution()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="mt-3">
                <tabs>
                    <tab title="General" id="tab-general" :selected="true">
                        <div class="mt-3">
                            <div class="mb-3 d-flex flex-column">
                                <label class="form-label" for="active-filter"> Active: </label>
                                <ToggleSwitch id="active-filter" v-model="solution.is_active">
                                    <template #handle>
                                        <font-awesome-icon
                                            :icon="
                                                solution.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']
                                            "
                                            class="text-primary"
                                        />
                                    </template>
                                </ToggleSwitch>
                            </div>
                            <div class="mb-5">
                                <label for="solution_type-name" class="form-label">Name:</label>
                                <Select
                                    required
                                    inputId="solution_type"
                                    v-model="solution.solution_type_id"
                                    :options="solutions"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Select Solution type"
                                    class="w-100"
                                />
                            </div>
                        </div>
                    </tab>
                </tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
