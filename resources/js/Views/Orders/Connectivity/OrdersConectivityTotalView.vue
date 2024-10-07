<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
const props = defineProps({
    modelValue: Object
})

const isEdited = ref(false)
const remove = async (id) => {
    let index = props.modelValue.items.findIndex((item) => item.id == id)
    try {
        await axios.delete(`/api/order-items/${id}`)
        props.modelValue.items.splice(index, 1)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const addQuantity = (product, quantity) => {
    product.quantity += quantity
    isEdited.value = true
}

const removeQuantity = (product, quantity) => {
    product.quantity -= quantity
    isEdited.value = true
}

const saveQuote = () => {
    try {
        props.modelValue.items.forEach(async (article) => {
            let url = article.id ? `/api/order-items/${article.id}` : '/api/order-items';
            let method = article.id ? 'put' : 'post';
            await axios[method](url,
                {
                    order_id: props.modelValue.id,
                    quantity: article.quantity,
                    name: article.name,
                })
        })
        router.get(`/orders/${props.modelValue.id}`)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}
</script>
<template>
    <div class="panel">

        <InfoConnectivity :order="modelValue"></InfoConnectivity>
        <div class="row mb-4" style="min-height: 50vh;">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Items</h2>
                </div>

                <!-- Product List -->
                <!-- In a real application, this would be dynamically generated based on the selected solution type -->
                <table class="table table-hover">
                    <!-- Headers -->
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center d-none d-xl-table-cell">Image</th>
                            <th>Name</th>
                            <th class="d-none d-xl-table-cell" style="white-space: nowrap;">Supplier Quote Ref.</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th class="text-end" style="white-space: nowrap;">Monthly Price</th>
                            <th class="text-end" style="white-space: nowrap;">Install Price</th>
                            <!-- <th>Line Discount</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row" v-for="item in modelValue.items" style="vertical-align: middle;">
                            <td class="position-relative" style="width:40px">
                                <button class="btn btn-secondary btn-sm" @click="remove(item.id)">
                                    <font-awesome-icon :icon="['fas', 'trash']"></font-awesome-icon>
                                </button>
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
                            <td>
                                <div class="cpq-qty d-flex">
                                    <button type="button" class="btn btn-primary btn-sm mx-2"
                                        @click="removeQuantity(item, 1)">
                                        <font-awesome-icon :icon="['fas', 'minus']" />
                                    </button>
                                    <input class="form-control form-control-sm" type="number" min="0" max="99999"
                                        maxlength="5" :value="item.quantity" style="width: 100px; ">
                                    <button type="button" class="btn btn-primary btn-sm mx-2"
                                        @click="addQuantity(item, 1)">
                                        <font-awesome-icon :icon="['fas', 'plus']" />
                                    </button>
                                </div>
                            </td>
                            <td class="text-end">
                                {{ GBP.format(item.data?.priceMonthly ?? item.data?.price) }}
                            </td>
                            <td class="text-end">
                                {{ GBP.format(item.data?.priceInstall ?? 0) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="isEdited" class="d-flex justify-content-end my-3">
                    <button class="btn btn-primary" @click="saveQuote()">Save Quote</button>
                </div>
                <div class="cpq-total text-right">
                    <div class="row">
                        <div class="col"></div>
                        <div class="col">
                            <table class="table table-hover">
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
                                            {{ GBP.format(modelValue.totals.monthly_net) }}
                                        </td>
                                        <td id="total_monthly_vat" class="text-end">
                                            {{ GBP.format(modelValue.totals.monthly_vat) }}
                                        </td>
                                        <td id="total_monthly_gross" class="text-end">
                                            {{ GBP.format(modelValue.totals.monthly_gross) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total One-off Costs</th>
                                        <td id="total_oneoff_net" class="text-end">
                                            {{ GBP.format(modelValue.totals.oneoff_net) }}
                                        </td>
                                        <td id="total_oneoff_vat" class="text-end">
                                            {{ GBP.format(modelValue.totals.oneoff_vat) }}
                                        </td>
                                        <td id="total_oneoff_gross" class="text-end">
                                            {{ GBP.format(modelValue.totals.oneoff_gross) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
