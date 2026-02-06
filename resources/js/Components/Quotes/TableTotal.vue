<script setup>
import { onMounted, reactive, ref } from 'vue';
import { quote, getQuote } from '@composables/useQuote';
import { can, hasRole } from '@/Composables/useAuth';

const props = defineProps({
    preview: Boolean
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
    temp.value = tempDiscount ? tempDiscount.discount : 0;
};

const save = async () => {
    isLoading.value = true;
    try {
        if (discount.id == null) {
            let res = await axios.post(`/api/quote-discounts`, discount);
        } else {
            let res = await axios.put(`/api/quote-discounts/${discount.id}`, discount);
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

const quoteTotal = (price_plan, column = 'total') => {
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
    return total;
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
</script>
<template>
    <div class="mobile-price-group-heading d-flex justify-content-between align-items-center">
        <h3>Totals</h3>
    </div>
    <table class="table-dealmaker table table-bordered text-end">
        <thead>
            <tr>
                <th style="width: 60%" class="text-start"></th>
                <th>%</th>
                <th>Total</th>
                <th v-if="!props.preview" style="width: 60px" class="text-center"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="can('viewAny quote_discount') || hasRole('Super Admin')">
                <td>
                    <p class="small text-start">Overall Discount</p>
                </td>
                <td>
                    <p class="small" v-if="!isEditing" :id="`global-quote-discount-discount`">
                        {{ discount.discount }} %
                    </p>
                    <input
                        v-else
                        :id="`input-global-quote-discount-discount`"
                        class="form-control text-center p-0 px-1"
                        type="number"
                        v-model="discount.discount"
                        min="0"
                    />
                </td>
                <td>{{ format.GBP(discount.value) }}</td>
                <td v-if="!props.preview">
                    <div class="d-flex justify-content-end">
                        <template v-if="!isEditing">
                            <font-awesome-icon
                                :id="'btn-quote-discount-remove'"
                                role="button"
                                class="px-2 py-2 text-secondary"
                                :icon="['fas', 'trash']"
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
                                class="px-2 py-2 text-secondary"
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
            <tr v-if="props.preview">
                <th>
                    <p class="small text-start">Monthly Total</p>
                </th>
                <td></td>
                <td>
                    {{ format.GBP(quoteTotal('Recurring')) }}
                </td>
                <td></td>
            </tr>
            <tr v-if="props.preview">
                <th>
                    <p class="small text-start">One Off Total</p>
                </th>
                <td></td>
                <td id="total_net">
                    {{ format.GBP(quoteTotal('One-Off')) }}
                </td>
                <td></td>
            </tr>
            <tr>
                <th>
                    <p class="small text-start">Total</p>
                </th>
                <td></td>
                <td id="total_net">
                    {{ format.GBP(quote.total) }}
                </td>
                <td v-if="!props.preview"></td>
            </tr>
        </tbody>
    </table>
</template>
