<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { getQuote, quote } from '@composables/useQuote';
import { can, hasRole } from '@/Composables/useAuth';
import { checkValue } from '@/Composables/input.js';

const props = defineProps({
    preview: Boolean,
    pricePlan: Object,
    columns: {
        type: Array,
        default: () => []
    }
});

const discountIndex = () => {
    let index = props.columns.findIndex((column) => column.value == 'discount');
    return index == -1 ? props.columns.length - 1 : index;
};

const labelColspan = () => discountIndex();
const valueColspan = () => Math.max(props.columns.length - discountIndex() - 1, 1);

const isEditing = ref(false);
const isLoading = ref(false);

const discount = reactive({
    id: null,
    quote_id: quote.id,
    code: props.pricePlan.code,
    discount: 0,
    entity: 'price_plans',
    entity_id: props.pricePlan.id,
    total: 0,
    value: 0
});

const temp = ref(0);

const edit = () => {
    isEditing.value = true;
};

const cancel = () => {
    isEditing.value = false;

    let tempDiscount = quote.discounts.find((x) => x.entity?.code == props.pricePlan.code);
    temp.value = tempDiscount ? tempDiscount.discount : 0;
};

const save = async () => {
    isLoading.value = true;

    try {
        if (discount.id == null) {
            let res = await axios.post(`/api/quote-discounts`, { ...discount, discount: temp.value });
        } else {
            let res = await axios.put(`/api/quote-discounts/${discount.id}`, { ...discount, discount: temp.value });
        }

        await getQuote(quote.id);
        isEditing.value = false;
        isLoading.value = false;
        init();
    } catch (ex) {
        isLoading.value = false;
        notyf.error(ex.response.data.message);
        isLoading.value = false;
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
            let res = await axios.delete(`/api/quote-discounts/${discount.id}`);
            await getQuote(quote.id);
            init();
            notyf.success('Removed with success');
            return;
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

    let tempDiscount = quote.discounts.find((x) => x.entity?.code == props.pricePlan.code);
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
    <tr v-if="!props.preview || discount.discount">
        <td :colspan="labelColspan()">
            <p class="small text-primary">{{ props.pricePlan.name }} Discount</p>
        </td>
        <td class="text-center">
            <p class="small text-primary" v-if="!isEditing" :id="`global-${props.pricePlan.code}-discount`">
                {{ discount.discount }} %
            </p>
            <input
                v-else
                :id="`input-global-${props.pricePlan.code}-discount`"
                class="form-control text-center p-0 px-1"
                type="number"
                v-model="temp"
                min="0"
                max="100"
                @input="checkValue"
            />
        </td>
        <td :colspan="valueColspan()" class="text-right text-primary bold">
            <p v-if="props.preview || props.pricePlan.name != 'Recurring'" class="small column_total">
                -{{ format.GBP(discount.value) }}
            </p>

            <div v-else class="column_total cpq-tooltip">
                -{{ format.GBP(discount.value) }}
                <span class="cpq-tooltip-text"> Overall Discount:<br />- {{ format.GBP(discount.total) }} </span>
            </div>
        </td>
        <td v-if="!props.preview && !quote.is_blocked">
            <div class="d-flex justify-content-start">
                <template v-if="!isEditing">
                    <font-awesome-icon
                        :id="'btn-' + props.pricePlan.code + '-remove'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'trash-alt']"
                        v-if="can('delete quote_discount') || hasRole('Super Admin')"
                        @click="destroy()"
                    />
                    <font-awesome-icon
                        :id="'btn-' + props.pricePlan.code + '-edit'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'edit']"
                        v-if="can('update quote_discount') || hasRole('Super Admin')"
                        @click="edit()"
                    />
                </template>
                <template v-else>
                    <font-awesome-icon
                        :id="'btn-' + props.pricePlan.code + '-cancel'"
                        role="button"
                        class="px-2 py-2 text-primary"
                        :icon="['fas', 'cancel']"
                        @click="cancel()"
                    />
                    <font-awesome-icon
                        :id="'btn-' + props.pricePlan.code + '-save'"
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
