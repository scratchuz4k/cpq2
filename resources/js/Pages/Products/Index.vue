<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';

const props = defineProps({
    solution_types: {
        type: Array,
        default: []
    }
});

const options = new DatatableOptions('products', 'products-table');
options.addColumn('name', 'Name', {}, { isSticky: true });
options.addColumn('sku', 'SKU', null, { isSticky: true });
options.addColumn('status', 'Status', {}, {});
options.addColumn('description', 'Description', {}, {});
options.addColumn('categories.name', 'Categories', {}, {});
options.addColumn('linked_children.name', 'Linked', {}, {});
options.addColumn('addons_children.name', 'Addons', {}, {});
options.addColumn('bundles_children.name', 'Bundle', {}, {});
options.addColumn('steps.name', 'Steps', {}, {});
options.addColumn('steps.solution_type.name', 'Solution Type', null, {});
options.addColumn('created_at', 'Created At', null, {});
options.addColumn('updated_at', 'Updated At', null, {});
options.addColumn('expires_at', 'Expires At', null, {});
options.addColumn('price.cost', 'Cost', null, { isSearchable: false });
options.addColumn('price.net', 'Net', null, { isSearchable: false });
options.addColumn('price.net_spa', 'Net Spa', null, { isSearchable: false });
options.addColumn('price.active_spas.rule.name', 'Active Spa', null, { isSearchable: false });
options.addColumn('price_plan.name', 'Contact Term', null, {});
// options.addColumn('child.price.cost', 'Cost Price', { width: '150px' }, { isSearchable: false });
// options.addColumn('child.price.net', 'Net Price', { width: '150px' }, { isSearchable: false });
// options.addColumn('child.price.net_spa', 'Net SPA', { width: '150px' }, { isSearchable: false });

options.addColumnFilter(
    'steps.solution_type.name',
    'select',
    props.solution_types.map((x) => x.name)
);
options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can(`export product`) || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Products' }]);
</script>
<template>
    <Head title="Products" />
    <AuthenticatedLayout>
        <Header title="Products" :breadcrumbs="items">
            <template #buttons>
                <Link href="/blacklists" id="blacklist-index-btn" class="btn btn-outline-primary me-2">
                    <font-awesome-icon :icon="['fas', 'shop-lock']" />
                    Blacklisted Items
                </Link>
                <Link href="/imports" id="product-import-btn" class="btn btn-outline-primary me-2">
                    <font-awesome-icon :icon="['fas', 'file-import']"></font-awesome-icon>
                    Import Products
                </Link>
                <Link href="/products/create" id="product-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Product
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options">
            <template #status="{ value }">
                <span class="badge text-bg-secondary">{{ value }}</span>
            </template>
            <template #steps.name="{ data, value }">
                <span>
                    {{
                        data.steps
                            .map((step) => step.name)
                            .filter(Boolean)
                            .join(', ')
                    }}
                </span>
            </template>
            <template #steps.solution_type.name="{ data, value }">
                <span>
                    {{ [...new Set(data.steps.map((step) => step.solution_type?.name).filter(Boolean))].join(', ') }}
                </span>
            </template>
            <template #updated_at="{ value }">
                {{ format.date(value) }}
            </template>
            <template #created_at="{ value }">
                {{ format.date(value) }}
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive :data="data" :value="value" route="products" />
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
