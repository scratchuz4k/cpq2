<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, Link, usePage} from '@inertiajs/vue3';
import {ref} from "vue";
import {proceedOrder, rejectOrder} from "@/Composables/orders.js";

const page = usePage()
const props = defineProps({
    order: Object,
});

const solutionsViews = {
    3: 'OrdersConnectivityTotalView',
    4: 'OrdersMobileTotalView',
    5: 'OrdersHardwareTotalView',
}

const showDate = (days = 0) => {
    const dateObj = new Date();
    dateObj.setDate(dateObj.getDate() + days);
    const currentDate = dateObj.getDate() + "/" + (dateObj.getMonth() + 1) + "/" + dateObj.getFullYear();
    return currentDate;
}

const items = ref([
    {label: 'Home', url: '/dashboard'},
    {label: `Orders`, url: '/orders'},
    {label: `Order #${props.order?.id} Preview`},
]);

</script>

<template>
    <Head :title="'Preview Order #' + order.id"/>
    <AuthenticatedLayout :order="order">
        <div class="row mb-3">
            <div class="col">
                <Header title="Order Preview" :breadcrumbs="items"></Header>
            </div>
            <div class="col" style="text-align: right">
                <button :disabled="order.user_id && order.user_id !== page.props.auth.user.id" class="btn btn-secondary mx-2" @click="rejectOrder(props.order)">
                    <font-awesome-icon :icon="['fas', 'x']"></font-awesome-icon>
                    Reject
                </button>
                <button :disabled="order.user_id && order.user_id !== page.props.auth.user.id" class="btn btn-primary mx-2" @click="proceedOrder(props.order)">
                    <font-awesome-icon :icon="['fas', 'check']"></font-awesome-icon>
                    Proceed
                </button>
            </div>
        </div>
        <div class="panel">
            <div class="container-fluid">
                <div class="row" id="header">
                    <div class="col" id="header_left">
                        <img src="https://img.logoipsum.com/331.svg" alt="Sample Logo" class="img-fluid">
                        <p>Insert Tagline</p>
                    </div>
                    <div class="col text-end" id="header_right">
                        <p>{{ order.account?.name }}<br>{{ order.address_data?.label ?? '' }}</p>
                        <p>Tel: +44 (0) 191 300 700</p>
                        <p>Email: sales@example.com</p>
                    </div>
                </div>
                <div class="cpq-summary row px-3" id="main">
                    <div class="col">
                        <h1 class="mb-3">Order O-{{ order.id }}</h1>
                        <div class="row mb-3">
                            <div class="col">
                                <p>Company address, city, state, post code<br>
                                    sales@example.com<br>
                                    +44 (0) 123 456 7890</p>
                            </div>
                            <div class="col">
                                <p>Order #{{ order.id }}<br>
                                    Quotation Date: {{ showDate() }}<br>
                                    Valid Date: {{ showDate(7) }}
                                </p>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col">
                                <h2>BILL FROM</h2>
                                <p>Name Surname<br>
                                    Company address, city, state, post code<br>
                                    sales@example.com<br>
                                    +44 (0) 123 456 7890</p>
                            </div>
                            <div class="col">
                                <h2>BILL TO</h2>
                                <p>Name Surname<br>
                                    Company address, city, state, post code<br>
                                    contact@account.test<br>
                                    +44 (0) 234 567 8910</p>
                            </div>
                        </div>

                        <div class="panel">
                            <div class="row mb-4" style="min-height: 50vh;">
<!--                                <div class="col">-->
<!--                                    <div class="d-flex justify-content-between align-items-center">-->
<!--                                        <h2>Items</h2>-->
<!--                                    </div>-->
<!--                                    &lt;!&ndash; Product List &ndash;&gt;-->
<!--                                    &lt;!&ndash; In a real application, this would be dynamically generated based on the selected solution type &ndash;&gt;-->
<!--                                    <table class="table">-->
<!--                                        &lt;!&ndash; Headers &ndash;&gt;-->
<!--                                        <thead>-->
<!--                                        <tr>-->
<!--                                            <th>#</th>-->
<!--                                            <th class="text-center d-none d-xl-table-cell">Provider</th>-->
<!--                                            <th>Name</th>-->
<!--                                            <th class="d-none d-xl-table-cell" style="white-space: nowrap;">Supplier-->
<!--                                                Order Ref.-->
<!--                                            </th>-->
<!--                                            <th>Type</th>-->
<!--                                            <th class="text-center">Qty</th>-->
<!--                                            <th class="text-end" style="white-space: nowrap;">Monthly Price</th>-->
<!--                                            <th class="text-end" style="white-space: nowrap;">Install Price</th>-->
<!--                                            <th class="text-end" style="white-space: nowrap;">Total Price</th>-->
<!--                                        </tr>-->
<!--                                        </thead>-->
<!--                                        <tbody>-->
<!--                                        <tr class="item-row" v-for="item in order.items"-->
<!--                                            style="vertical-align: middle;">-->
<!--                                            <td class="position-relative" style="width:40px">-->
<!--                                                {{ order.items.indexOf(item) + 1 }}-->
<!--                                            </td>-->
<!--                                            <td class="text-center d-none d-xl-table-cell">-->
<!--                                                <img v-if="item.data.providerKey" class="img-provider"-->
<!--                                                     @error="setAltImg"-->
<!--                                                     :src="`/images/providers/${item.data.providerKey}.png`"-->
<!--                                                     :alt="item.data.providerName" :title="item.data.providerName"/>-->
<!--                                            </td>-->
<!--                                            <td>-->
<!--                                                {{ item.name }}-->
<!--                                                <span v-if="item.data.additionalInfo['Primary Router Model']" v-tooltip-->
<!--                                                      data-bs-toggle="tooltip" data-bs-placement="top"-->
<!--                                                      title="Primary Router Model"-->
<!--                                                      class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">-->
<!--                                                        <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>-->
<!--                                                        {{ item.data.additionalInfo['Primary Router Model'] }}-->
<!--                                                    </span>-->
<!--                                            </td>-->
<!--                                            <td class="d-none d-xl-table-cell">-->
<!--                                                {{ item.data?.productCode }}-->
<!--                                            </td>-->
<!--                                            <td>-->
<!--                                                Monthly-->
<!--                                            </td>-->
<!--                                            <td class="text-center">-->
<!--                                                {{ item.quantity }}-->
<!--                                            </td>-->
<!--                                            <td class="text-end">-->
<!--                                                {{ GBP.format(item.data?.priceMonthly ?? item.data?.price) }}-->
<!--                                            </td>-->
<!--                                            <td class="text-end">-->
<!--                                                {{ GBP.format(item.data?.priceInstall ?? 0) }}-->
<!--                                            </td>-->
<!--                                            <td class="text-end">-->
<!--                                                {{ GBP.format(item.total_monthly) }}-->
<!--                                            </td>-->
<!--                                        </tr>-->
<!--                                        </tbody>-->
<!--                                    </table>-->
<!--                                    <div class="text-right">-->
<!--                                        <div class="row">-->
<!--                                            <div class="col"></div>-->
<!--                                            <div class="col">-->
<!--                                                <table class="table">-->
<!--                                                    <thead>-->
<!--                                                    <tr>-->
<!--                                                        <th></th>-->
<!--                                                        <th>NET</th>-->
<!--                                                        <th>VAT (20%)</th>-->
<!--                                                        <th>GROSS</th>-->
<!--                                                    </tr>-->
<!--                                                    </thead>-->
<!--                                                    <tbody>-->
<!--                                                    <tr>-->
<!--                                                        <th>Total Monthly</th>-->
<!--                                                        <td id="total_monthly_net" class="text-end">-->
<!--                                                            {{ GBP.format(order.total_monthly) }}-->
<!--                                                        </td>-->
<!--                                                        <td id="total_monthly_vat" class="text-end">-->
<!--                                                            {{ GBP.format(order.total_monthly_vat) }}-->
<!--                                                        </td>-->
<!--                                                        <td id="total_monthly_gross" class="text-end">-->
<!--                                                            {{ GBP.format(order.total_monthly_gross) }}-->
<!--                                                        </td>-->
<!--                                                    </tr>-->
<!--                                                    <tr>-->
<!--                                                        <th>Total One-off Costs</th>-->
<!--                                                        <td id="total_net" class="text-end">-->
<!--                                                            {{ GBP.format(order.total) }}-->
<!--                                                        </td>-->
<!--                                                        <td id="total_vat" class="text-end">-->
<!--                                                            {{ GBP.format(order.total_vat) }}-->
<!--                                                        </td>-->
<!--                                                        <td id="total_gross" class="text-end">-->
<!--                                                            {{ GBP.format(order.total_gross) }}-->
<!--                                                        </td>-->
<!--                                                    </tr>-->
<!--                                                    </tbody>-->
<!--                                                </table>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->

<!--                                </div>-->
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row px-3 mb-4" id="terms">
                    <div class="col">
                        <p>Terms & Conditions:</p>
                        <p>Lorem ipsum dolor sit amet...</p>
                    </div>
                </div>
                <div class="row mb-2" id="footer">
                    <div class="col">
                        <img src="https://img.logoipsum.com/331.svg">
                        <p>Insert Tagline</p>
                    </div>
                    <div class="col text-end">
                        <p>Lorem ipsum dolor sit amet...</p>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
