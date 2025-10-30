<script setup>
import { reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from 'primevue';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import Tabs from '@/Components/Tabs.vue';
import Tab from '@/Components/Tab.vue';

const props = defineProps({
    step: Object,
    solution_types: Array
});

const product = ref(null);
const step = reactive(props.step);

const saveStep = () => {
    let temp = step;
    temp.solution_type_id = step.solution_type?.id ?? null;
    router.put(`/steps/${step.id}`, step, {
        onSuccess: (page) => {
            notyf.success('Updated with Success');
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
    { label: 'My Account', url: '/myaccount' },
    { label: 'Steps', url: '/steps' },
    { label: 'Edit Step' }
]);
</script>
<template>
    <Head title="Edit Step" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Edit Step" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-step" @click="saveStep()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <tabs>
                <tab :selected="true" title="General">
                    <div class="col-6">
                        <div class="mb-3 d-flex flex-column">
                            <label class="form-label" for="active-filter"> Active: </label>
                            <ToggleSwitch id="active-filter" v-model="step.is_active">
                                <template #handle>
                                    <font-awesome-icon
                                        :icon="step.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                        class="text-primary"
                                    />
                                </template>
                            </ToggleSwitch>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="step-name">Name:</label>
                            <input id="step-name" v-model="step.name" class="form-control" />
                        </div>

                        <h4>Relations</h4>
                        <hr />
                        <div class="mb-3">
                            <label for="step-solution-type" class="form-label">Solution Type:</label>
                            <Select
                                inputId="step-solution-type"
                                v-model="step.solution_type"
                                :options="props.solution_types"
                                optionLabel="name"
                                placeholder="Select a Solution Type"
                                class="w-100"
                            />
                        </div>
                    </div>
                </tab>
                <tab title="Products">
                    <div class="row mt-3">
                        <div class="col">
                            <Datatable
                                id="group-products"
                                :columns="[
                                    { data: 'id', title: '#', style: 'width:5%' },
                                    { data: 'code', title: 'Code', style: 'width:20%' },
                                    { data: 'name', title: 'Name', style: 'width:30%' },
                                    { data: 'supplier.name', title: 'Supplier', style: 'width:12%' },
                                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                                    { data: 'actions', title: ' At', style: 'width:5%' }
                                ]"
                                :query="[{ column: 'step.id', operator: 'equals', value: step.id }]"
                                route="/products"
                            >
                                <template #updated_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                                <template #created_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                            </Datatable>
                        </div>
                    </div>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
