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

const totals = ref(proxyValue.value.bundles?.length ?? 0);
const optionBundle = new DatatableOptions('product-bundles', 'product-bundles');
optionBundle.addColumn('item.sku', 'SKU', null, { isSticky: true });
optionBundle.addColumn('item.name', 'Name', null, { isSticky: true });
optionBundle.addColumn('item.description', 'Description', null, {});
optionBundle.addColumn('item.categories.name', 'Categories', {}, {});
optionBundle.addColumn('item.status', 'Status', null, {});
optionBundle.addColumn('item.price.cost', 'Cost Price', { width: '150px' }, { isSearchable: false });
optionBundle.addColumn('item.price.net', 'Net Price', { width: '150px' }, { isSearchable: false });
optionBundle.addColumn('item.price.net_spa', 'Net SPA', { width: '150px' }, { isSearchable: false });
optionBundle.addColumn(
    'item.price.active_spas',
    'Active SPAs',
    { width: '350px' },
    { isSearchable: false, isSortable: false }
);
optionBundle.addColumn('item.price_plan.name', 'Contract Term', {}, {});
optionBundle.addColumn('item.steps.name', 'Steps', { width: '150px' }, {});
optionBundle.addColumn('item.steps.solution_type.name', 'Solution Type', { width: '250px' }, {});

optionBundle.addQuery('product_id', FilterOperators.EQUALS, proxyValue.value.id);
optionBundle.setAllowedActions({
    destroy: true,
    allowExport: can(`export product`) || hasRole('Super Admin')
});

const bundleProductQueries = () => {
    return [
        {
            group: 'parent',
            column: 'id',
            operator: FilterOperators.NOT_IN,
            value: proxyValue.value.bundles.map((bundle) => bundle.id)
        }
    ];
};

const getProductBundles = async () => {
    const res = await axios.get(`/api/products/${proxyValue.value.id}`);
    proxyValue.value.bundles = res.data.data.bundles;
};
const setMeta = (newTotals) => {
    totals.value = newTotals;
    getProductBundles();
};
</script>

<template>
    <div class="d-flex justify-content-between my-3">
        <div>
            <h3>Bundle ({{ totals }})</h3>
        </div>
        <div>
            <ProductCreateRelation
                id="bundles"
                route="product-bundles"
                :product="proxyValue"
                column="item_id"
                :queries="bundleProductQueries"
                @product-added="getProductBundles()"
            />
        </div>
    </div>
    <NewDatatable :options="optionBundle" @update:meta="(meta) => setMeta(meta.total)">
        <template #item.status="{ data, value }">
            <span
                class="badge"
                :class="[value !== 'Active' ? (value == 'Inactive' ? 'badge-danger' : 'badge-muted') : 'badge-success']"
            >
                {{ value }}
            </span>
        </template>
        <template #item.price.cost="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>
        <template #item.price.net="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>
        <template #item.price.net_spa="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>

        <template #item.price.active_spas="{ data, value }">
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
        <template #item.categories.name="{ data, value }">
            <p
                class="small"
                v-if="data.item.categories && data.item.categories.length == 1"
                style="width: 150px !important"
            >
                {{ data.item.categories[0].name }}
            </p>
            <p
                class="small"
                v-else-if="data.item.categories && data.item.categories.length > 1"
                v-tooltip
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                :title="
                    data.item.categories
                        .slice(1)
                        .map((category) => category.name)
                        .join(', ')
                "
                style="cursor: pointer; width: 150px !important"
            >
                {{ data.item.categories[0].name }}
                <span class="color-purple-dark fw-semibold ms-1"> +{{ data.item.categories.length - 1 }} </span>
            </p>
        </template>
    </NewDatatable>
</template>
