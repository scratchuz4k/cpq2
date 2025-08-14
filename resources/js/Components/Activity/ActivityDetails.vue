<script setup>
import { hasRole } from '@composables/useAuth';
import format from '@composables/format';

const props = defineProps({
    item: Object
});

const fieldTypes = {
    total: 'price',
    margin: 'price',
    price: 'price',
    discount: 'percentage',
    quantity: 'integer',
    contract_length: 'integer',
    name: 'text',
    status: 'text'
};

const typeConfigs = {
    Quote: {
        fields: ['total', 'margin', 'status']
    },
    QuoteItem: {
        fields: ['name', 'quantity', 'contract_length', 'price', 'discount']
    },
    QuoteDiscount: {
        fields: ['discount']
    }
};

function formatValue(field, value) {
    switch (fieldTypes[field]) {
        case 'price':
            return format.GBP(value) ?? '0';
        case 'percentage':
            return (format.decimalPlaces(value) ?? '0') + '%';
        case 'integer':
            return value ?? '0';
        default:
            return value ?? 'null';
    }
}

function generateLabel(field, from, to, operation) {
    if (operation === 'updated') {
        return `<b class="text-muted">${format.friendlyString(field)}</b> changed from <i>${formatValue(field, from)}</i> to <i>${formatValue(field, to)}</i>`;
    } else {
        return `<b class="text-muted">${format.friendlyString(field)}</b> set to <i>${formatValue(field, to)}</i>`;
    }
}

function getTypeConfig(type) {
    return typeConfigs[type] || { fields: [] };
}

function getFilteredChanges(item) {
    const config = getTypeConfig(item.activitable_type);
    return item.data
        .filter((change) => config.fields.includes(change.field))
        .map((change) => ({
            ...change,
            label: generateLabel(change.field, change.from, change.to, item.operation)
        }));
}
</script>
<template>
    <ul class="my-1">
        <li v-for="change in getFilteredChanges(props.item)" v-html="change.label" class="small"></li>
    </ul>

    <div v-if="hasRole('Super Admin')">
        <table v-if="props.item.readmore" class="table table-activity mt-3">
            <thead>
                <tr>
                    <th style="width: 30%">Field</th>
                    <th style="width: 35%">From</th>
                    <th style="width: 35%">To</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in props.item.data.filter((item) => item.field != 'updated_at')" class="field-info">
                    <td class="">
                        <p class="small">{{ item.field }}</p>
                    </td>
                    <td>
                        <p class="small">{{ item.from ?? 'null' }}</p>
                    </td>
                    <td>
                        <p class="small">{{ item.to ?? 'null' }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <a
            class="text-secondary small"
            v-if="props.item.data?.length"
            role="button"
            @click="() => (props.item.readmore = !props.item.readmore)"
        >
            Show {{ !props.item.readmore ? 'more' : 'less' }}...
        </a>
    </div>
</template>
