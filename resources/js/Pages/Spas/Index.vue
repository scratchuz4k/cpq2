<script setup>
import format from '@composables/format';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import axios from 'axios';
import { can, hasRole } from '@/Composables/useAuth.js';
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';

const page = usePage();
const props = defineProps({
    rules: Array,
    price_plans: Array
});

const showGlobal = ref(false);
const selectedRules = ref([]);

const form = reactive({
    rule: null,
    rule_id: [],
    field_1: null,
    field_2: null,
    group: null
});

const fields = reactive({
    account: null,
    supplier: null,
    product: null
});

const globalRules = {};
props.rules.forEach((element) => {
    let str = element.code;
    const splitResult = str.split(/(?=[A-Z])/);
    if (!Object.keys(globalRules).includes(splitResult[0])) {
        globalRules[splitResult[0]] = [];
    }

    if (!globalRules[splitResult[0]].find((x) => x === splitResult[1])) {
        globalRules[splitResult[0]].push(splitResult[1] ?? null);
    }
});

// The entity filters below don't map onto single columns, so they are pushed into the table as
// raw query filters (reactive, so the table reloads whenever they change).
const options = reactive(new DatatableOptions('spas', 'spas-table'));
options.addColumn('id', '#');
options.addColumn('is_active', 'Active');
options.addColumn('rule.name', 'Rule');
// field_1/field_2 hold the id of a different entity type per rule, so they can be sorted but not searched by name.
options.addColumn('field_1', 'Level 1', { isSearchable: false });
options.addColumn('field_2', 'Level 2', { isSearchable: false });
options.addColumn('price_plan.name', 'Price Plan');
options.addColumn('operation.code', 'Operation');
options.addColumn('group.name', 'Group');
options.addColumn('value', 'Value');
options.addColumn('percentage', 'Percentage');
options.addColumnFilter('is_active', 'checkbox');
options.setSort('asc', 'id');
options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export spa') || hasRole('Super Admin'),
    duplicate: (item) => cloneSpa(item)
});

const entityFilterCount = computed(
    () => (showGlobal.value ? 1 : 0) + Object.values(fields).filter((x) => x != null).length + (form.group ? 1 : 0)
);

const getData = () => {
    options.query = getFilters();
};

const handleGlobal = () => {
    let keys = Object.keys(fields).filter((x) => fields[x] != null);
    showGlobal.value = keys.length ? true : !showGlobal.value;
    Object.keys(fields).forEach((x) => (fields[x] = null));

    form.rule_id = [];
    form.field_1 = null;
    form.field_2 = null;
    if (!showGlobal.value) {
        selectedRules.value = [];
        getData();
        return;
    }

    selectedRules.value = props.rules;
    selectedRules.value = selectedRules.value.filter((x) => x.code.includes('Global'));

    getData();
};

const handleFields = () => {
    showGlobal.value = false;
    let keys = Object.keys(fields).filter((x) => fields[x] != null);

    if (keys.length > 2) {
        selectedRules.value = [];
        notyf.error('Only two entities may be selected at a time');
        return;
    } else if (keys.length == 0) {
        selectedRules.value = [];
    } else {
        selectedRules.value = props.rules;

        keys.forEach((e) => {
            selectedRules.value = selectedRules.value.filter((x) => {
                return x.code.includes(format.capitalizeFirstLetter(e));
            });
        });
    }
    getData();
};

const getFilters = () => {
    let temp = [];
    if (!showGlobal.value) {
        let keys = Object.keys(fields).filter((x) => fields[x] != null);
        selectedRules.value.forEach((x, ruleIndex) => {
            const splitResult = x.code.split(/(?=[A-Z])/);
            splitResult.forEach((e, index) => {
                if (!keys.includes(e.toLowerCase())) {
                    return;
                }

                temp.push({
                    column: 'rule_id',
                    operator: 'equals',
                    value: x.id,
                    function: ruleIndex != 0 ? 'orWhere' : 'where'
                });

                if (index == 0) {
                    temp.push({
                        column: 'field_1',
                        operator: 'equals',
                        value: fields[e.toLowerCase()]?.id
                    });
                }

                if (index == 1) {
                    temp.push({
                        column: 'field_2',
                        operator: 'equals',
                        value: fields[e.toLowerCase()]?.id
                    });
                }
            });
        });
    } else {
        temp.push({
            column: 'rule_id',
            operator: 'equals',
            value: selectedRules.value[0].id
        });

        temp.push({
            column: 'field_1',
            operator: 'equals',
            value: page.props.tenant.id
        });
    }

    if (!!form.group) {
        temp.push({
            column: 'group_id',
            operator: 'equals',
            value: form.group?.id
        });
    }

    return temp;
};

const cloneSpa = async (item) => {
    let result = await swal.fire({
        title: 'Are you sure?',
        text: 'This action will clone the selected SPA'
    });
    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }
    try {
        let res = await axios.post('/api/spas', {
            rule_id: item.rule_id ?? 0,
            field_1: item.field_1?.id ?? 0,
            field_2: item.field_2?.id ?? 0,
            operation_id: item.operation_id ?? 0,
            price_plan_id: item.price_plan_id ?? null,
            value: item.value ?? 0,
            percentage: item.percentage ?? 0,
            is_active: item.is_active ?? true,
            group_id: item.group_id ?? null
        });
        notyf.success('Clone with success');
        emitter.emit('datatable-refresh:spas-table');
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};

const clearFilters = () => {
    showGlobal.value = false;
    Object.keys(fields).forEach((x) => (fields[x] = null));
    selectedRules.value = [];
    form.group = null;
    getData();
};

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'SPAs' }]);
</script>
<template>
    <Head title="SPAs" />
    <AuthenticatedLayout>
        <Header title="SPAs" :breadcrumbs="items">
            <template #buttons>
                <Link id="create-spa" href="/spas/create" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New SPA
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options" :extra-filter-count="entityFilterCount" @clear-filters="clearFilters">
            <template #filters>
                <p class="spa-filter-title">Entities</p>
                <div class="mb-2">
                    <label class="spa-filter-label" for="Global">Global</label>
                    <button
                        type="button"
                        id="Global"
                        class="btn btn-sm w-100"
                        :class="[showGlobal ? 'btn-primary' : 'btn-outline-primary']"
                        @click="handleGlobal()"
                    >
                        {{ showGlobal ? 'Showing global SPAs' : 'Show global SPAs' }}
                    </button>
                </div>
                <div class="mb-2">
                    <label class="spa-filter-label">Account</label>
                    <InputSelect
                        id="filter-account"
                        url="/accounts"
                        :fields="['name', 'reference']"
                        v-model="fields.account"
                        @change="handleFields()"
                        placeholder="Select an Account"
                        :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                    />
                </div>
                <div class="mb-2">
                    <label class="spa-filter-label">Supplier</label>
                    <InputSelect
                        id="filter-supplier"
                        url="/suppliers"
                        :fields="['name']"
                        v-model="fields.supplier"
                        @change="handleFields()"
                        placeholder="Select a Supplier"
                        :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                    />
                </div>
                <div class="mb-2">
                    <label class="spa-filter-label">Product</label>
                    <InputSelect
                        id="filter-product"
                        url="/products"
                        :fields="['name']"
                        v-model="fields.product"
                        @change="handleFields()"
                        placeholder="Select a Product"
                        :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                    />
                </div>
                <div class="mb-2">
                    <label class="spa-filter-label">Group</label>
                    <InputSelect
                        id="filter-group"
                        url="/groups"
                        :fields="['name']"
                        v-model="form.group"
                        @change="handleFields()"
                        placeholder="Select a Group"
                    />
                </div>
                <div v-if="selectedRules.length" class="mt-2">
                    <p class="spa-filter-label">Matching rules</p>
                    <span
                        v-for="rule in selectedRules"
                        :key="rule.id"
                        class="badge border border-secondary text-secondary opacity-75 me-1 mb-1 lh-sm"
                    >
                        {{ rule.name }}
                    </span>
                </div>
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive
                    :data="{
                        ...data,
                        field_1: data.field_1?.id,
                        field_2: data.field_2?.id
                    }"
                    :value="value"
                    route="spas"
                />
            </template>
            <template #field_1="{ value }">
                <p class="small overflow-hidden text-truncate" :title="value?.name">{{ value?.name }}</p>
            </template>
            <template #field_2="{ value }">
                <p class="small overflow-hidden text-truncate" :title="value?.name">{{ value?.name }}</p>
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
<style scoped>
/* Matches the column filter rows of DatatableFilterPanel. */
.spa-filter-title {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.spa-filter-label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #646464;
    margin-bottom: 4px;
}
</style>
