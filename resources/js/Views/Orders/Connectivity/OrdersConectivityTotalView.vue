<script setup>
import _ from 'lodash'
import {reactive, ref} from 'vue';
import InputQuantity from "@/Components/InputQuantity.vue";
const props = defineProps({
    modelValue: Object,
    preview: Boolean
})

const order = reactive(props.modelValue)

const remove = async (index) => {
    try {
        order.items = order.items.splice(index, 1)
        let res = await axios.put(`/api/orders/${order.id}`, {
            items: order.items
        })
        Object.assign(order, res.data)
    } catch (ex) {
        alert(ex.response.data.message)
    }
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

const changeQuantity = async (item, ev) => {
    item.quantity = ev
    saveQuote()
}

const items = ref([
    { label: 'Home', url: '/dashboard' },
    { label: 'Create a quote' },
]);

</script>
<template>
    <Header title="Create a quote" :breadcrumbs="items"></Header>
    <InfoConnectivity v-if="!preview" :order="order"></InfoConnectivity>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <p class="fs-4 me-3 mb-0"><b>Your quote</b></p>
            <p class="mb-0 text-primary bold fs-5 pt-1">STEP 2/2</p>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <a class="btn btn-outline-primary me-2" type="submit" :href="route('orders.preview', { order: order.id })">
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview PDF
            </a>
            <a :href="route('dashboard')" class="btn btn-primary" type="submit" >
                Save and close
            </a>
        </div>
    </div>

    <div class="row mb-4" style="min-height: 50vh;">
        <div class="col">
            <!-- Product List -->
            <!-- In a real application, this would be dynamically generated based on the selected solution type -->
            <table :class="{ 'table-hover': !preview }" class="table table-bordered">
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
                    <tr class="item-row" v-for="(item, index) in order.items" style="vertical-align: middle;">
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
                                    <InputQuantity :id="'line-' + index" v-model="item.quantity"
                                       @change="(ev) => changeQuantity(item, ev)"
                                    />
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
                        <td style="width:40px">
                            <template v-if="!preview">
                                <button class="btn delete-btn w-100 px-0 text-start" @click="remove(index)">
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
                        <table class="table table-bordered">
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
                                    <th>Total monthly osts</th>
                                    <td id="total_monthly_net" class="text-end">
                                        {{ GBP.format(order.total_monthly) }}
                                    </td>
                                    <td id="total_monthly_vat" class="text-end">
                                        {{ GBP.format(order.total_monthly_vat) }}
                                    </td>
                                    <td id="total_monthly_gross" class="text-end">
                                        {{ GBP.format(order.total_monthly_gross) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total one off costs</th>
                                    <td id="total_net" class="text-end">
                                        {{ GBP.format(order.total) }}
                                    </td>
                                    <td id="total_vat" class="text-end">
                                        {{ GBP.format(order.total_vat) }}
                                    </td>
                                    <td id="total_gross" class="text-end">
                                        {{ GBP.format(order.total_gross) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div>
        <div class="d-flex justify-content-end align-items-center">
            <a class="btn btn-outline-primary me-2" type="submit" :href="route('orders.preview', { order: order.id })">
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview PDF
            </a>
            <a class="btn btn-primary" type="submit" >
                Save and close
            </a>
        </div>
    </div>
</template>

<style>
    .delete-btn {
        all: unset;
        height: 30px;
        width: 30px;
        color: var(--purple);

        &:hover {
            color: var(--red) !important;
        }
    }

    .cpq-qty {
        button {
            border: 2px solid var(--grey);
            border-radius: 8px;

            &:hover {
                background-color: var(--purple);
                border-color: var(--purple);
                color: white;
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
