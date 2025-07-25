<script setup>
import { updateQuoteItem, removeQuoteItem, temp } from '@composables/useQuoteItem';
import { getQuote, quote } from '@/Composables/useQuote';
import format from '@/Composables/format';

const props = defineProps({
    code: String,
    index: Number,
    showToggle: Boolean,
    isChild: Boolean,
    preview: Boolean,
    columns: Array,
    item: Object,
    groupColumns: {
        type: Array,
        default: []
    }
});
const item = defineModel('item');

const edit = (p) => {
    temp.quantity = p.quantity;
    temp.discount = p.discount;
    temp.contract_length = p.contract_length;
    emitter.emit('quote-items:toggle:is-editing');
    item.value.is_editing = true;
};

const save = async (p) => {
    item.value.quantity = temp.quantity;
    item.value.discount = temp.discount;
    item.value.contract_length = temp.contract_length;
    if (await updateQuoteItem(item.value.id, item.value)) {
        item.value.is_editing = false;
        emitter.emit('quote-items:updated');
    }
};

const remove = async (p) => {
    let items = quote.items.filter((x) => x.group == p.group);
    let result = null;
    if (items.length > 1) {
        result = await swal.fire({
            title: 'Are you sure?',
            text: 'This line contains other associated lines, removing it deletes all associated lines'
        });
    } else {
        result = await swal.fire({
            title: 'Are you sure?'
        });
    }

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }

    items.forEach(async (x) => {
        await removeQuoteItem(x.id);
    });
    emitter.emit('quote-items:updated');
};

const cancel = (p) => {
    p.is_editing = !p.is_editing;
};

const getComponent = (column) => {
    return format.toCamelCase(column.split('.')[0]) + 'Column';
};

emitter.on('quote-items:toggle:is-editing', () => (item.value.is_editing = false));
</script>
<template>
    <tr :class="['item-row', props.code + '-item', isChild ? 'child-bg' : '']" :style="{ 'vertical-align': 'middle' }">
        <template v-for="column in columns">
            <td v-if="!groupColumns.find((x) => x.value == column.value)" :class="[`column-${column.value}-body`]">
                <component
                    :is="getComponent(column.value)"
                    v-bind="{
                        column: column,
                        modelValue: item,
                        isGroup: false,
                        preview: preview,
                        index: index,
                        code: props.code
                    }"
                >
                </component>
            </td>
        </template>
        <td v-if="!preview" class="text-center p-0">
            <div class="d-flex justify-content-end">
                <template v-if="!item.is_editing">
                    <font-awesome-icon
                        :id="'btn-' + props.code + '-' + index + '-edit'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'edit']"
                        @click="edit(item)"
                    />
                    <font-awesome-icon
                        :id="'btn-' + props.code + '-' + index + '-remove'"
                        role="button"
                        class="px-2 py-2 text-secondary"
                        :icon="['fas', 'trash']"
                        @click="remove(item)"
                    />
                </template>
                <template v-else>
                    <font-awesome-icon
                        :id="'btn-' + props.code + '-' + index + '-cancel'"
                        role="button"
                        class="px-2 py-2 text-secondary"
                        :icon="['fas', 'cancel']"
                        @click="cancel(item)"
                    />
                    <font-awesome-icon
                        :id="'btn-' + props.code + '-' + index + '-save'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'save']"
                        @click="save(item)"
                    />
                </template>
            </div>
        </td>
    </tr>
</template>
<style>
.child-bg > td {
    background-color: whitesmoke !important;
}
</style>
