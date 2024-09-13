<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';


defineProps({
    order: Object,
});

</script>

<template>

    <Head :title="'Quote #' + order.id" />
    <AuthenticatedLayout>
        <div class="row mb-3">
            <div class="col d-flex">
                <div class="me-3">
                    <font-awesome-icon class="fa-border" :icon="['fas', 'file-lines']"
                        style="font-size:30px;color:var(--secondary-light);border-color:var(--secondary-light);border-width:4px;border-radius:12px" />
                </div>
                <div style="padding-top:1px">
                    <p class="m-0">Q-{{ order.id }} // <span id="account_name">
                            {{ order.account?.name }}
                        </span>
                    </p>
                    <h1 class="m-0" style="font-size:18px;font-weight:bold">Quote</h1>
                </div>
            </div>
            <div class="col" style="text-align: right">
                <Link :href="route('orders.edit', { order: order.id })" class="btn btn-primary" method="get">
                <font-awesome-icon :icon="['fas', 'edit']"></font-awesome-icon>
                Edit Quote
                </Link>
                <Link :href="route('orders.show', { order: order.id })" class="btn btn-secondary mx-2" method="get"
                    :disabled="true">
                <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
                Preview PDF
                </Link>
            </div>
        </div>

        <div class="panel">

            <InfoBox :order="order"></InfoBox>
            <div class="row mb-4" style="min-height: 50vh;">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2>Items</h2>

                        <div>
                            <!-- <button class="btn btn-primary m-2" @click="() => showFilters = !showFilters">
                                <font-awesome-icon :icon="['fas', 'filter']" />
                            </button> -->
                        </div>
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
                            <tr class="item-row" v-for="item in order.items" style="vertical-align: middle;">
                                <td class="position-relative" style="width:40px">
                                    <label class="container-form-check-input custom-control custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="selected_items[]"
                                            value="{{ $item['id'] }}">
                                    </label>
                                </td>
                                <td class="text-center d-none d-xl-table-cell">
                                    <img class="img-provider" @error="setAltImg"
                                        :src="`/images/providers/${item.data.providerKey}.png`"
                                        :alt="item.data.providerName" :title="item.data.providerName" />
                                </td>
                                <td>
                                    {{ item.name }}
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    {{ item.data?.quoteId }}
                                </td>
                                <td>
                                    Monthly
                                </td>
                                <td>
                                    <div class="cpq-qty d-flex">
                                        <button type="button" class="btn btn-primary btn-sm mx-2">
                                            <font-awesome-icon :icon="['fas', 'minus']" />
                                        </button>
                                        <input class="form-control form-control-sm" type="number" min="0" max="99999"
                                            maxlength="5" :value="item.quantity" style="width: 100px; ">
                                        <button type="button" class="btn btn-primary btn-sm mx-2">
                                            <font-awesome-icon :icon="['fas', 'plus']" />
                                        </button>
                                    </div>
                                </td>
                                <td class="text-end">
                                    {{ GBP.format(item.data?.priceMonthly) }}
                                </td>
                                <td class="text-end">
                                    {{ GBP.format(item.data?.priceInstall) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="cpq-total text-right">
                        <div class="row">
                            <div class="col"></div>
                            <div class="col">
                                <table class="table table-hover">
                                    <tr>
                                        <th></th>
                                        <th>NET</th>
                                        <th>VAT (20%)</th>
                                        <th>GROSS</th>
                                    </tr>
                                    <tr>
                                        <th>Total Monthly</th>
                                        <td id="total_monthly_net" class="text-end">{{
                                            GBP.format(order.totals.monthly_net) }}</td>
                                        <td id="total_monthly_vat" class="text-end">{{
                                            GBP.format(order.totals.monthly_vat) }}</td>
                                        <td id="total_monthly_gross" class="text-end">{{
                                            GBP.format(order.totals.monthly_gross) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Total One-off Costs</th>
                                        <td id="total_oneoff_net" class="text-end">{{
                                            GBP.format(order.totals.oneoff_net) }}</td>
                                        <td id="total_oneoff_vat" class="text-end">{{
                                            GBP.format(order.totals.oneoff_vat) }}</td>
                                        <td id="total_oneoff_gross" class="text-end">{{
                                            GBP.format(order.totals.oneoff_gross) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </AuthenticatedLayout>
</template>