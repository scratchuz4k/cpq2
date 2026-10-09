<script setup>
import { computed } from 'vue';
import { quote } from '@composables/useQuote';
import { checkValue } from '@/Composables/input.js';
import {
    useQuoteDiscount,
    canViewDiscounts,
    canUpdateDiscounts,
    canDeleteDiscounts
} from '@/Composables/useQuoteDiscount';
import format from '@/Composables/format';

const props = defineProps({
    // unique key used for element ids, e.g. YEARS, one_off, quote
    code: String,
    label: String,
    // returns the existing discount from quote.discounts
    find: Function,
    // base payload for creating / updating the discount
    payload: Function,
    // (discount) => discount value shown in the "Discount value" column
    discountValue: Function,
    // (discount) => row total
    total: Function,
    // read-only customer view (preview / shared link / PDF): discounts always shown, no actions
    preview: Boolean
});

const { discount, isEditing, isLoading, temp, edit, cancel, save, destroy } = useQuoteDiscount(
    props.find,
    props.payload
);

const showDiscount = computed(() => props.preview || canViewDiscounts());

const actions = computed(() => {
    let list = [];
    if (canUpdateDiscounts() && quote.items?.length) {
        list.push({
            id: `btn-${props.code}-discount-edit`,
            label: 'Edit discount',
            icon: ['far', 'pen-to-square'],
            onClick: edit
        });
    }
    if (discount.value && canDeleteDiscounts()) {
        list.push({
            id: `btn-${props.code}-discount-remove`,
            label: 'Delete discount',
            icon: ['far', 'trash-can'],
            danger: true,
            onClick: destroy
        });
    }
    return list;
});
</script>
<template>
    <tr>
        <td class="column-name-body">
            <slot name="label">{{ label }}</slot>
        </td>
        <template v-if="showDiscount">
            <td class="column-discount-body">
                <p class="small" v-if="!isEditing" :id="`${code}-discount`">{{ discount?.discount ?? 0 }}%</p>
                <input
                    v-else
                    :id="`input-${code}-discount`"
                    class="form-control text-center p-0 px-1"
                    type="number"
                    v-model="temp"
                    min="0"
                    max="100"
                    @input="checkValue"
                    @keyup.enter="save()"
                />
            </td>
            <td class="column-discount-value-body discount-value" :id="`${code}-discount-value`">
                -{{ format.GBP(discountValue(discount)) }}
            </td>
        </template>
        <td class="column-total-body" :id="`${code}-total`">{{ format.GBP(total(discount)) }}</td>
        <td v-if="!preview" class="column-actions-body">
            <template v-if="showDiscount && !quote.is_blocked">
                <div v-if="isEditing" class="d-flex justify-content-center">
                    <font-awesome-icon
                        :id="`btn-${code}-discount-cancel`"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'xmark']"
                        title="Cancel"
                        @click="cancel()"
                    />
                    <font-awesome-icon
                        :id="`btn-${code}-discount-save`"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :class="{ 'pe-none opacity-50': isLoading }"
                        :icon="['fas', 'check']"
                        title="Save"
                        @click="save()"
                    />
                </div>
                <KebabMenu v-else-if="actions.length" :id="`btn-${code}-discount-actions`" :actions="actions" />
            </template>
        </td>
    </tr>
</template>
