<script setup>
import { proceedOrder, rejectOrder } from '@/Composables/useOrders.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { reactive, ref } from "vue";

const page = usePage()
const props = defineProps({
    order: Object,
});

const getLeafNodesWithChildren = (objects) => {
    let leaves = [];
    const findLeaves = (items) => {
        for (const item of items) {
            if (item.children && item.children.length > 0) {
                findLeaves(item.children);
            } else {
                leaves.push(item);
            }
        }
    }

    findLeaves(objects);
    return leaves;
}

const order = reactive(props.order)
Object.assign(order.items, getLeafNodesWithChildren(order.items))

const showDate = (days = 0) => {
    const dateObj = new Date();
    dateObj.setDate(dateObj.getDate() + days);
    const currentDate = dateObj.getDate() + "/" + (dateObj.getMonth() + 1) + "/" + dateObj.getFullYear();
    return currentDate;
}

const orderTotal = (key) => {
    return order.items.filter((x) => x.price_plan && x.price_plan.code == key).reduce((acc, item) => acc += item.total, 0)
}

const items = ref([
    { label: 'Home', url: '/dashboard' },
    { label: `Orders`, url: '/orders' },
    { label: `Order #${props.order?.id} Preview` },
]);

</script>

<template>

    <Head :title="'Preview Order #' + order.id" />
    <AuthenticatedLayout :order="order">
        <div class="row mb-3">
            <div class="col">
                <Header title="Create a order" :breadcrumbs="items"></Header>
            </div>
            <div class="col" style="text-align: right">
                <template v-if="order.status !== 'REGISTRY'">
                    <div class="badge text-bg-dark h2">
                        {{ order.status }}
                    </div>
                </template>
                <template v-else>
                    <button id="reject-order-btn"
                            v-if="(order.user_id === page.props.auth.user.id || order.user_id === null)"
                            class="btn btn-secondary mx-2" @click="rejectOrder(props.order)">
                        <font-awesome-icon :icon="['fas', 'x']"></font-awesome-icon>
                        Reject
                    </button>
                    <button id="proceed-order-btn"
                            v-if="(order.user_id === page.props.auth.user.id || order.user_id === null)"
                            class="btn btn-primary mx-2" @click="proceedOrder(props.order)">
                        <font-awesome-icon :icon="['fas', 'check']"></font-awesome-icon>
                        Proceed
                    </button>
                </template>
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
                        <p>{{ order.account?.name }}<br>{{ order.address_data?.label }}</p>
                        <p>Tel: +44 (0) 191 300 700</p>
                        <p>Email: sales@example.com</p>
                    </div>
                </div>
                <div class="cpq-summary row px-3" id="main">
                    <div class="col">
                        <h1 class="mb-3">Order Q-{{ order.id }}</h1>
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
                        <div>
                            <div class="row mb-4" style="min-height: 50vh;">
                                <div class="col">
                                    <h3>Recurring charges</h3>
                                    <table class="table">
                                        <!-- Headers -->
                                        <thead>
                                            <tr>
                                                <th class="w-5">ID</th>
                                                <th v-if="order.solution_type_id === 1"
                                                    class="text-center d-none d-xl-table-cell w-5">Provider
                                                </th>
                                                <th class="w-auto">Product</th>
                                                <th class="text-center w-5">Months</th>
                                                <th v-if="order.solution_type_id === 1"
                                                    class="d-none d-xl-table-cell text-center w-10"
                                                    style="white-space: nowrap;">Supplier Order Ref.
                                                </th>
                                                <th class="text-center w-10">Type</th>
                                                <th class="text-center w-5">Qty</th>
                                                <th class="text-center w-10">Price</th>
                                                <th class="text-end w-10" style="white-space: nowrap;">Total Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="item-row"
                                                v-for="(item, index) in order.items.filter((x) => x.price_plan && x.price_plan.code == 'MONTHLY')"
                                                style="vertical-align: middle;">
                                                <td class="position-relative">
                                                    {{ index + 1 }}
                                                </td>
                                                <td v-if="order.solution_type_id === 1"
                                                    class="text-center d-none d-xl-table-cell">
                                                    <img v-if="item.data?.providerKey" class="img-provider"
                                                        @error="setAltImg"
                                                        :src="`/images/providers/${item.data.providerKey}.png`"
                                                        :alt="item.data.providerName" :title="item.data.providerName" />
                                                </td>
                                                <td>
                                                    {{ item.name }}
                                                    <span v-if="item.data?.additionalInfo['Primary Router Model']"
                                                        v-tooltip data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Primary Router Model"
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                                        <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                                        {{ item.data.additionalInfo['Primary Router Model'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    {{ item.contract_length }}
                                                </td>
                                                <td v-if="order.solution_type_id === 1"
                                                    class="d-none d-xl-table-cell text-center">
                                                    {{ item.data?.productCode }}
                                                </td>
                                                <td class="text-center">
                                                    Monthly
                                                </td>
                                                <td class="text-center">
                                                    {{ item.quantity }}
                                                </td>
                                                <td class="text-center">
                                                    {{ GBP.format(item.price) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ GBP.format(item.total) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    <h3>One off charges</h3>
                                    <table class="table">
                                        <!-- Headers -->
                                        <thead>
                                            <tr>
                                                <th class="w-5">ID</th>
                                                <th v-if="order.solution_type_id === 1"
                                                    class="text-center d-none d-xl-table-cell w-5">Provider
                                                </th>
                                                <th class="w-auto">Product</th>
                                                <th v-if="order.solution_type_id === 1"
                                                    class="d-none d-xl-table-cell w-10 text-center"
                                                    style="white-space: nowrap;">Supplier
                                                    Order Ref.
                                                </th>
                                                <th class="text-center w-10">Type</th>
                                                <th class="text-center w-5">Qty</th>
                                                <th class="text-center w-10">Price</th>
                                                <th class="text-end w-10" style="white-space: nowrap;">Total Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="item-row"
                                                v-for="(item, index) in order.items.filter((x) => x.price_plan && x.price_plan.code == 'SINGLE')"
                                                style="vertical-align: middle;">
                                                <td class="position-relative">
                                                    {{ index + 1 }}
                                                </td>
                                                <td v-if="order.solution_type_id === 1"
                                                    class="text-center d-none d-xl-table-cell">
                                                    <img v-if="item.data?.providerKey" class="img-provider"
                                                        @error="setAltImg"
                                                        :src="`/images/providers/${item.data.providerKey}.png`"
                                                        :alt="item.data.providerName" :title="item.data.providerName" />
                                                </td>
                                                <td>
                                                    {{ item.name }}
                                                    <span v-if="item.data?.additionalInfo['Primary Router Model']"
                                                        v-tooltip data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Primary Router Model"
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                                        <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                                        {{ item.data.additionalInfo['Primary Router Model'] }}
                                                    </span>
                                                </td>
                                                <td v-if="order.solution_type_id === 1"
                                                    class="d-none d-xl-table-cell text-center">
                                                    {{ item.data?.productCode }}
                                                </td>
                                                <td class="text-center">
                                                    Single
                                                </td>
                                                <td class="text-center">
                                                    {{ item.quantity }}
                                                </td>
                                                <td class="text-center">
                                                    {{ GBP.format(item.price) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ GBP.format(item.total) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    <br>
                                    <div class="text-right">
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
                                                                {{ GBP.format(orderTotal('MONTHLY')) }}
                                                            </td>
                                                            <td id="total_monthly_vat" class="text-end">
                                                                {{ GBP.format(orderTotal('MONTHLY') * .20) }}
                                                            </td>
                                                            <td id="total_monthly_gross" class="text-end">
                                                                {{ GBP.format(orderTotal('MONTHLY') * 1.20) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Total One-off Costs</th>
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
<style scoped>
.w-5 {
    width: 5% !important;
}

.w-10 {
    width: 10% !important;
}

.w-20 {
    width: 20% !important;
}
</style>
