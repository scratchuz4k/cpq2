<script setup>
import { Panel, MultiSelect, Select } from 'primevue';
import draggable from 'vuedraggable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { v4 as uuid } from 'uuid';
import ToggleSwitch from 'primevue/toggleswitch';
import Tabs from '@/Components/Tabs.vue';

const props = defineProps({
    suppliers: Object,
    solution_type: Object,
    available_tables: Array,
    available_columns: Array,
    calculator_layouts: Array
});

const configurations = reactive({
    amortisation: props.solution_type.configurations.amortisation
        ? JSON.parse(props.solution_type.configurations.amortisation)
        : {}
});
const solution = reactive({
    name: props.solution_type?.name,
    estimated_time: props.solution_type?.estimated_time,
    is_active: props.solution_type?.is_active === 1
});

const dragOptions = {
    animation: 200,
    group: 'description',
    disabled: false,
    ghostClass: 'ghost'
};

const steps = ref([]);

props.solution_type.steps.forEach((x) => {
    steps.value.push({ id: uuid(), step: x });
});

const saveSolutionType = () => {
    let temp = {};
    steps.value.forEach((item, index) => {
        if (item.step == null) {
            return;
        }
        temp[item.step.id] = { order: index };
    });

    router.put(
        `/solution-types/${props.solution_type.id}`,
        {
            ...solution,
            configurations: {
                ...configurations,
                amortisation: JSON.stringify(configurations.amortisation)
            },
            steps: temp
        },
        {
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
        }
    );
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
    { label: 'Solution Types', url: '/solution-types' },
    { label: 'Edit Solution Type' }
]);
</script>
<template>
    <Head title="Solution Types" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Solution Types #' + props.solution_type.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-solution-type" @click="saveSolutionType()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="mt-3">
                <tabs>
                    <tab title="General" :selected="true">
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
                            <div class="mb-3">
                                <label for="solution_type-name" class="form-label">Name:</label>
                                <input id="solution_type-name" class="form-control" v-model="solution.name" disabled />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="solution_type-estimated_time" class="form-label">Estimated time:</label>
                            <input
                                id="solution_type-estimated_time"
                                class="form-control"
                                v-model="solution.estimated_time"
                            />
                        </div>
                    </tab>
                    <tab title="Price" v-if="solution_type.code != 3">
                        <Panel class="mt-3" header="Steps" :toggleable="true">
                            <div class="mb-3">
                                <label class="form-label">Order Steps:</label>
                                <table class="table" id="steps-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%"></th>
                                            <th style="text-align: left">Step</th>
                                        </tr>
                                    </thead>
                                    <draggable
                                        v-model="steps"
                                        item-key="id"
                                        tag="tbody"
                                        v-bind="dragOptions"
                                        handle=".handle"
                                    >
                                        <template #item="{ element, index }">
                                            <tr>
                                                <td style="width: 5%">
                                                    <button class="btn p-1 border-0 handle">
                                                        <font-awesome-icon icon="fas fa-bars" />
                                                    </button>
                                                </td>
                                                <td style="text-align: left">
                                                    {{ steps[index].step.name }}
                                                </td>
                                            </tr>
                                        </template>
                                    </draggable>
                                </table>
                            </div>
                        </Panel>
                    </tab>
                    <tab title="Price" v-if="solution_type.code == 3">
                        <Panel class="mt-3" header="Amortisation" :toggleable="true">
                            <div class="mb-3">
                                <label class="form-label">Choose months to use amortisation:</label>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; width: 40%">Supplier</th>
                                            <th style="text-align: center">Months</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="supplier in props.suppliers">
                                            <td style="text-align: left">{{ supplier.name }}</td>
                                            <td>
                                                <MultiSelect
                                                    v-model="configurations.amortisation[supplier.value]"
                                                    style="width: 100%"
                                                    :options="['12', '24', '36', '48', '60']"
                                                    placeholder="Select Months"
                                                >
                                                    <template #option="slotProps">
                                                        <div style="font-size: 14px; line-height: 1.5rem">
                                                            {{ slotProps.option }} Months
                                                        </div>
                                                    </template>
                                                </MultiSelect>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </Panel>
                    </tab>
                </tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
