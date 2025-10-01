<script setup>
import { Panel, Checkbox, MultiSelect, Select, InputText } from 'primevue';
import draggable from 'vuedraggable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import InputSelect from '@/Components/InputSelect.vue';
import { v4 as uuid } from 'uuid';
import ToggleSwitch from 'primevue/toggleswitch';
import RadioButton from 'primevue/radiobutton';
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
        : {},
    component: props.solution_type.configurations.component ?? 'DealMaker',
    has_custom_table: props.solution_type.configurations.has_custom_table ?? '0',
    calculator_layout: props.solution_type.configurations.calculator_layout ?? null
});
const solution = reactive({
    name: props.solution_type?.name,
    estimated_time: props.solution_type?.estimated_time,
    is_active: props.solution_type?.is_active === 1
});
const table = reactive(
    props.solution_type.table
        ? { ...props.solution_type.table }
        : {
              code: 'custom',
              group: null,
              group_name: null,
              columns: []
          }
);

const dragOptions = {
    animation: 200,
    group: 'description',
    disabled: false,
    ghostClass: 'ghost'
};

const categories = ref([]);

props.solution_type.categories.forEach((x) => {
    categories.value.push({ id: uuid(), category: x });
});

const saveSolutionType = () => {
    table.columns = table.columns.filter((x) => !!x.value);
    table.columns.forEach((x) => {
        let temp = props.available_columns.find((e) => e.value == x.value);
        x.has_order = !!temp?.has_order;
    });

    table.code = !table.code ? 'custom' : table.code;

    let temp = {};
    categories.value.forEach((item, index) => {
        if (item.category == null) {
            return;
        }
        temp[item.category.id] = { order: index };
    });

    router.put(
        `/solution-types/${props.solution_type.id}`,
        {
            ...solution,
            configurations: {
                ...configurations,
                amortisation: JSON.stringify(configurations.amortisation)
            },
            tables: [table],
            categories: temp
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

const addColumn = (table) => {
    if (!Object.keys(table).includes('columns')) {
        table.columns = [];
    }
    table.columns.push({ value: null, name: null });
};

const deleteColumn = (element) => {
    let index = table.columns.findIndex((x) => x.value == element.value);
    if (index != -1) {
        table.columns.splice(index, 1);
    }
};

const clearAllColumns = async (table) => {
    let result = await swal.fire({
        title: 'Are you sure?',
        text: 'This action removes all columns'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }

    table.columns = [];
};

const addCategory = () => {
    categories.value.push({ category: null, id: uuid() });
};

const deleteCategory = (element) => {
    let index = categories.value.findIndex((x) => x.value == element.value);
    if (index != -1) {
        categories.value.splice(index, 1);
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
    categories.value = [];
};

const checkCategories = (index) => {
    let selected_category = categories.value[index];

    let duplicated_category = categories.value.findIndex((x, i) => {
        return i !== index && x?.id === selected_category.id;
    });

    if (duplicated_category != -1) {
        notyf.error('Category already selected');
        categories.value[index] = {};
    }
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
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
                        <Panel class="mt-3" header="Categories" :toggleable="true">
                            <div class="mb-3">
                                <label class="form-label">Order Categories:</label>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%"></th>
                                            <th style="text-align: left">Category</th>
                                        </tr>
                                    </thead>
                                    <draggable
                                        v-model="categories"
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
                                                    {{ categories[index].category.name }}
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
                    <tab title="Quote">
                        <Panel class="mt-3" header="Summary Layout" :toggleable="true">
                            <p class="mb-3">
                                This section contains all the customizations for the appearance of the solution type to
                                match specific preferences within the quote.
                            </p>
                            <div>
                                <p for="solution_type-component" class="form-label">Choose the view:</p>
                                <Select
                                    inputId="solution_type-component"
                                    v-model="configurations.component"
                                    :options="available_tables"
                                    placeholder="Select a View"
                                    class="w-100"
                                />
                            </div>
                            <div class="mt-4">
                                <p for="solution_type-calculator-layout" class="form-label">
                                    Choose the Calculator Layout:
                                </p>
                                <div
                                    v-for="calculator_layout in props.calculator_layouts"
                                    class="flex align-items-center"
                                >
                                    <RadioButton
                                        v-model="configurations.calculator_layout"
                                        :inputId="calculator_layout.name"
                                        name="dynamic"
                                        :value="calculator_layout.name"
                                    />
                                    <label :for="calculator_layout.name" class="ms-2">{{
                                        calculator_layout.value
                                    }}</label>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p for="solution_type-calculator-layout" class="form-label">
                                    Choose the Calculator Layout:
                                </p>
                                <Select
                                    inputId="solution_type-component"
                                    v-model="configurations.calculator_layout"
                                    :options="props.calculator_layouts"
                                    optionLabel="value"
                                    optionValue="name"
                                    placeholder="Calculator Layout"
                                    class="w-100"
                                />
                            </div>
                        </Panel>

                        <Panel class="mt-3" header="Summary Table" :toggleable="true">
                            <p class="mb-3">
                                In this section you can enable or disable a custom a table shown in the solution type
                                view.
                            </p>
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <Checkbox
                                        inputId="has_custom_table"
                                        v-model="configurations.has_custom_table"
                                        binary
                                        trueValue="1"
                                        falseValue="0"
                                    />
                                    <label for="has_custom_table"> Enable Custom Table </label>
                                </div>
                            </div>
                            <template v-if="configurations.has_custom_table == '1'">
                                <div class="mb-3">
                                    <label class="form-label">Choose the group field (optional):</label>
                                    <Select
                                        v-model="table.group"
                                        :options="available_columns.filter((x) => x.is_group)"
                                        id="solution-type-table-group"
                                        placeholder="Select a group"
                                        class="w-100"
                                        optionLabel="name"
                                        optionValue="value"
                                        showClear
                                    />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pick a name (optional):</label>

                                    <InputText
                                        :id="`table-column-name`"
                                        type="text"
                                        v-model="table.group_name"
                                        class="w-100"
                                        size="small"
                                    />
                                </div>
                                <hr />
                                <div class="mb-3">
                                    <label class="form-label">Choose and order the table columns:</label>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th style="width: 50%">Field</th>
                                                <th style="width: 50%">Name</th>
                                                <th>
                                                    <button
                                                        id="solution-type-table-remove-columns"
                                                        class="btn p-2 mx-1 border-0"
                                                        @click="clearAllColumns(table)"
                                                    >
                                                        <font-awesome-icon
                                                            :icon="['fas', 'trash-alt']"
                                                        ></font-awesome-icon>
                                                    </button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <draggable
                                            v-model="table.columns"
                                            group="columns"
                                            item-key="id"
                                            tag="tbody"
                                            v-bind="dragOptions"
                                            handle=".handle"
                                        >
                                            <template #item="{ element, index }">
                                                <tr>
                                                    <td>
                                                        <button class="btn p-1 border-0 handle">
                                                            <font-awesome-icon icon="fas fa-bars" />
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <Select
                                                            v-model="element.value"
                                                            :options="available_columns"
                                                            :id="`table-column-` + index + `-value`"
                                                            optionLabel="name"
                                                            optionValue="value"
                                                            placeholder="Select a Field"
                                                            class="w-100"
                                                        ></Select>
                                                    </td>
                                                    <td>
                                                        <InputText
                                                            :id="`table-column-` + index + `-name`"
                                                            type="text"
                                                            v-model="element.name"
                                                            class="w-100"
                                                            size="small"
                                                        />
                                                    </td>
                                                    <td>
                                                        <button
                                                            class="btn p-2 mx-1 border-0"
                                                            @click="deleteColumn(element)"
                                                        >
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
                                                        <button
                                                            id="solution-type-table-add-column"
                                                            class="btn btn-sm"
                                                            @click="addColumn(table)"
                                                        >
                                                            <font-awesome-icon
                                                                :icon="['fas', 'plus']"
                                                            ></font-awesome-icon>
                                                        </button>
                                                    </div>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </template>
                        </Panel>
                    </tab>
                </tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
