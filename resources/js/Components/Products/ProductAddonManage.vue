<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import { FilterOperators } from '@composables/useApi';
import ProductCreateRelation from './ProductCreateRelation.vue';
import { can, hasRole } from '@composables/useAuth';
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: Object,
    queries: Array
});

const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const totals = ref(proxyValue.value.addons?.length ?? 0);
const optionAddons = new DatatableOptions('product-addons', 'product-addons');
optionAddons.addColumn('addon.sku', 'SKU', null, { isSticky: true });
optionAddons.addColumn('addon.name', 'Name', null, { isSticky: true });
optionAddons.addColumn('addon.description', 'Description', null, {});
optionAddons.addColumn('addon.categories.name', 'Categories', {}, {});
optionAddons.addColumn('addon.status', 'Status', null, {});
optionAddons.addColumn('addon.price.cost', 'Cost Price', { width: '150px' }, { isSearchable: false });
optionAddons.addColumn('addon.price.net', 'Net Price', { width: '150px' }, { isSearchable: false });
optionAddons.addColumn('addon.price.net_spa', 'Net SPA', { width: '150px' }, { isSearchable: false });
optionAddons.addColumn(
    'addon.price.active_spas',
    'Active SPAs',
    { width: '350px' },
    { isSearchable: false, isSortable: false }
);
optionAddons.addColumn('addon.price_plan.name', 'Contract Term', {}, {});
optionAddons.addColumn('addon.steps.name', 'Steps', { width: '150px' }, {});
optionAddons.addColumn('addon.steps.solution_type.name', 'Solution Type', { width: '250px' }, {});

optionAddons.addQuery('product_id', FilterOperators.EQUALS, proxyValue.value.id);
optionAddons.setAllowedActions({
    destroy: true,
    allowExport: can(`export product`) || hasRole('Super Admin')
});
const addonProductQueries = () => {
    return [];
};

const getProductAddons = async () => {
    const res = await axios.get(`/api/products/${proxyValue.value.id}`);
    proxyValue.value.addons = res.data.data.addons;
};
const setMeta = (newTotals) => {
    totals.value = newTotals;
    getProductAddons();
};
</script>

<template>
    <div class="d-flex justify-content-between my-3">
        <div>
            <h3>Addons ({{ totals }})</h3>
        </div>
        <div>
            <ProductCreateRelation
                id="addons"
                route="product-addons"
                :product="proxyValue"
                column="addon_id"
                :queries="addonProductQueries"
                @product-added="getProductAddons()"
            />
        </div>
    </div>
    <NewDatatable :options="optionAddons" @update:meta="(meta) => setMeta(meta.total)">
        <template #addon.status="{ data, value }">
            <span
                class="badge"
                :class="[value !== 'Active' ? (value == 'Inactive' ? 'badge-danger' : 'badge-muted') : 'badge-success']"
            >
                {{ value }}
            </span>
        </template>
        <template #addon.price.cost="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>
        <template #addon.price.net="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>
        <template #addon.price.net_spa="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>

        <template #addon.price.active_spas="{ data, value }">
            <p class="small" v-if="value && value.length == 1" style="width: 250px !important">
                {{ value[0].rule.name }} - {{ value[0].operation.code }}:
                {{
                    value[0].percentage ? format.decimalPlaces(value[0].percentage) + '%' : format.GBP(value[0].margin)
                }}
            </p>
            <p
                class="small"
                v-else-if="value && value.length > 1"
                v-tooltip
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                :title="
                    value
                        .slice(1)
                        .map(
                            (category) =>
                                category.rule.name +
                                ' - ' +
                                category.operation.code +
                                ': ' +
                                (category.percentage
                                    ? format.decimalPlaces(category.percentage) + '%'
                                    : format.GBP(category.value))
                        )
                        .join(', ')
                "
                style="cursor: pointer; width: 250px !important"
            >
                {{ value[0].rule.name }} - {{ value[0].operation.code }}:
                {{ value[0].percentage ? format.decimalPlaces(value[0].percentage) + '%' : format.GBP(value[0].value) }}
                <span class="color-purple-dark fw-semibold ms-1">+{{ value.length - 1 }}</span>
            </p>
        </template>
        <template #child.categories.name="{ data, value }">
            <p
                class="small"
                v-if="data.child.categories && data.child.categories.length == 1"
                style="width: 150px !important"
            >
                {{ data.child.categories[0].name }}
            </p>
            <p
                class="small"
                v-else-if="data.child.categories && data.child.categories.length > 1"
                v-tooltip
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                :title="
                    data.child.categories
                        .slice(1)
                        .map((category) => category.name)
                        .join(', ')
                "
                style="cursor: pointer; width: 150px !important"
            >
                {{ data.child.categories[0].name }}
                <span class="color-purple-dark fw-semibold ms-1"> +{{ data.child.categories.length - 1 }} </span>
            </p>
        </template>
    </NewDatatable>
</template>
