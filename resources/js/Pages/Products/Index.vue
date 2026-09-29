<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import FileUploadModal from '@/Components/Import/FileUploadModal.vue';
import ImportPreviewModel from '@/Components/Import/ImportPreviewModel.vue';

const props = defineProps({
    solution_types: {
        type: Array,
        default: []
    },
    statuses: {
        type: Array,
        default: []
    },
    productRelationshipImports: {
        type: Array,
        default: () => []
    }
});

const showRelationshipImportForm = ref(false);
const showRelationshipImportPreview = ref(false);
const relationshipPreviewData = ref(null);
const relationshipImportForm = ref(null);

const handleRelationshipPreview = ({ data, form }) => {
    relationshipPreviewData.value = data;
    relationshipImportForm.value = form;
    showRelationshipImportPreview.value = true;
};

const options = new DatatableOptions('products', 'products-table');
options.addColumn('name', 'Name', { isSticky: true });
options.addColumn('sku', 'SKU');
options.addColumn('status', 'Status');
options.addColumn('description', 'Description');
options.addColumn('categories.name', 'Categories', { isSortable: false });
options.addColumn('linked_children.name', 'Linked', { isSortable: false });
options.addColumn('addons_children.name', 'Addons', { isSortable: false });
options.addColumn('bundles_children.name', 'Bundle', { isSortable: false });
options.addColumn('steps.name', 'Steps', { isSortable: false });
options.addColumn('steps.solution_type.name', 'Solution Type');
options.addColumn('created_at', 'Created At');
options.addColumn('updated_at', 'Updated At');
options.addColumn('expires_at', 'Expires At');
options.addColumn('price.cost', 'Cost', { isSearchable: false });
options.addColumn('price.net', 'Net', { isSearchable: false });
options.addColumn('price.net_spa', 'Final Sell Price', { isSearchable: false });
options.addColumn('price.active_spas.rule.name', 'Active Spa', { isSearchable: false, isSortable: false });
options.addColumn('price_plan.name', 'Price Plan', { isSortable: false });
options.addColumn('renewal_terms_value', 'Renewal Terms Value');
options.addColumn('renewal_terms_unit', 'Renewal Terms Unit');
options.addColumn('billing_frequency_value', 'Billing Frequency Value');
options.addColumn('billing_frequency_unit', 'Billing Frequency Unit');
// options.addColumn('child.price.cost', 'Cost Price', { width: '150px' }, { isSearchable: false });
// options.addColumn('child.price.net', 'Net Price', { width: '150px' }, { isSearchable: false });
// options.addColumn('child.price.net_spa', 'Final Sell Price', { width: '150px' }, { isSearchable: false });

options.addColumnFilter(
    'steps.solution_type.name',
    'select',
    props.solution_types.map((x) => x.name)
);

options.addColumnFilter('status', 'select', props.statuses);

options.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can(`export product`) || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Products' }]);
const totalProducts = ref(0);
</script>
<template>
    <Head title="Products" />
    <AuthenticatedLayout>
        <Header title="Products" :breadcrumbs="items">
            <template #buttons>
                <Link
                    v-if="can(`viewAny blacklist`) || hasRole('Super Admin')"
                    href="/blacklists"
                    id="blacklist-index-btn"
                    class="btn btn-outline-primary me-2"
                >
                    <font-awesome-icon :icon="['fas', 'shop-lock']" />
                    Blacklisted Items
                </Link>
                <a
                    v-if="can(`viewAny import`) || hasRole('Super Admin')"
                    href="#"
                    id="product-relationship-import-btn"
                    class="btn btn-outline-primary me-2"
                    @click.prevent="showRelationshipImportForm = true"
                >
                    <font-awesome-icon :icon="['fas', 'file-import']"></font-awesome-icon>
                    Import Product
                </a>
                <Link href="/products/create" id="product-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Product
                </Link>
            </template>
        </Header>
        <p class="small text-muted mb-2">{{ totalProducts }} product{{ totalProducts !== 1 ? 's' : '' }}</p>
        <NewDatatable :options="options" @update:meta="(meta) => (totalProducts = meta.total)">
            <template #status="{ value }">
                <span class="small badge" :class="value == 'Active' ? 'badge-success' : 'badge-muted'">
                    {{ value }}
                </span>
            </template>
            <template #updated_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #created_at="{ value }">
                <p class="small">{{ format.date(value) }}</p>
            </template>
            <template #is_active="{ data, value }">
                <ToggleActive :data="data" :value="value" route="products" />
            </template>
        </NewDatatable>

        <FileUploadModal
            v-model:visible="showRelationshipImportForm"
            model="/products"
            :imports="props.productRelationshipImports"
            @preview="handleRelationshipPreview"
        />
        <ImportPreviewModel
            v-model:visible="showRelationshipImportPreview"
            :preview-data="relationshipPreviewData"
            :import-form="relationshipImportForm"
        />
    </AuthenticatedLayout>
</template>
