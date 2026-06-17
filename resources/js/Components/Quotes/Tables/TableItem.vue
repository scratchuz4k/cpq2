<script setup>
import { updateQuoteItem, removeQuoteItem, temp } from '@composables/useQuoteItem';
import { getQuote, quote } from '@/Composables/useQuote';
import format from '@/Composables/format';
import { ref } from 'vue';

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
const showBundleItems = ref(props.preview || false);

const toggleBundleItems = () => {
    showBundleItems.value = !showBundleItems.value;
};

const edit = (p) => {
    temp.quantity = p.quantity;
    temp.discount = p.discount;
    temp.contract_length = p.contract_length;
    emitter.emit('quote-items:toggle:is-editing');
    item.value.is_editing = true;
};

const save = async (p) => {
    let item = {
        quantity: temp.quantity,
        discount: temp.discount,
        contract_length: temp.contract_length
    };

    if (await updateQuoteItem(p.id, item)) {
        await getQuote(quote.id);
        p.is_editing = false;
    }
};

const remove = async (p) => {
    let result = null;
    let items = quote.items.filter((x) => x.group == p.group);
    if (p.data?.type === 'main_product' && items.length > 1) {
        result = await swal.fire({
            width: '400px',
            title: 'Are you sure?',
            text: 'This item includes other associated items, removing it will also remove them from the quote.'
        });
    } else {
        result = await swal.fire({
            title: 'Are you sure?'
        });
    }

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }
    await removeQuoteItem(p.id);
    await getQuote(quote.id);
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
    <tr
        :class="['item-row', props.code + '-item', isChild ? 'child-bg' : '', !item.is_margin_valid ? 'red-bg' : '']"
        :style="{ 'vertical-align': 'middle' }"
    >
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
                <template v-if="!quote.is_blocked">
                    <template v-if="!item.is_editing">
                        <font-awesome-icon
                            :id="'btn-' + props.code + '-' + index + '-remove'"
                            role="button"
                            class="px-2 py-2 text-primary"
                            :icon="['fas', 'trash-alt']"
                            @click="remove(item)"
                        />
                        <font-awesome-icon
                            :id="'btn-' + props.code + '-' + index + '-edit'"
                            role="button"
                            class="px-2 py-2 text-primary"
                            :icon="['fas', 'edit']"
                            @click="edit(item)"
                        />
                    </template>
                    <template v-else>
                        <font-awesome-icon
                            :id="'btn-' + props.code + '-' + index + '-cancel'"
                            role="button"
                            class="px-2 py-2 text-primary"
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
                </template>
                <div v-if="item.data?.bundle_items != null && !preview">
                    <font-awesome-icon
                        v-if="showBundleItems"
                        :id="'btn-' + props.code + '-' + index + '-save'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'chevron-up']"
                        @click="toggleBundleItems()"
                    />
                    <font-awesome-icon
                        v-else
                        :id="'btn-' + props.code + '-' + index + '-save'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'chevron-down']"
                        @click="toggleBundleItems()"
                    />
                </div>
            </div>
        </td>
    </tr>
    <template v-if="item.data?.bundle_items != null && showBundleItems">
        <tr v-for="(bundleItem, bundleIndex) in item.data.bundle_items" :key="bundleIndex" class="child-bg">
            <td colspan="100%">
                <p class="small ps-2">{{ bundleItem.name }} x{{ bundleItem.quantity }}</p>
            </td>
        </tr>
    </template>
</template>
<style>
.child-bg > td {
    background-color: whitesmoke !important;
}
.red-bg > td {
    background: #f5cccc !important;
}
.red-bg > td:nth-child(1) {
    padding-left: 0.625rem !important;
}
.red-bg > td:nth-child(1):before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    border-width: 0;
    width: 4px;
    height: 100%;
    background: #dc3545;
}
</style>
