<script setup>
import { ref } from 'vue';
import { setQuote, quote } from '@composables/useQuote';

const props = defineProps({
    pricePlan: Array
});

const isLoading = ref(false);
const pricePlansDiscount = ref([]);

let discount = quote.discounts.find((x) => x.entity?.code == props.pricePlan.code);
pricePlansDiscount.value = {
    ...props.pricePlan,
    value: discount ? discount.discount : 0,
    entity: 'price_plans'
};

const saveDiscount = async () => {
    isLoading.value = true;
    if (quote.discount === '') {
        isLoading.value = false;
        return;
    }
    try {
        let res = await axios.put(`/api/quotes/${quote.id}`, {
            discounts: [pricePlansDiscount.value]
        });
        setQuote(res.data);
        isLoading.value = false;
    } catch (ex) {
        isLoading.value = false;
        notyf.error(ex.response.data.message);
        isLoading.value = false;
    }
};
const resetDiscounts = async () => {
    isLoading.value = true;
    let result = await swal.fire({
        title: 'Are you sure?'
    });
    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        isLoading.value = false;
        return;
    }

    quote.items.forEach((e) => {
        e.discount = 0;
    });
    pricePlansDiscounts.value.forEach((e) => (e.value = 0));
    try {
        let res = await axios.put(`/api/quotes/${quote.id}`, {
            discounts: [...pricePlansDiscounts.value],
            items: quote.items
        });
        setQuote(res.data);
        isLoading.value = false;
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
};
</script>
<template>
    <tr>
        <td>Total Quote Discount</td>
        <td>
            <label class="form-label m-0">{{ pricePlan.name }}</label>

            <div class="input-group p-0 w-50">
                <input
                    :id="`input-global-${pricePlan.code}-discount`"
                    type="number"
                    class="form-control"
                    v-model="pricePlan.value"
                />
                <span class="input-group-text">%</span>
            </div>
        </td>
        <td>
            <button
                v-if="!quote.is_blocked"
                id="btn-apply-global-discount"
                class="btn btn-secondary"
                @click="saveDiscount()"
                :disabled="isLoading"
            >
                A
            </button>
            <button
                id="btn-reset-quote"
                v-if="!quote.is_blocked"
                class="btn"
                @click="resetDiscounts()"
                :disabled="isLoading"
            >
                R
            </button>
        </td>
    </tr>
</template>
