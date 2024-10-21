<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue'

const props = defineProps({
    modelValue: Object
})

const order = reactive(props.modelValue)
const temp = reactive({
    margin: 0,
    discount: 0
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
    temp.discount = p.discount
    temp.margin = p.margin
    p.is_editing = !p.is_editing
}
const save = async (p) => {

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
                        <h3>{{ GBP.format(order.total_monthly) }}</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:50%">Product</th>
                                <th> Qty </th>
                                <th style="width:15%"> Your Price</th>
                                <th class="text-center" style="width:15%"> Discount</th>
                                <th class="text-center" style="width:15%"> Margin</th>
                                <th class="text-right" style="width:15%"> Sell Price</th>
                                <th>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in order.items.filter((x) => x.price_monthly)" class="bg-light-grey ">
                                <td><font-awesome-icon class="me-2" :icon="['fas', 'trash']" @click="remove()" /> {{
                                    p.name }}</td>
                                <td> x{{ p.quantity }}</td>
                                <td> {{ GBP.format(p.price_monthly) }}</td>
                                <td class="text-center">
                                    <template v-if="!p.is_editing">
                                        {{ p.discount }} %
                                    </template>
                                    <input v-else class="form-control py-0" type="number" min="0" max="100"
                                        v-model="temp.discount">
                                </td>
                                <td class="text-center">
                                    <template v-if="!p.is_editing">
                                        {{ p.margin }} %
                                    </template>
                                    <input v-else class="form-control py-0" type="number" min="0" max="100"
                                        v-model="temp.margin">
                                </td>
                                <td class="text-right" style="width:10%"> {{ GBP.format(p.total_monthly) }}</td>
                                <td>
                                    <div class="d-flex">
                                        <template v-if="!p.is_editing">
                                            <font-awesome-icon :icon="['fas', 'edit']" @click="edit(p)" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon class="me-3" :icon="['fas', 'cancel']"
                                                @click="cancel(p)" />
                                            <font-awesome-icon :icon="['fas', 'save']" @click="save(p)" />
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
                        <h3>{{ GBP.format(order.total) }}</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:50%">Product</th>
                                <th> Qty </th>
                                <th style="width:15%"> Your Price</th>
                                <th class="text-center" style="width:15%"> Discount</th>
                                <th class="text-center" style="width:15%"> Margin</th>
                                <th class="text-right" style="width:15%"> Sell Price</th>
                                <th>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in order.items.filter((x) => x.price)" class="bg-light-grey">
                                <td> <font-awesome-icon class="me-2" :icon="['fas', 'trash']" @click="remove()" /> {{
                                    p.name }}</td>
                                <td> x{{ p.quantity }}</td>
                                <td> {{ GBP.format(p.price) }}</td>
                                <td class="text-center">
                                    <template v-if="!p.is_editing">
                                        {{ p.discount }} %
                                    </template>
                                    <input v-else class="form-control py-0" type="number" min="0" max="100"
                                        v-model="temp.discount">
                                </td>
                                <td class="text-center">
                                    <template v-if="!p.is_editing">
                                        {{ p.margin }} %
                                    </template>
                                    <input v-else class="form-control py-0" type="number" min="0" max="100"
                                        v-model="temp.margin">
                                </td>
                                <td class="text-right"> {{ GBP.format(p.total) }}</td>
                                <td>
                                    <div class="d-flex">
                                        <template v-if="!p.is_editing">
                                            <font-awesome-icon :icon="['fas', 'edit']" @click="edit(p)" />
                                        </template>
                                        <template v-else>
                                            <font-awesome-icon class="me-3" :icon="['fas', 'cancel']"
                                                @click="cancel(p)" />
                                            <font-awesome-icon :icon="['fas', 'save']" @click="save(p)" />
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
                <InfoBox>
                    <div style="font-size: 14px;">
                        <b style="font-size: 16px;">Quote {{ order.id }} for {{ order.account.name }}</b>
                        <br>
                        Contact Number: 0124567890<br>
                        Email: firstname.lastname@gmail.com
                    </div>
                </InfoBox>
                <div class="price-mobile-sidebar px-4 pt-4 pb-2 mb-3" style="position: sticky; top: 25px">
                    <h3 class="mb-3">Your quote: #{{ order.id }}</h3>

                    <h4>Recurring charges</h4>
                    <div class="sidebar_items border-bottom mb-2">
                        <div v-for="p in planProducts" class="item mb-2">
                            <span>{{ p.label }}</span><span class="badge bg-light-grey dark">
                                {{ GBP.format(p.quantity * p.priceMonthly) }}
                            </span>
                        </div>

                        <div v-for="p in boltOnProducts" class="item mb-2">
                            <span>{{ p.name }}</span>
                            <span class="badge bg-light-grey dark">
                                {{ GBP.format(p.quantity * p.priceMonthly) }}
                            </span>
                        </div>
                    </div>
                    <div class="sidebar_items border-bottom mb-4">
                        <div class="item mb-2">
                            <span><b>Recurring Subtotal</b></span>
                            <span class="badge bg-light-grey dark">
                                {{ GBP.format(boltOnProducts.reduce((acc, p) => acc += p.quantity * p.priceMonthly, 0)
                                    + planProducts.reduce((acc, p) => acc += p.quantity * p.priceMonthly, 0)) }}
                            </span>
                        </div>
                    </div>

                    <h4>One-off charges</h4>
                    <div class="sidebar_items border-bottom mb-2">
                        <div class="item mb-2">
                            <span>Hardware</span>
                            <span class="badge bg-light-grey dark">
                                {{ GBP.format(hardwareProducts.reduce(
                                    (acc, p) => acc += p.quantity * (p.data?.price ?? 0), 0))
                                }}
                            </span>
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
</style>
