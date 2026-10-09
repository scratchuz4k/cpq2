<script setup>
import { computed, onUnmounted } from 'vue';
import { updateQuoteItem, removeQuoteItem, temp } from '@composables/useQuoteItem';
import { getQuote, quote } from '@/Composables/useQuote';
import { formatFrequency } from '@/Composables/billingFrequency';
import format from '@/Composables/format';

const props = defineProps({
    // 'one_off' | 'recurring'
    code: String,
    index: Number,
    // read-only customer view (preview / shared link / PDF): no margin, cost price or actions
    preview: Boolean
});
const item = defineModel('item');

const isRecurring = computed(() => props.code == 'recurring');

// connectivity details, shown in the preview as a sub row (same as bundle items)
const connectivityDetails = computed(() => {
    let data = item.value.data;
    if (!data || (data.speed == null && data.bearer == null && !data.connectivitySupplierGroup)) {
        return [];
    }

    return [
        { label: 'Provider', value: data.providerName ?? data.providerKey },
        { label: 'Subprovider', value: data.connectivitySupplierGroup },
        { label: 'Speed', value: data.speed != null ? format.speed(data.speed) : null },
        { label: 'Bearer', value: data.bearer != null ? format.speed(data.bearer) : null },
        { label: 'Quote Reference', value: quote.data?.quoteReference ?? data.quoteId }
    ].filter((x) => x.value);
});

const mainProduct = computed(() => {
    if (!item.value.data || item.value.data?.type === 'main_product') {
        return null;
    }
    return quote.items.find((x) => x.group === item.value.group && x.data?.type === 'main_product') || null;
});

const edit = () => {
    temp.quantity = item.value.quantity;
    temp.discount = item.value.discount;
    emitter.emit('quote-items:toggle:is-editing');
    item.value.is_editing = true;
};

const save = async () => {
    let data = {
        quantity: temp.quantity,
        discount: temp.discount
    };

    if (await updateQuoteItem(item.value.id, data)) {
        await getQuote(quote.id);
        item.value.is_editing = false;
    }
};

const cancel = () => {
    item.value.is_editing = false;
};

const remove = async () => {
    let items = quote.items.filter((x) => x.group == item.value.group);
    let result = await swal.fire(
        item.value.data?.type === 'main_product' && items.length > 1
            ? {
                  width: '400px',
                  title: 'Are you sure?',
                  text: 'This item includes other associated items, removing it will also remove them from the quote.'
              }
            : { title: 'Are you sure?' }
    );

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }
    await removeQuoteItem(item.value.id);
    await getQuote(quote.id);
};

const actions = [
    { id: `btn-${props.code}-${props.index}-edit`, label: 'Edit', icon: ['far', 'pen-to-square'], onClick: edit },
    {
        id: `btn-${props.code}-${props.index}-remove`,
        label: 'Delete',
        icon: ['far', 'trash-can'],
        danger: true,
        onClick: remove
    }
];

const stopEditing = () => (item.value.is_editing = false);
emitter.on('quote-items:toggle:is-editing', stopEditing);
onUnmounted(() => emitter.off('quote-items:toggle:is-editing', stopEditing));
</script>
<template>
    <tr :class="['item-row', code + '-item', !preview && !item.is_margin_valid ? 'red-bg' : '']">
        <td class="column-name-body">
            <div class="d-flex align-items-center gap-2">
                <span class="product-name" :title="item.name">{{ item.name }}</span>
                <span
                    v-if="item.data?.additionalInfo?.router"
                    v-tooltip
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-html="true"
                    :data-bs-original-title="'<b>Primary Router Model:</b> ' + item.data.additionalInfo['router']"
                    class="badge border border-secondary text-secondary opacity-75 lh-sm router-model"
                >
                    <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                </span>
                <span
                    v-if="item.data?.additionalInfo?.['Type'] == 'Fibre Flex 200+'"
                    v-tooltip
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-html="true"
                    data-bs-original-title="<b>Burstable Service:</b> This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                    class="badge border border-secondary text-secondary opacity-75 lh-sm is-burstable"
                >
                    <font-awesome-icon :icon="['fas', 'bolt']"></font-awesome-icon>
                </span>
                <span
                    v-if="item.data?.additionalInfo?.['addon_ipv4']"
                    v-tooltip
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-html="true"
                    :data-bs-original-title="item.data?.additionalInfo?.['addon_ipv4']"
                    class="badge border border-secondary text-secondary opacity-75 lh-sm addon-ipv4"
                >
                    <font-awesome-icon :icon="['fas', 'network-wired']"></font-awesome-icon>
                </span>
                <span v-if="item.data?.bundle_items?.length > 0" class="badge badge-bundle">Bundle</span>
            </div>
            <p v-if="mainProduct" class="add-on-reference" :title="mainProduct.name">
                <span>Add-on of:</span> {{ mainProduct.name }}
            </p>
        </td>
        <td class="column-discount-body">
            <p v-if="preview" class="small" :id="`${code}-discount-${index}`">{{ item.discount ?? 0 }}%</p>
            <DiscountColumn v-else :column="{ value: 'discount' }" v-model="item" :index="index" :code="code" />
        </td>
        <td v-if="!preview" class="column-margin-body" :id="`${code}-${index}-margin`">
            {{ format.GBP(item.margin) }}
        </td>
        <td class="column-price-body" :id="`${code}-${index}-price`">
            <template v-if="preview">{{ format.GBP(item.original_price) }}</template>
            <HoverTooltip v-else :text="`Cost price: ${format.GBP(item.raw_price)}`">
                {{ format.GBP(item.original_price) }}
            </HoverTooltip>
        </td>
        <td class="column-quantity-body">
            <p v-if="preview" class="small" :id="`${code}-${index}-quantity`">{{ item.quantity }}</p>
            <QuantityColumn v-else :column="{ value: 'quantity' }" v-model="item" :index="index" :code="code" />
        </td>
        <template v-if="isRecurring">
            <td class="column-billing_frequency-body">
                <span
                    v-if="item.billing_frequency_value"
                    class="frequency-pill"
                    :class="`frequency-pill-${item.billing_frequency_unit?.toLowerCase()}`"
                >
                    {{ formatFrequency(item.billing_frequency_value, item.billing_frequency_unit) }}
                </span>
            </td>
            <td class="column-renewal_terms-body">
                <span
                    v-if="item.renewal_terms_value"
                    class="frequency-pill"
                    :class="`frequency-pill-${item.renewal_terms_unit?.toLowerCase()}`"
                >
                    {{ formatFrequency(item.renewal_terms_value, item.renewal_terms_unit) }}
                </span>
            </td>
        </template>
        <td class="column-total-body" :id="`${code}-total-${index}`">
            <template v-if="preview">{{ format.GBP(item.quantity * item.price) }}</template>
            <HoverTooltip v-else :text="isRecurring ? `Overall cost: ${format.GBP(item.total)}` : null">
                {{ format.GBP(item.quantity * item.price) }}
            </HoverTooltip>
        </td>
        <td v-if="!preview" class="column-actions-body">
            <template v-if="!quote.is_blocked">
                <div v-if="item.is_editing" class="d-flex justify-content-center">
                    <font-awesome-icon
                        :id="`btn-${code}-${index}-cancel`"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'xmark']"
                        title="Cancel"
                        @click="cancel()"
                    />
                    <font-awesome-icon
                        :id="`btn-${code}-${index}-save`"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'check']"
                        title="Save"
                        @click="save()"
                    />
                </div>
                <KebabMenu v-else :id="`btn-${code}-${index}-actions`" :actions="actions" />
            </template>
        </td>
    </tr>
    <template v-if="preview">
        <tr v-if="connectivityDetails.length" class="child-bg connectivity-details">
            <td colspan="100%">
                <p class="small ps-2">
                    <span
                        v-for="detail in connectivityDetails"
                        :key="detail.label"
                        class="d-inline-block text-nowrap me-4"
                    >
                        <b>{{ detail.label }}:</b> {{ detail.value }}
                    </span>
                </p>
            </td>
        </tr>
        <tr v-for="(bundleItem, bundleIndex) in item.data?.bundle_items ?? []" :key="bundleIndex" class="child-bg">
            <td colspan="100%">
                <p class="small ps-2">{{ bundleItem.name }} x{{ bundleItem.quantity }}</p>
            </td>
        </tr>
    </template>
</template>
