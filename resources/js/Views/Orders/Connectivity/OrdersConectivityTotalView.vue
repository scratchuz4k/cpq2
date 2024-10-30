<script setup>
import _ from 'lodash'
import { reactive } from 'vue';
import InputQuantity from "@/Components/InputQuantity.vue";
const props = defineProps({
    modelValue: Object,
    preview: Boolean
})

const order = reactive(props.modelValue)

const remove = async (id) => {
    try {
        await axios.delete(`/api/order-items/${id}`)
        let res = await axios.get(`/api/orders/${order.id}`)
        Object.assign(order, res.data)
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
        let res = await axios.put(`/api/orders/${order.id}`,
            {
                items: order.items,
            })
        Object.assign(order, res.data)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}, 500)

</script>
<template>
<!-- Product List -->
<!-- In a real application, this would be dynamically generated based on the selected solution type -->
<table :class="{'table-hover': !preview}" class="table table-bordered">
    <!-- Headers -->
    <thead>
        <tr>
            <th class="text-center d-none d-xl-table-cell">Provider</th>
            <th>Product</th>
            <th class="d-none d-xl-table-cell" style="white-space: nowrap;">Supplier quote ref.</th>
            <th>Type</th>
            <th>Qty.</th>
            <th style="white-space: nowrap;">Monthly price</th>
            <th style="white-space: nowrap;">Install price</th>
            <th>Remove</th>
        </tr>
    </thead>
    <tbody>
        <tr class="item-row" v-for="item in order.items" style="vertical-align: middle;">
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
            <td>
                <template v-if="!preview">
                    <div class="cpq-qty d-flex input-group">
                        <InputQuantity :id="'hardware-' + index" v-model="item.quantity" />
                    </div>
                </template>
                <template v-else>
                    {{ item.quantity }}
                </template>
            </td>
            <td>
                {{ GBP.format(item.data?.priceMonthly ?? item.data?.price) }}
            </td>
            <td>
                {{ GBP.format(item.data?.priceInstall ?? 0) }}
            </td>
            <td class="position-relative" style="width:40px">
                <template v-if="!preview">
                    <button class="btn delete-btn w-100" @click="remove(item.id)">
                        <font-awesome-icon :icon="['fas', 'trash']"></font-awesome-icon>
                    </button>
                </template>
                <template v-else>
                    {{ order.items.indexOf(item) + 1 }}
                </template>
            </td>
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
                            {{ GBP.format(order.totals.monthly_net) }}
                        </td>
                        <td id="total_monthly_vat" class="text-end">
                            {{ GBP.format(order.totals.monthly_vat) }}
                        </td>
                        <td id="total_monthly_gross" class="text-end">
                            {{ GBP.format(order.totals.monthly_gross) }}
                        </td>
                    </tr>
                    <tr>
                        <th>Total One-off Costs</th>
                        <td id="total_oneoff_net" class="text-end">
                            {{ GBP.format(order.totals.oneoff_net) }}
                        </td>
                        <td id="total_oneoff_vat" class="text-end">
                            {{ GBP.format(order.totals.oneoff_vat) }}
                        </td>
                        <td id="total_oneoff_gross" class="text-end">
                            {{ GBP.format(order.totals.oneoff_gross) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>

<style>
    .delete-btn {
        all: unset;
        height: 30px;
        width: 30px;
        color: var(--purple)
    }

    .cpq-qty {
        button {
            border: 2px solid var(--grey);
            border-radius: 8px;

            &:hover {
                background-color: var(--purple);
                border-color: var(--purple);
            }

            &.decrease {
                border-top-right-radius: 0px;
                border-bottom-right-radius: 0px;
            }

            &.increase {
                border-top-left-radius: 0px;
                border-bottom-left-radius: 0px;
            }
        }

        .cpq-qty-input {
            border-width: 2px!important;
            border-color: var(--grey);
        }
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Remove arrows in Firefox */
    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
