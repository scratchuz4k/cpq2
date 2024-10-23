<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue'

const props = defineProps({
    modelValue: Object
})

const order = reactive(props.modelValue)
const temp = reactive({
    margin: 0,
    discount: 0,
    quantity: 0
})

const planProducts = ref([
    {
        id: order.items.find(x => x.data?.name == 'Basic / 5 GB / 100min')?.id ?? null,
        label: 'Basic',
        name: 'Basic / 5 GB / 100min',
        data: '5 GB',
        minutes: 100,
        priceMonthly: 10,
        quantity: order.items.find(x => x.data?.name == 'Basic / 5 GB / 100min')?.quantity ?? 0,
    },
    {
        id: order.items.find(x => x.data?.name == 'Premium / 20 GB / 500min')?.id ?? null,
        label: 'Premium',
        name: 'Premium / 20 GB / 500min',
        data: '20 GB',
        minutes: 500,
        priceMonthly: 20,
        quantity: order.items.find(x => x.data?.name == 'Premium / 20 GB / 500min')?.quantity ?? 0,
    },
    {
        id: order.items.find(x => x.data?.name == 'Traveller / Unlimited / 1000min')?.id ?? null,
        label: 'Traveller',
        name: 'Traveller / Unlimited / 1000min',
        data: 'Unlimited',
        minutes: 1000,
        priceMonthly: 30,
        quantity: order.items.find(x => x.data?.name == 'Traveller / Unlimited / 1000min')?.quantity ?? 0,
    },
]);

const boltOnProducts = ref([
    {
        id: order.items.find(x => x.data?.name == '500 UK minute bundle')?.id ?? null,
        name: '500 UK minute bundle',
        priceMonthly: 10,
        quantity: order.items.find(x => x.data?.name == '500 UK minute bundle')?.quantity ?? 0,
    },
    {
        id: order.items.find(x => x.data?.name == 'Share bundle')?.id ?? null,
        name: 'Share bundle',
        priceMonthly: 20,
        quantity: order.items.find(x => x.data?.name == 'Share bundle')?.quantity ?? 0,
    },
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

const saveDiscount = async (p) => {
    try {
        let res = await axios.put(`/api/orders/${order.id}`,
            {
                discount: order.discount,
            })
        Object.assign(order, res.data)
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}



const cancel = (p) => {
    p.is_editing = !p.is_editing
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
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:40%">Product</th>
                                <th class="text-center" style="width:15%"> Qty </th>
                                <th style="width:10%"> Your Price</th>
                                <th class="text-center" style="width:15%"> Discount</th>
                                <th class="text-center" style="width:15%"> Margin</th>
                                <th class="text-right" style="width:10%"> Sell Price</th>
                                <th> </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, i) in order.items.filter((x) => x.price_monthly)" class="bg-light-grey ">
                                <td>
                                    {{ p.name }}
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-quantity'">
                                        x{{ p.quantity }}
                                    </span>
                                    <input :id="'input-recurring-' + i + '-quantity'" v-else class="form-control py-0"
                                        type="number" min="0" max="100" v-model="temp.quantity">
                                </td>
                                <td> {{ GBP.format(p.original_price_monthly) }}</td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-discount'">
                                        {{ p.discount }} %
                                    </span>
                                    <input :id="'input-recurring-' + i + '-discount'" v-else class="form-control py-0"
                                        type="number" min="0" max="100" v-model="temp.discount">
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-margin'">
                                        {{ p.margin }} %
                                    </span>
                                    <input :id="'input-recurring-' + i + '-margin'" v-else class="form-control py-0"
                                        type="number" min="0" max="100" v-model="temp.margin">
                                </td>
                                <td class="text-right" style="width:10%">
                                    {{ GBP.format(p.price_monthly) }}
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <template v-if="!p.is_editing">
                                            <font-awesome-icon :id="'btn-recurring-' + i + '-edit'" class="me-2"
                                                :icon="['fas', 'edit']" @click="edit(p)" />
                                            <font-awesome-icon :id="'btn-recurring-' + i + '-remove'"
                                                :icon="['fas', 'trash']" @click="remove()" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :id="'btn-recurring-' + i + '-cancel'" class="me-2"
                                                :icon="['fas', 'cancel']" @click="cancel(p)" />
                                            <font-awesome-icon :id="'btn-recurring-' + i + '-save'"
                                                :icon="['fas', 'save']" @click="save(p)" />
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="order.items.find((x) => x.price)" class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>One off charges</h3>
                        <h3 id="one_off_total">{{ GBP.format(order.total) }}</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:40%">Product</th>
                                <th class="text-center" style="width:15%"> Qty </th>
                                <th style="width:10%"> Your Price</th>
                                <th class="text-center" style="width:15%"> Discount</th>
                                <th class="text-center" style="width:15%"> Margin</th>
                                <th class="text-right" style="width:10%"> Sell Price</th>
                                <th> </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, i) in order.items.filter((x) => x.price)" class="bg-light-grey">
                                <td>{{ p.name }}</td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'one_off-' + i + '-quantity'">
                                        x{{ p.quantity }}
                                    </span>
                                    <input :id="'input-one_off-' + i + '-quantity'" v-else class="form-control py-0"
                                        type="number" min="0" max="100" v-model="temp.quantity">
                                </td>
                                <td> {{ GBP.format(p.original_price) }}</td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'one_off-' + i + '-discount'">
                                        {{ p.discount }} %
                                    </span>
                                    <input v-else :id="'input-one_off-' + i + '-discount'" class="form-control py-0"
                                        type="number" min="0" max="100" v-model="temp.discount">
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'one_off-' + i + '-margin'">
                                        {{ p.margin }} %
                                    </span>
                                    <input v-else :id="'input-one_off-' + i + '-margin'" class="form-control py-0"
                                        type="number" min="0" max="100" v-model="temp.margin">
                                </td>
                                <td class="text-right">
                                    <span :id="'input-one_off-' + i + '-price'">
                                        {{ GBP.format(p.price) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <template v-if="!p.is_editing">
                                            <font-awesome-icon :id="'btn-one_off-' + i + '-edit'" class="me-2"
                                                :icon="['fas', 'edit']" @click="edit(p)" />
                                            <font-awesome-icon :id="'btn-one_off-' + i + '-remove'"
                                                :icon="['fas', 'trash']" @click="remove()" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon :id="'btn-one_off-' + i + '-cancel'" class="me-3"
                                                :icon="['fas', 'cancel']" @click="cancel(p)" />
                                            <font-awesome-icon :id="'btn-one_off-' + i + '-save'"
                                                :icon="['fas', 'save']" @click="save(p)" />
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mb-5">
                    <div>
                        <p class="payment-info border-bottom pb-3">
                            <span class="text-primary">1 payment </span> of
                            {{ GBP.format(order.total) }}<br>
                            followed by
                            <span class="text-primary">12 monthly payments</span> of
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
                        <h3 class="mb-3">Overall Discount</h3>
                        <div class="mb-3">
                            <label class="form-label">Recurring charges</label>
                            <input class="form-control" v-model="order.discount">
                        </div>
                        <button class="btn btn-secondary w-100 mb-2" @click="saveDiscount()">Apply to quote</button>
                        <button class="btn  w-100"> Reset quote</button>
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
                                <input class="form-control" :value="order.margin.toFixed(2) + ' %'">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comission</label>
                                <input class="form-control" :value="GBP.format(order.comission_value)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
tr td {
    padding: 15px !important;
}

tr th {
    padding: 10px !important;
    background: #E8E8E8;
    font-weight: normal;
    font-size: 12px !important;
}

.table {
    font-size: 14px !important;
    vertical-align: middle;
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
}
</style>
