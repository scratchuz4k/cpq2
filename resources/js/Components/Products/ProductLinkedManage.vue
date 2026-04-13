<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import { FilterOperators } from '@composables/useApi';
import ProductCreateRelation from './ProductCreateRelation.vue';
import { can, hasRole } from '@composables/useAuth';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
    queries: Array
});

const totals = ref(props.product.children?.length ?? 0);

const parentIds = (props.product.parents ?? []).map((p) => p.id);

const linkedProductQueries = [
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

const sharesStep = (item) => item.steps.some((s) => props.product.steps?.some((ps) => ps.id === s.id));

const optionLinks = new DatatableOptions('product-links', 'product-links');
optionLinks.addColumn('child.sku', 'SKU', null, { isSticky: true });
optionLinks.addColumn('child.name', 'Name', null, { isSticky: true });
optionLinks.addColumn('child.description', 'Description', null, {});
optionLinks.addColumn('child.categories.name', 'Categories', {}, {});
optionLinks.addColumn('child.status', 'Status', null, {});
optionLinks.addColumn('child.price.cost', 'Cost Price', { width: '150px' }, { isSearchable: false });
optionLinks.addColumn('child.price.net', 'Net Price', { width: '150px' }, { isSearchable: false });
optionLinks.addColumn('child.price.net_spa', 'Net SPA', { width: '150px' }, { isSearchable: false });
optionLinks.addColumn('child.price.active_spas', 'Active SPAs', { width: '350px' }, { isSearchable: false });
optionLinks.addColumn('child.steps.name', 'Steps', { width: '150px' }, {});
optionLinks.addColumn('child.steps.solution_type.name', 'Solution Type', { width: '250px' }, {});

optionLinks.addQuery('product_id', FilterOperators.EQUALS, props.product.id);
optionLinks.setAllowedActions({
    destroy: true,
    allowExport: can(`export product`) || hasRole('Super Admin')
});
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
                :product="props.product"
                column="child_id"
                :queries="linkedProductQueries"
                warningMessage="Linked products can only be added to the main product if they are configured in different steps."
                :isRowDisabled="sharesStep"
            />
        </div>
    </div>
    <NewDatatable :options="optionLinks" @update:meta="(meta) => (totals = meta.total)">
        <template #child.status="{ data, value }">
            <div class="badge" :class="[value == 'Active' ? 'text-bg-success' : 'text-bg-secondary']">
                {{ value }}
            </div>
        </template>
        <template #child.price.cost="{ data, value }">
            {{ value ? format.GBP(value) : 'N/A' }}
        </template>
        <template #child.price.net="{ data, value }">
            {{ value ? format.GBP(value) : 'N/A' }}
        </template>
        <template #child.price.net_spa="{ data, value }">
            {{ value ? format.GBP(value) : 'N/A' }}
        </template>
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
                                    : format.GBP(category.margin))
                        )
                        .join(', ')
                "
                style="cursor: pointer; width: 250px !important"
            >
                {{ value[0].rule.name }} - {{ value[0].operation.code }}:
                {{
                    value[0].percentage ? format.decimalPlaces(value[0].percentage) + '%' : format.GBP(value[0].margin)
                }}
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
