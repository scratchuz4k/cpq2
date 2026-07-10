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

const setStepProduct = async (product_id, step_id) => {
    if (!product_id) {
        return;
    }
    try {
        if (step_id) {
            await axios.put('/api/products/' + product_id, { steps: [step_id] });
        } else {
            await axios.put('/api/products/' + product_id, { detach_steps: [step.id] });
        }
        product.value = null;

        notyf.success(step_id ? 'Product added with Success' : 'Product removed with success');

        emitter.emit('datatable-refresh:step-products');
        emitter.emit('input-select-refresh:select-product');
    } catch (ex) {
        product.value = null;
        notyf.error(ex.response?.data?.message ?? ex.response?.data);
    }
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
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
                    <form @submit.prevent="setStepProduct(product?.id, step.id)" class="mt-3 d-flex">
                        <div class="me-3" style="width: 100%">
                            <label class="form-label">Add Products:</label>
                            <InputSelect
                                id="select-product"
                                url="/products"
                                :fields="['name']"
                                v-model="product"
                                placeholder="Select an Product"
                                :query="[
                                    { column: 'steps.id', operator: 'not_in', value: step.id },
                                    { column: 'is_active', operator: 'equals', value: 1 }
                                ]"
                            >
                            </InputSelect>
                        </div>
                        <div class="d-flex align-items-end justify-content-end">
                            <Button id="button-add-product-to-step" type="submit" class="btn btn-secondary">
                                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                                Add
                            </Button>
                        </div>
                    </form>
                    <div class="row mt-3">
                        <div class="col">
                            <Datatable
                                id="step-products"
                                :columns="[
                                    { data: 'id', title: '#', style: 'width:5%' },
                                    { data: 'sku', title: 'Code', style: 'width:20%' },
                                    { data: 'name', title: 'Name', style: 'width:30%' },
                                    { data: 'supplier.name', title: 'Supplier', style: 'width:12%' },
                                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                                    { data: 'actions', title: ' At', style: 'width:5%' }
                                ]"
                                :query="[{ column: 'steps.id', operator: 'equals', value: step.id }]"
                                route="/products"
                            >
                                <template #updated_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                                <template #created_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                                <template #actions="{ data, value, index }">
                                    <button
                                        class="btn px-2 py-0 mx-1 border-0"
                                        :id="'datatable-btn-' + index + '-delete'"
                                        @click="setStepProduct(data.id, null)"
                                    >
                                        <font-awesome-icon :icon="['fas', 'trash-alt']"></font-awesome-icon>
                                    </button>
                                </template>
                            </Datatable>
                        </div>
                    </div>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
