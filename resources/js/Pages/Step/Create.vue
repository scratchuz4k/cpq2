<script setup>
import { reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    solution_types: Array
});

const step = reactive({
    name: null,
    is_active: true,
    solution_type_id: null
});

const saveStep = () => {
    let temp = step;
    temp.solution_type_id = step.solution_type ?? null;

    router.post(`/steps`, temp, {
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
    { label: 'My Account', url: '/myaccount' },
    { label: 'Steps', url: '/steps' },
    { label: 'New Step' }
]);
</script>
<template>
    <Head title="New Step" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Step" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-step" @click="saveStep()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
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
                    <label for="step-name" class="form-label">Name:</label>
                    <input id="step-name" class="form-control" v-model="step.name" />
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
                        optionValue="id"
                        placeholder="Select a Solution Type"
                        class="w-100"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
