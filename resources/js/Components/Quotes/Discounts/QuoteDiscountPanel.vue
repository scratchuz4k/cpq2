<script setup>
import { ref } from 'vue';
import { setQuote, quote } from '@composables/useQuote';

const props = defineProps({
    pricePlans: Array
});
const isLoading = ref(false);
const pricePlansDiscounts = ref([]);
props.pricePlans.forEach((e) => {
    let discount = quote.discounts.find((x) => x.entity?.code == e.code);
    pricePlansDiscounts.value.push({
        ...e,
        value: discount ? discount.discount : 0,
        entity: 'price_plans'
    });
});

const saveQuote = async () => {
    isLoading.value = true;
    if (quote.discount === '') {
        isLoading.value = false;
        return;
    }
    try {
        let res = await axios.put(`/api/quotes/${quote.id}`, {
            discounts: [...pricePlansDiscounts.value]
        });
        setQuote(res.data);
        isLoading.value = false;
    } catch (ex) {
        isLoading.value = false;
        notyf.error(ex.response.data.message);
        isLoading.value = false;
    }
};
const resetQuote = async () => {
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
    <div class="price-mobile-sidebar total-quote-discount px-4 pt-4 pb-2 mb-3">
        <h3 class="mb-3">Total Quote Discount</h3>
        <div v-for="pricePlan in pricePlansDiscounts" class="d-flex justify-content-between align-items-center mb-3">
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
        </div>
        <template v-if="!quote.is_blocked">
            <button
                id="btn-apply-global-discount"
                class="btn btn-secondary w-100 mb-2"
                @click="saveQuote()"
                :disabled="isLoading"
            >
                Apply to quote
            </button>
            <button id="btn-reset-quote" class="btn w-100" @click="resetQuote()" :disabled="isLoading">
                Reset quote
            </button>
        </template>
    </div>
</template>
