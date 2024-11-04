<script setup>
import _ from 'lodash'
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
const props = defineProps({
    modelValue: Object,
    preview: Boolean
})

const quote = reactive(props.modelValue)

const remove = async (id) => {
    try {
        await axios.delete(`/api/quote-items/${id}`)
        let res = await axios.get(`/api/quotes/${quote.id}`)
        Object.assign(quote, res.data)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const changeQuantity = (quantity, product) => {
    product.quantity = quantity
    saveQuote()
}

const saveQuote = _.debounce(async () => {
    try {
        let res = await axios.put(`/api/quotes/${quote.id}`,
            {
                items: quote.items,
            })
        Object.assign(quote, res.data)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}, 500)

</script>
<template>
<!-- Product List -->
<!-- In a real application, this would be dynamically generated based on the selected solution type -->
<table :class="{'table-hover': !preview}" class="table">
    <!-- Headers -->
    <thead>
        <tr>
            <th>
                <template v-if="preview">#</template>
            </th>
            <th class="text-center d-none d-xl-table-cell">Provider</th>
            <th>Name</th>
            <th class="d-none d-xl-table-cell" style="white-space: nowrap;">Supplier Quote Ref.</th>
            <th>Type</th>
            <th class="text-center">Qty</th>
            <th class="text-end" style="white-space: nowrap;">Monthly Price</th>
            <th class="text-end" style="white-space: nowrap;">Install Price</th>
            <template v-if="preview">
                <th class="text-end" style="white-space: nowrap;">Total Price</th>
            </template>
        </tr>
    </thead>
    <tbody>
        <tr class="item-row" v-for="item in quote.items" style="vertical-align: middle;">
            <td class="position-relative" style="width:40px">
                <template v-if="!preview">
                    <button class="btn btn-secondary btn-sm" @click="remove(item.id)">
                        <font-awesome-icon :icon="['fas', 'trash']"></font-awesome-icon>
                    </button>
                </template>
                <template v-else>
                    {{ quote.items.indexOf(item) + 1 }}
                </template>
            </td>
            <td class="text-center d-none d-xl-table-cell">
                <img v-if="item.data.providerKey" class="img-provider" @error="setAltImg"
                    :src="`/images/providers/${item.data.providerKey}.png`"
                    :alt="item.data.providerName" :title="item.data.providerName" />
            </td>
            <td>
                {{ item.name }}
            </td>
            <td class="d-none d-xl-table-cell">
                {{ item.data?.productCode }}
            </td>
            <td>
                Monthly
            </td>
            <td class="text-center">
                <template v-if="!preview">
                <div class="cpq-qty d-flex justify-content-center">
                    <button type="button" class="btn btn-primary btn-sm mx-2"
                        :disabled="item.quantity == 1" @click="changeQuantity(item.quantity - 1, item)">
                        <font-awesome-icon :icon="['fas', 'minus']" />
                    </button>
                    <input class="form-control form-control-sm" type="number" min="0" max="99999"
                        @input="(ev) => changeQuantity(ev.target.value, item)" maxlength="5"
                        :value="item.quantity" style="width: 100px;">
                    <button type="button" class="btn btn-primary btn-sm mx-2"
                        @click="changeQuantity(item.quantity + 1, item)">
                        <font-awesome-icon :icon="['fas', 'plus']" />
                    </button>
                </div>
                </template>
                <template v-else>
                    {{ item.quantity }}
                </template>
            </td>
            <td class="text-end">
                {{ GBP.format(item.data?.priceMonthly ?? item.data?.price) }}
            </td>
            <td class="text-end">
                {{ GBP.format(item.data?.priceInstall ?? 0) }}
            </td>
            <template v-if="preview">
                <td class="text-end">
                    {{ GBP.format((item.quantity * (item.data?.priceMonthly ?? 0 + item.data?.priceInstall ?? 0)) ?? 0) }}
                </td>
            </template>
        </tr>
    </tbody>
</table>
<div class="cpq-total text-right">
    <div class="row">
        <div class="col"></div>
        <div class="col">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>NET</th>
                        <th>VAT (20%)</th>
                        <th>GROSS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Total Monthly</th>
                        <td id="total_monthly_net" class="text-end">
                            {{ GBP.format(quote.totals.monthly_net) }}
                        </td>
                        <td id="total_monthly_vat" class="text-end">
                            {{ GBP.format(quote.totals.monthly_vat) }}
                        </td>
                        <td id="total_monthly_gross" class="text-end">
                            {{ GBP.format(quote.totals.monthly_gross) }}
                        </td>
                    </tr>
                    <tr>
                        <th>Total One-off Costs</th>
                        <td id="total_oneoff_net" class="text-end">
                            {{ GBP.format(quote.totals.oneoff_net) }}
                        </td>
                        <td id="total_oneoff_vat" class="text-end">
                            {{ GBP.format(quote.totals.oneoff_vat) }}
                        </td>
                        <td id="total_oneoff_gross" class="text-end">
                            {{ GBP.format(quote.totals.oneoff_gross) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


</template>
