<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { getQuote, quote } from '@composables/useQuote';
import { can, hasRole } from '@/Composables/useAuth';
import { checkValue } from '@/Composables/input.js';

const props = defineProps({
    preview: Boolean,
    isSimpleView: Boolean,
    // { unit: 'YEARS' | 'MONTHS' | 'DAYS', name: 'Annual' | 'Monthly' | 'Daily' }
    billingFrequency: Object,
    // recurring items billed in this billing frequency
    items: {
        type: Array,
        default: () => []
    }
});

const unit = props.billingFrequency.unit;
const pricePlan = computed(() => props.items[0]?.price_plan);

const isEditing = ref(false);
const isLoading = ref(false);
const temp = ref(0);

const discount = reactive({
    id: null,
    quote_id: quote.id,
    discount: 0,
    entity: 'price_plans',
    billing_frequency_unit: unit,
    total: 0,
    value: 0
});

const canViewDiscount = computed(
    () =>
        !props.isSimpleView &&
        (can('viewAny quote_discount') || hasRole('Super Admin')) &&
        (!props.preview || discount.discount)
);

// Recurring discount (charges table) applies first, then this billing frequency discount
const total = computed(() => {
    let gross = props.items.reduce((acc, item) => (acc += item.total), 0);
    let recurring = quote.discounts.find((x) => x.entity?.code == pricePlan.value?.code && !x.billing_frequency_unit);

    return gross * (1 - (recurring?.discount ?? 0) / 100) - discount.total;
});

const edit = () => {
    isEditing.value = true;
};

const cancel = () => {
    isEditing.value = false;
    temp.value = discount.discount;
};

const save = async () => {
    isLoading.value = true;

    let payload = {
        ...discount,
        code: pricePlan.value.code,
        entity_id: pricePlan.value.id,
        discount: temp.value
    };

    try {
        if (discount.id == null) {
            await axios.post(`/api/quote-discounts`, payload);
        } else {
            await axios.put(`/api/quote-discounts/${discount.id}`, payload);
        }

        await getQuote(quote.id);
        isEditing.value = false;
        isLoading.value = false;
        init();
    } catch (ex) {
        isLoading.value = false;
        notyf.error(ex.response.data.message);
    }
};

const destroy = async () => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return;
        }
        if (!discount.id) {
            return;
        }
        try {
            await axios.delete(`/api/quote-discounts/${discount.id}`);
            await getQuote(quote.id);
            init();
            notyf.success('Removed with success');
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        }
    });
};

const init = () => {
    discount.id = null;
    discount.discount = 0;
    discount.total = 0;
    discount.value = 0;
    temp.value = 0;

    let tempDiscount = quote.discounts.find(
        (x) => x.entity?.code == pricePlan.value?.code && x.billing_frequency_unit == unit
    );
    if (tempDiscount) {
        discount.id = tempDiscount.id;
        discount.discount = tempDiscount.discount;
        discount.total = tempDiscount.total;
        discount.value = tempDiscount.value;
        temp.value = tempDiscount.discount;
    }
};

onMounted(() => {
    init();
});

watch(
    () => [quote.discounts],
    () => {
        init();
    }
);
</script>
<template>
    <tr>
        <td>
            <p class="small text-start">{{ props.billingFrequency.name }} Recurring Total</p>
        </td>
        <td>
            <template v-if="canViewDiscount">
                <p class="small text-center text-primary" v-if="!isEditing" :id="`global-${unit}-discount`">
                    {{ discount.discount }} %
                </p>
                <input
                    v-else
                    :id="`input-global-${unit}-discount`"
                    class="form-control text-center p-0 px-1"
                    type="number"
                    v-model="temp"
                    min="0"
                    max="100"
                    @input="checkValue"
                />
            </template>
        </td>
        <td class="bold" :id="`total_${unit}`">
            <span v-if="props.preview || !discount.total">{{ format.GBP(total) }}</span>
            <div v-else class="cpq-tooltip">
                {{ format.GBP(total) }}
                <span class="cpq-tooltip-text">
                    {{ props.billingFrequency.name }} Discount:<br />- {{ format.GBP(discount.total) }}
                </span>
            </div>
        </td>
        <td v-if="!props.preview">
            <div class="d-flex justify-content-end" v-if="canViewDiscount && !quote.is_blocked">
                <template v-if="!isEditing">
                    <font-awesome-icon
                        :id="'btn-' + unit + '-discount-remove'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'trash-alt']"
                        v-if="discount.id && (can('delete quote_discount') || hasRole('Super Admin'))"
                        @click="destroy()"
                    />
                    <font-awesome-icon
                        :id="'btn-' + unit + '-discount-edit'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'edit']"
                        v-if="can('update quote_discount') || hasRole('Super Admin')"
                        @click="edit()"
                    />
                </template>
                <template v-else>
                    <font-awesome-icon
                        :id="'btn-' + unit + '-discount-cancel'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'cancel']"
                        @click="cancel()"
                    />
                    <font-awesome-icon
                        :id="'btn-' + unit + '-discount-save'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'save']"
                        @click="save()"
                    />
                </template>
            </div>
        </td>
    </tr>
</template>
