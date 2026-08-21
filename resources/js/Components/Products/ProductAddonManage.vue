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

const totals = ref(proxyValue.value.addons_children?.length ?? 0);
const optionAddons = new DatatableOptions('product-addons', 'product-addons');
optionAddons.addColumn('addon.sku', 'SKU');
optionAddons.addColumn('addon.name', 'Name', { isSticky: true });
optionAddons.addColumn('addon.description', 'Description');
optionAddons.addColumn('addon.categories.name', 'Categories');
optionAddons.addColumn('addon.status', 'Status');
optionAddons.addColumn('addon.price.cost', 'Cost Price', { style: { width: '150px' }, isSearchable: false });
optionAddons.addColumn('addon.price.net', 'Net Price', { style: { width: '150px' }, isSearchable: false });
optionAddons.addColumn('addon.price.net_spa', 'Final Sell Price', { style: { width: '150px' }, isSearchable: false });
optionAddons.addColumn('addon.price.active_spas', 'Active SPAs', {
    style: { width: '350px' },
    isSearchable: false,
    isSortable: false
});
optionAddons.addColumn('addon.price_plan.name', 'Contract Term');
optionAddons.addColumn('addon.steps.name', 'Steps', { style: { width: '150px' } });
optionAddons.addColumn('addon.steps.solution_type.name', 'Solution Type', { style: { width: '250px' } });

optionAddons.addQuery('product_id', FilterOperators.EQUALS, proxyValue.value.id);
optionAddons.setAllowedActions({
    destroy: true,
    allowExport: can(`export product`) || hasRole('Super Admin')
});
const addonProductQueries = () => {
    const existingAddonIds = (proxyValue.value.addons_children ?? []).map((addon) => addon.id);

    if (existingAddonIds.length === 0) {
        return [];
    }

    return [
        {
            group: 'parent',
            column: 'id',
            operator: FilterOperators.NOT_IN,
            value: existingAddonIds
        }
    ];
};

const getProductAddons = async () => {
    const res = await axios.get(`/api/products/${proxyValue.value.id}`);
    proxyValue.value.addons_children = res.data.data.addons_children;
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
