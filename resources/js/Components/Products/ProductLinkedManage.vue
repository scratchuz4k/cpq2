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

const totals = ref(proxyValue.value.children?.length ?? 0);

const linkedProductQueries = () => {
    let childrenIds = (proxyValue.value.children ?? []).map((c) => c.id);
    let parentIds = (proxyValue.value.parents ?? []).map((p) => p.id);

    return [
        {
            group: 'parent',
            column: 'id',
            operator: FilterOperators.NOT_IN,
            value: childrenIds,
            function: 'orWhere'
        },
        ...(parentIds.length > 0
            ? [
                  {
                      group: 'exclude-parents',
                      column: 'id',
                      operator: FilterOperators.NOT_IN,
                      value: parentIds
                  }
              ]
            : [])
    ];
};

const sharesStep = (item) => {
    return (
        item.steps.some((s) => proxyValue.value.steps?.some((ps) => ps.id === s.id)) ||
        proxyValue.value.children.some((c) => c.id == item.id)
    );
};

const optionLinks = new DatatableOptions('product-links', 'product-links');
optionLinks.addColumn('child.sku', 'SKU', null, { isSticky: true });
optionLinks.addColumn('child.name', 'Name', null, { isSticky: true });
optionLinks.addColumn('child.description', 'Description', null, {});
optionLinks.addColumn('child.categories.name', 'Categories', {}, {});
optionLinks.addColumn('child.status', 'Status', null, {});
optionLinks.addColumn('child.price.cost', 'Cost Price', { width: '150px' }, { isSearchable: false });
optionLinks.addColumn('child.price.net', 'Net Price', { width: '150px' }, { isSearchable: false });
optionLinks.addColumn('child.price.net_spa', 'Net SPA', { width: '150px' }, { isSearchable: false });
optionLinks.addColumn(
    'child.price.active_spas',
    'Active SPAs',
    { width: '350px' },
    { isSearchable: false, isSortable: false }
);
optionLinks.addColumn('child.price_plan.name', 'Contract Term', {}, {});
optionLinks.addColumn('child.steps.name', 'Steps', { width: '150px' }, {});
optionLinks.addColumn('child.steps.solution_type.name', 'Solution Type', {}, {});

optionLinks.addQuery('product_id', FilterOperators.EQUALS, proxyValue.value.id);
optionLinks.setAllowedActions({
    destroy: true,
    allowExport: can(`export product`) || hasRole('Super Admin')
});

const getProductChildren = async () => {
    const res = await axios.get(`/api/products/${proxyValue.value.id}`);
    proxyValue.value.children = res.data.data.children;
};

const setMeta = (newTotals) => {
    totals.value = newTotals;
    getProductChildren();
};
</script>
<template>
    <div class="d-flex justify-content-between my-3">
        <div>
            <h3>Products ({{ totals }})</h3>
        </div>
        <div>
            <ProductCreateRelation
                id="links"
                route="product-links"
                :product="proxyValue"
                column="child_id"
                warningMessage="Linked products can only be added to the main product if they are configured in different steps."
                :queries="linkedProductQueries"
                :isRowDisabled="sharesStep"
                @product-added="getProductChildren"
            />
        </div>
    </div>
    <NewDatatable :options="optionLinks" @update:meta="(meta) => setMeta(meta.total)">
        <template #child.status="{ data, value }">
            <div class="badge" :class="[value == 'Active' ? 'text-bg-success' : 'text-bg-secondary']">
                {{ value }}
            </div>
        </template>
        <template #child.price.cost="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>
        <template #child.price.net="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>
        <template #child.price.net_spa="{ data, value }">
            <p class="small">{{ value ? format.GBP(value) : 'N/A' }}</p>
        </template>

        <!-- <template #child.price.active_spas="{ data, value }">
            <p v-for="temp in value" class="small" style="width: 200px !important">
                {{ temp.rule.name }} - {{ temp.operation.code }}:
                {{ temp.percentage ? format.decimalPlaces(temp.percentage) + '%' : format.GBP(temp.value) }}
            </p>
        </template> -->
        <template #child.price.active_spas="{ data, value }">
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
                <span class="color-purple-dark fw-semibold ms-1">+{{ data.child.categories.length - 1 }}</span>
            </p>
        </template>
    </NewDatatable>
</template>
