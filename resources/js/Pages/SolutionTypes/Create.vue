<script setup>
import Panel from 'primevue/panel';
import Select from 'primevue/select';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import draggable from 'vuedraggable';
import ToggleSwitch from 'primevue/toggleswitch';

const page = usePage();
const props = defineProps({
    solutions: Array,
    available_tables: Array
});

const solution = reactive({
    tenant_id: page.props.tenant.id,
    solution_type_id: null,
    configurations: {
        component: null
    },
    categories: [],
    is_active: true
});

const dragOptions = {
    animation: 200,
    group: 'description',
    disabled: false,
    ghostClass: 'ghost'
};

const saveSolution = () => {
    solution.configurations.component ??= 'DealMaker';
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


const addCategory = () => {
    solution.categories.push({ name: null, id: null });
};

const deleteCategory = (element) => {
    let index = solution.categories.findIndex((x) => x.value == element.value);
    if (index != -1) {
        solution.categories.splice(index, 1);
    }
};

const clearAllCategories = async () => {
    let result = await swal.fire({
        title: 'Are you sure?',
        text: 'This action removes all categories'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }
    solution.categories = [];
};

const checkCategories = (index) => {
    let selected_category = solution.categories[index];

    let duplicated_category = solution.categories.findIndex((x, i) => {
        return i !== index && x?.id === selected_category.id;
    });

    if (duplicated_category != -1) {
        notyf.error('Category already selected');
        solution.categories[index] = {};
    }
};


const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
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
                                <label class="form-label" for="active-filter">
                                    Active:
                                </label>
                                <ToggleSwitch id="active-filter" v-model="solution.is_active">
                                    <template #handle>
                                        <font-awesome-icon
                                            :icon="solution.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                            class="text-primary"
                                        />
                                    </template>
                                </ToggleSwitch>
                            </div>
                            <div class="mb-5">
                                <label for="solution_type-name" class="form-label">Name:</label>
                                <Select required inputId="solution_type" v-model="solution.solution_type_id"
                                    :options="solutions" optionLabel="name" optionValue="id"
                                    placeholder="Select Solution type" class="w-100" />
                            </div>
                        </div>
                    </tab>
                    <tab title="Price" v-if="solution.code != 3">
                        <Panel class="mt-3" header="Categories" :toggleable="true">
                            <div class="mb-3">
                                <label class="form-label">Choose Categories:</label>
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th style="width: 5%;"></th>
                                        <th style="text-align: left;">Category</th>
                                        <th style="text-align: right;width: 5%;">
                                            <button id="solution-type-categories-remove-columns"
                                                class="btn p-2 mx-1 border-0" @click="clearAllCategories()">
                                                <font-awesome-icon :icon="['fas', 'trash-alt']"></font-awesome-icon>
                                            </button>
                                        </th>
                                    </tr>
                                    </thead>
                                    <draggable v-model="solution.categories" item-key="id" tag="tbody"
                                        v-bind="dragOptions" handle=".handle">
                                        <template #item="{ element, index }">
                                            <tr>
                                                <td style="width: 5%;">
                                                    <button class="btn p-1 border-0 handle">
                                                        <font-awesome-icon icon="fas fa-bars" />
                                                    </button>
                                                </td>
                                                <td style="text-align: left;">
                                                    <InputSelect v-model="solution.categories[index]" url="/categories"
                                                        :id="`solution-type-categories-` + index + `-value`"
                                                        :fields="['name']" placeholder="Select a Category"
                                                        @change="checkCategories(index)">
                                                    </InputSelect>
                                                </td>
                                                <td style="text-align: right; width: 5%;">
                                                    <button class="btn p-2 mx-1 border-0"
                                                        @click="deleteCategory(element)">
                                                        <font-awesome-icon icon="fas fa-trash-alt" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </draggable>
                                    <tfoot>
                                    <tr>
                                        <th colspan="4">
                                            <div class="d-flex justify-content-end">
                                                <button id="solution-type-table-add-category" class="btn btn-sm"
                                                    @click="addCategory()">
                                                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                                                </button>
                                            </div>
                                        </th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </Panel>
                    </tab>
                    <tab title="Quote" id="tab-quotes">
                        <p class="my-3">
                            This section contains all the customizations for the appearance of the
                            solution type to match specific preferences within the quote.
                        </p>
                        <div class="my-3">
                            <p for="solution_type-component" class="form-label">Choose the view:</p>
                            <Select required inputId="solution_type-component"
                                v-model="solution.configurations.component" :options="available_tables"
                                placeholder="Select a View" class="w-100" />
                        </div>
                    </tab>
                </tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
