<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { quote, getQuote } from '@composables/useQuote';
import { can, hasRole } from '@/Composables/useAuth';

const props = defineProps({
    preview: Boolean,
    isSimpleView: Boolean
});
const isEditing = ref(false);
const isLoading = ref(false);
const group = quote.solution_type?.table?.group ?? null;
const pricePlanskeys = { 'One-Off': 'SINGLE', Recurring: 'MONTHLY' };

const discount = reactive({
    id: null,
    quote_id: quote.id,
    code: 'quote-' + quote.id,
    discount: 0,
    entity: 'quote',
    entity_id: quote.id,
    total: 0,
    value: 0
});

const temp = ref(0);

const edit = () => {
    isEditing.value = true;
};

const cancel = () => {
    isEditing.value = false;
    let tempDiscount = quote.discounts.find((x) => x.entity?.uuid == quote.uuid);
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

const quoteTotalByPricePlan = (price_plan, column = 'total') => {
    let total = 0;
    let items = quote.items;

    if (group) {
        items = quote.items.flatMap((obj) => obj.items ?? obj);
    }

    items.forEach((e) => {
        if (e.children?.lenght) {
            total += e.children
                .filter((x) => x.price_plan && x.price_plan.code == pricePlanskeys[price_plan])
                .reduce((acc, item) => (acc += item[column]), 0);
        } else if (e.price_plan && e.price_plan.code == pricePlanskeys[price_plan]) {
            total += e[column];
        }
    });

    let tempDiscount = quote.discounts.find((x) => x.entity?.code == pricePlanskeys[price_plan]);

    return total ? total - (tempDiscount?.total ?? 0) : 0;
};

const quoteTotal = () => {
    let items = quote.items;

    if (group) {
        items = quote.items.flatMap((obj) => obj.items ?? obj);
    }

    let total = quote.items.reduce((acc, item) => (acc += item.quantity * item.price * item.contract_length), 0);
    let tempDiscount = quote.discounts
        .filter((x) => x.entity?.uuid == quote.uuid)
        .reduce((acc, item) => (acc += item.total), 0);

    return total ? total - tempDiscount : 0;
};

const init = () => {
    discount.id = null;
    discount.discount = 0;
    discount.total = 0;
    discount.value = 0;
    temp.value = 0;

    let tempDiscount = quote.discounts.find((x) => x.entity?.uuid == quote.uuid);
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
    <div class="mobile-price-group-heading d-flex justify-content-between align-items-center">
        <h3>Overall Totals</h3>
    </div>
    <table class="table-dealmaker table table-bordered text-end">
        <colgroup>
            <col style="width: 70%" class="text-start" />
            <col style="width: 9%" />
            <col style="width: 20%" />
            <col v-if="!props.preview" style="width: 60px" class="text-center" />
        </colgroup>
        <tbody>
            <tr>
                <td>
                    <p class="small text-start">Recurring Total</p>
                </td>
                <td></td>
                <td class="bold">
                    {{ format.GBP(quoteTotalByPricePlan('Recurring')) }}
                </td>
                <td v-if="!props.preview"></td>
            </tr>
            <tr>
                <td>
                    <p class="small text-start">One Off Total</p>
                </td>
                <td></td>
                <td id="total_net" class="bold">
                    {{ format.GBP(quoteTotalByPricePlan('One-Off')) }}
                </td>
                <td v-if="!props.preview"></td>
            </tr>
            <tr
                v-if="
                    !isSimpleView &&
                    (can('viewAny quote_discount') || hasRole('Super Admin')) &&
                    (!props.preview || discount.discount)
                "
            >
                <td class="column-discount-body">
                    <p class="small text-start">Overall Discount</p>
                </td>
                <td>
                    <p class="small text-center" v-if="!isEditing" :id="`global-quote-discount-discount`">
                        {{ discount.discount }} %
                    </p>
                    <input
                        v-else
                        :id="`input-global-quote-discount-discount`"
                        class="form-control text-center p-0 px-1"
                        type="number"
                        v-model="temp"
                        min="0"
                    />
                </td>
                <td class="text-primary">-{{ format.GBP(discount.total) }}</td>
                <td v-if="!props.preview">
                    <div class="d-flex justify-content-end">
                        <template v-if="!isEditing">
                            <font-awesome-icon
                                :id="'btn-quote-discount-remove'"
                                role="button"
                                class="px-2 py-2 text-primary"
                                :icon="['fas', 'trash-alt']"
                                v-if="can('delete quote_discount') || hasRole('Super Admin')"
                                @click="destroy()"
                            />
                            <font-awesome-icon
                                :id="'btn-quote-discount-edit'"
                                role="button"
                                class="px-2 py-2 text-primary"
                                :icon="['fas', 'edit']"
                                v-if="can('update quote_discount') || hasRole('Super Admin')"
                                @click="edit()"
                            />
                        </template>
                        <template v-else>
                            <font-awesome-icon
                                :id="'btn-quote-discount-cancel'"
                                role="button"
                                class="px-2 py-2 text-primary"
                                :icon="['fas', 'cancel']"
                                @click="cancel()"
                            />
                            <font-awesome-icon
                                :id="'btn-quote-discount-save'"
                                role="button"
                                class="px-2 py-2 text-primary"
                                :icon="['fas', 'save']"
                                @click="save()"
                            />
                        </template>
                    </div>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th>
                    <p class="text-start">Final Amount</p>
                </th>
                <th></th>
                <th id="total_net" style="font-size: 16px">
                    {{ format.GBP(quoteTotal()) }}
                </th>
                <th v-if="!props.preview"></th>
            </tr>
        </tfoot>
    </table>
</template>
