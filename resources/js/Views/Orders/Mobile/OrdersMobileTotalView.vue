<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue'

const props = defineProps({
    modelValue: Object
})

const isLoading = ref(false)
const order = reactive(props.modelValue)
const recurringNewLine = reactive({
    name: null,
    margin: 0,
    discount: 0,
    quantity: 0,
    price: 0,
    price_monthly: 0,
})
const oneOffNewLine = reactive({
    name: null,
    margin: 0,
    discount: 0,
    quantity: 0,
    price: 0,
    price_monthly: 0,
})


const temp = reactive({
    margin: 0,
    discount: 0,
    quantity: 0
})

const planProducts = ref([
]);

const boltOnProducts = ref([
]);

const hardwareProducts = ref([])

const getHardwareProducts = () => {
    order.items.forEach(element => {
        if (boltOnProducts.value.find(x => x.name == element.name) || planProducts.value.find(x => x.name == element.name)) {
            return
        }
        hardwareProducts.value.push(element)
    });
}
const remove = async (id) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return
        }

        try {
            await axios.delete(`/api/order-items/${id}`)
            router.get(`/orders/${order.id}`)
        } catch (ex) {
            notyf.error(ex.response.data.message)
        }

    })
}

const edit = (p) => {
    temp.quantity = p.quantity
    temp.discount = p.discount
    temp.margin = p.margin
    order.items.forEach((e) => e.is_editing = false)
    p.is_editing = !p.is_editing

}
const save = async (p) => {
    p.quantity = temp.quantity
    p.discount = temp.discount
    p.margin = temp.margin
    try {
        let res = await axios.put(`/api/orders/${order.id}`,
            {
                items: order.items,
            })
        Object.assign(order, res.data)
        p.is_editing = !p.is_editing
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}

const saveOrder = async (p) => {
    isLoading.value = true
    if (order.discount === '') {
        return
    }
    try {
        let res = await axios.put(`/api/orders/${order.id}`,
            {
                contract_length: order.contract_length,
                discount: order.discount,
            })
        Object.assign(order, res.data)
        isLoading.value = false
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}

const resetQuote = async () => {
    isLoading.value = true
    let result = await swal.fire({
        title: 'Are you sure?'
    })
    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return
    }

    order.items.forEach((e) => { e.discount = 0; e.margin = 0; })
    try {
        let res = await axios.put(`/api/orders/${order.id}`,
            {
                discount: 0,
                items: order.items,
            })
        Object.assign(order, res.data)
        isLoading.value = false
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}

const cancel = (p) => {
    p.is_editing = !p.is_editing
}

const saveNewLine = async (item) => {
    try {
        let res = await axios.put(`/api/orders/${order.id}`,
            {
                items: [...order.items, item],
            })
        Object.assign(order, res.data)
        item.show = false
        Object.assign(item, {
            name: null,
            margin: 0,
            discount: 0,
            quantity: 0,
            price: 0,
            price_monthly: 0,
        })
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}

getHardwareProducts()

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Create a Quote', url: `/orders/${order.id}/edit` },
    { label: 'Summary of quote' },
]);

</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
            <div class="col-lg-8 mb-5 price-summary">
                <Header title="Build your deal" :breadcrumbs="items"></Header>
                <div v-if="order.items.find((x) => x.price_monthly)" class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>Recurring charges</h3>
                        <h3 id="recurring_total">{{ GBP.format(order.total_monthly) }}</h3>
                    </div>
                    <table id="table-recurring" class="table">
                        <thead>
                            <tr>
                                <th style="width:40%">Product</th>
                                <th class="text-center" style="width:10%"> Qty </th>
                                <th class="text-right" style="width:10%"> Your Price</th>
                                <th class="text-center" style="width:10%"> Discount</th>
                                <th class="text-center" style="width:10%"> Margin</th>
                                <th class="text-right" style="width:10%"> Sell Price</th>
                                <th class="text-right" style="width:10%">
                                    <div class="d-flex justify-content-center">
                                        <font-awesome-icon id="btn-recurring-add" :icon="['fas', 'plus']" role="button"
                                            class="py-2 w-100" @click="() => recurringNewLine.show = true" />
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, i) in order.items.filter((x) => x.price_monthly)"
                                class="recurring-item bg-light-grey">
                                <td>
                                    {{ p.name }}
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-quantity'">
                                        x{{ p.quantity }}
                                    </span>
                                    <input :id="'input-recurring-' + i + '-quantity'" v-else
                                        class="form-control text-center py-1" type="number" min="0" max="100"
                                        v-model="temp.quantity">
                                </td>
                                <td class="text-right"> {{ GBP.format(p.original_price_monthly) }}</td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-discount'">
                                        {{ p.discount }} %
                                    </span>
                                    <input :id="'input-recurring-' + i + '-discount'" v-else
                                        class="form-control text-center py-1" type="number" min="0" max="100"
                                        v-model="temp.discount">
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-margin'">
                                        {{ p.margin }} %
                                    </span>
                                    <input :id="'input-recurring-' + i + '-margin'" v-else
                                        class="form-control text-center py-1" type="number" min="0" max="100"
                                        v-model="temp.margin">
                                </td>
                                <td class="text-right fw-bold">
                                    {{ GBP.format(p.price_monthly) }}
                                </td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-center">
                                        <template v-if="!p.is_editing && p.id">
                                            <font-awesome-icon :id="'btn-recurring-' + i + '-edit'" role="button"
                                                class="px-2 py-3 text-primary" :icon="['fas', 'edit']"
                                                @click="edit(p)" />
                                            <font-awesome-icon :id="'btn-recurring-' + i + '-remove'" role="button"
                                                class="px-2 py-3 text-secondary" :icon="['fas', 'trash']"
                                                @click="remove(p.id)" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :id="'btn-recurring-' + i + '-cancel'" role="button"
                                                class="px-2 py-3 text-secondary" :icon="['fas', 'cancel']"
                                                @click="cancel(p)" />
                                            <font-awesome-icon :id="'btn-recurring-' + i + '-save'" role="button"
                                                class="px-2 py-3  text-primary" :icon="['fas', 'save']"
                                                @click="save(p)" />
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="recurringNewLine.show">
                            <tr>
                                <th>
                                    <input :id="'input-new-recurring-name'" class="form-control py-1" type="text"
                                        v-model="recurringNewLine.name">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-recurring-qty'" class="form-control text-center py-1"
                                        type="number" v-model="recurringNewLine.quantity">
                                </th>
                                <th>
                                    <input :id="'input-new-recurring-price_monthly'"
                                        class="form-control text-center py-1" type="number"
                                        v-model="recurringNewLine.price_monthly">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-recurring-discount'" class="form-control text-center py-1"
                                        type="number" v-model="recurringNewLine.discount">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-recurring-margin'" class="form-control text-center py-1"
                                        type="number" v-model="recurringNewLine.margin">
                                </th>
                                <th class="text-right"> </th>
                                <th class="text-right">
                                    <div class="d-flex justify-content-center">
                                        <font-awesome-icon :id="'btn-new-recurring-cancel'" role="button"
                                            class="px-2 py-3 text-secondary" :icon="['fas', 'cancel']"
                                            @click="() => recurringNewLine.show = false" />
                                        <font-awesome-icon id="btn-new-recurring-add" :icon="['fas', 'save']"
                                            role="button" class="px-2 py-3 text-primary"
                                            @click="saveNewLine(recurringNewLine)" />
                                    </div>
                                </th>
                            </tr>
                        </tfoot>

                    </table>
                </div>
                <div v-if="order.items.find((x) => x.price)" class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>One off charges</h3>
                        <h3 id="one_off_total">{{ GBP.format(order.total) }}</h3>
                    </div>
                    <table id="table-one_off" class="table">
                        <thead>
                            <tr>
                                <th style="width:40%">Product</th>
                                <th class="text-center" style="width:10%"> Qty </th>
                                <th class="text-right" style="width:10%"> Your Price</th>
                                <th class="text-center" style="width:10%"> Discount</th>
                                <th class="text-center" style="width:10%"> Margin</th>
                                <th class="text-right" style="width:10%"> Sell Price</th>
                                <th class="text-right" style="width:10%">
                                    <div class="d-flex justify-content-center">
                                        <font-awesome-icon id="btn-one_off-add" :icon="['fas', 'plus']" role="button"
                                            class="py-2 w-100" @click="() => oneOffNewLine.show = true" />
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, i) in order.items.filter((x) => x.price)" class="one_off-item bg-light-grey">
                                <td>{{ p.name }}</td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'one_off-' + i + '-quantity'">
                                        x{{ p.quantity }}
                                    </span>
                                    <input :id="'input-one_off-' + i + '-quantity'" v-else class="form-control text-center py-1"
                                        type="number" min="0" max="100" v-model="temp.quantity">
                                </td>
                                <td class="text-right"> {{ GBP.format(p.original_price) }}</td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'one_off-' + i + '-discount'">
                                        {{ p.discount }} %
                                    </span>
                                    <input v-else :id="'input-one_off-' + i + '-discount'" class="form-control text-center py-1"
                                        type="number" min="0" max="100" v-model="temp.discount">
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'one_off-' + i + '-margin'">
                                        {{ p.margin }} %
                                    </span>
                                    <input v-else :id="'input-one_off-' + i + '-margin'" class="form-control text-center py-1"
                                        type="number" min="0" max="100" v-model="temp.margin">
                                </td>
                                <td class="text-right fw-bold">
                                    <span :id="'input-one_off-' + i + '-price'">
                                        {{ GBP.format(p.price) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-center">
                                        <template v-if="!p.is_editing">
                                            <font-awesome-icon :id="'btn-one_off-' + i + '-edit'" role="button"
                                                class="px-2 py-3 text-primary" :icon="['fas', 'edit']"
                                                @click="edit(p)" />
                                            <font-awesome-icon :id="'btn-one_off-' + i + '-remove'" role="button"
                                                class="px-2 py-3 text-secondary" :icon="['fas', 'trash']"
                                                @click="remove(p.id)" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :id="'btn-one_off-' + i + '-cancel'" role="button"
                                                class="px-2 py-3 text-secondary" :icon="['fas', 'cancel']"
                                                @click="cancel(p)" />
                                            <font-awesome-icon :id="'btn-one_off-' + i + '-save'" role="button"
                                                class="px-2 py-3  text-primary" :icon="['fas', 'save']"
                                                @click="save(p)" />
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="oneOffNewLine.show">
                            <tr>
                                <th>
                                    <input :id="'input-new-one_off-name'" class="form-control py-1" type="text"
                                        v-model="oneOffNewLine.name">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-one_off-qty'" class="form-control text-center py-1" type="number"
                                        v-model="oneOffNewLine.quantity">
                                </th>
                                <th>
                                    <input :id="'input-new-one_off-price'" class="form-control text-center py-1" type="number"
                                        v-model="oneOffNewLine.price">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-one_off-discount'" class="form-control text-center py-1" type="number"
                                        v-model="oneOffNewLine.discount">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-one_off-margin'" class="form-control text-center py-1" type="number"
                                        v-model="oneOffNewLine.margin">
                                </th>
                                <th class="text-right"> </th>
                                <th class="text-right">
                                    <div class="d-flex justify-content-center">
                                        <font-awesome-icon :id="'btn-new-one_off-cancel'" role="button"
                                            class="px-2 py-3 text-secondary" :icon="['fas', 'cancel']"
                                            @click="() => oneOffNewLine.show = false" />
                                        <font-awesome-icon id="btn-new-one_off-add" :icon="['fas', 'save']"
                                            role="button" class="px-2 py-3 text-primary"
                                            @click="saveNewLine(oneOffNewLine)" />
                                    </div>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mb-5">
                    <div>
                        <p class="payment-info border-bottom pb-3 mb-3 big bold">
                            <span class="text-primary">1 payment </span> of
                            {{ GBP.format(order.total) }}<br>
                            followed by
                            <span class="text-primary"> {{ order.contract_length }} monthly payments</span> of
                            {{ GBP.format(order.total_monthly) }}
                        </p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a class="btn text-primary" :href="route('orders.edit', { order: order.id }) + '?step=3'">
                            <font-awesome-icon class="me-2" :icon="['fas', 'arrow-left']"></font-awesome-icon>
                            Previous</a>
                        <div>
                            <button class="btn btn-primary">
                                <font-awesome-icon class="me-2" :icon="['fas', 'envelope']">
                                </font-awesome-icon> Email this quote
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div style="position: sticky; top: 25px">
                    <InfoBox>
                        <div style="font-size: 14px;">
                            <b style="font-size: 16px;">Quote {{ order.id }} for {{ order.account.name }}</b>
                            <br>
                            Contact Number: 0124567890<br>
                            Email: firstname.lastname@gmail.com
                        </div>
                    </InfoBox>
                    <div class="price-mobile-sidebar px-4 pt-4 pb-2 mb-3">
                        <h3 class="mb-3">Overall Informations</h3>
                        <div class="mb-3">
                            <label class="form-label">Contract Length (Months)</label>
                            <input id="input-global-contratct-length" type="number" class="form-control"
                                v-model="order.contract_length">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quote Discount</label>
                            <input id="input-global-discount" type="number" class="form-control"
                                v-model="order.discount">
                        </div>
                        <button id="btn-apply-global-discount" class="btn btn-secondary w-100 mb-2" @click="saveOrder()"
                            :disabled="isLoading">
                            Apply to quote
                        </button>
                        <button id="btn-reset-quote" class="btn w-100" @click="resetQuote()" :disabled="isLoading">
                            Reset quote
                        </button>
                    </div>
                    <div class="price-mobile-sidebar px-4 pt-4 pb-2 mb-3">
                        <h3 class="mb-3">Comission Calculator</h3>
                        <p style="font-size: 14px;"> Currently, you will make {{ GBP.format(order.comission_value) }}

                            commission and
                            {{ order.margin.toFixed(2) }} %
                            margin on
                            this quote.</p>
                        <div class="p-3" style="background: #E9EDF0;">
                            <div class="mb-3">
                                <label class="form-label">Margin</label>
                                <input id="input-margin-order" class="form-control" :value="order.margin.toFixed(2) + ' %'"
                                    disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comission (<span id="order-tax-comission">
                                        {{ order.commission_rate }}</span>%)
                                </label>
                                <input id="input-comission_value-order" class="form-control"
                                    :value="GBP.format(order.comission_value)" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.table {
    font-size: 14px !important;
    vertical-align: middle;
}

tr td,
tr th {
    padding: 0px 10px !important;
}

tr td:last-child,
tr th:last-child {
    padding: 0px !important;
}

thead {
    vertical-align: middle !important;
}

thead tr th {
    padding: 10px !important;
    background: #E8E8E8;
    font-weight: normal;
    font-size: 12px !important;
}

/* For Chrome, Safari, Edge, Opera */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* For Firefox */
input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}

[role=button]:hover {
    opacity: 0.75
}
</style>
