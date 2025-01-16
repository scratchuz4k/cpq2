<script setup>
import _ from 'lodash'
import {reactive, ref} from 'vue';
import InputQuantity from "@/Components/InputQuantity.vue";
import {createOrder} from "@/Composables/useOrders.js";
const props = defineProps({
    modelValue: Object,
    preview: Boolean
})

const quote = reactive(props.modelValue)

const remove = async (index) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return
        }

        try {
            quote.items.splice(index, 1)
            let res = await axios.put(`/api/quotes/${quote.id}`, {
                items: quote.items
            })
            Object.assign(quote, res.data)
        } catch (ex) {
            alert(ex.response.data.message)
        }
    })
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

const changeQuantity = async (item, ev) => {
    item.quantity = ev
    saveQuote()
}

const items = ref([
    { label: 'Home', url: '/dashboard' },
    {
        label: `Quote #${quote?.id}`,
        url: quote
            ? (['REGISTRY', 'ORDERED'].includes(quote.status)) ? '#' : `/quotes/${quote.id}/edit`
            : '#'
    },
    { label: 'Summary' }
]);

</script>
<template>
    <Header title="Create a quote" :breadcrumbs="items"></Header>
    <InfoConnectivity v-if="!preview" :quote="quote"></InfoConnectivity>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <p class="fs-4 me-3 mb-0"><b>Your quote</b></p>
            <p class="mb-0 text-primary bold fs-5 pt-1">STEP 2/2</p>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-outline-primary me-2" @click="createOrder(quote.id)">
                <font-awesome-icon class="me-2" :icon="['fas', 'thumbtack']">
                </font-awesome-icon>Send to Order
            </button>
            <button v-if="modelValue.items.length == 0" class="btn btn-outline-primary me-2" type="button" disabled>
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview PDF
            </button>
            <a v-else class="btn btn-outline-primary me-2" type="submit" :href="route('quotes.preview', { quote: quote.id })">
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview PDF
            </a>
            <a :href="route('dashboard')" class="btn btn-primary" type="submit" >
                Save and close
            </a>
        </div>
    </div>

    <div class="row mb-4">
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
                        <th>Contract Len.</th>
                        <th style="white-space: nowrap;">Monthly price</th>
                        <th style="white-space: nowrap;">Install price</th>
                        <th style="width:60px" class="text-center"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="item-row" v-for="(item, index) in quote.items" style="vertical-align: middle;">
                        <td class="text-center d-none d-xl-table-cell">
                            <img v-if="item.data.providerKey" class="img-provider" @error="setAltImg"
                                :src="`/images/providers/${item.data.providerKey}.png`"
                                :alt="item.data.providerName" :title="item.data.providerName" />
                        </td>
                        <td>
                            {{ item.name }}
                            <span v-if="item.data.additionalInfo['Primary Router Model']" v-tooltip
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Primary Router Model"
                                class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                <font-awesome-icon :icon="['fas', 'plug']"></font-awesome-icon>
                                {{ item.data.additionalInfo['Primary Router Model'] }}
                            </span>
                            <span v-if="item.data?.isContended"
                                class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Contended, wires only full fibre service with symmetrical upload and download speeds" v-tooltip>
                                Contended Service
                            </span>
                            <span v-if="item.data?.additionalInfo?.['Type']=='Fibre Flex 200+'" v-tooltip
                                data-bs-toggle="tooltip" data-bs-placement="top" title="This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                                class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                Burstable Service
                            </span>
                        </td>
                        <td class="d-none d-xl-table-cell">
                            {{ item.data?.productCode }}
                        </td>
                        <td>
                            Monthly
                        </td>
                        <td>
                            <template v-if="!preview">
                                <div class="cpq-qty d-flex">
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
                            {{ item.contract_length }}
                        </td>
                        <td>
                            {{ GBP.format(item.data?.priceMonthly ?? item.data?.price) }}
                        </td>
                        <td>
                            {{ GBP.format(item.data?.priceInstall ?? 0) }}
                        </td>
                        <td class="text-center p-0">
                            <template v-if="!preview">
                                <button :id="'delete-btn-' + index" class="btn delete-btn w-100 border-0 py-2 px-0 text-center" @click="remove(index)">
                                    <font-awesome-icon :icon="['fas', 'trash']"></font-awesome-icon>
                                </button>
                            </template>
                            <template v-else>
                                {{ quote.items.indexOf(item) + 1 }}
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
                                    <th>Total monthly costs</th>
                                    <td id="total_monthly_net" class="text-end">
                                        {{ GBP.format(quote.total_monthly) }}
                                    </td>
                                    <td id="total_monthly_vat" class="text-end">
                                        {{ GBP.format(quote.total_monthly_vat) }}
                                    </td>
                                    <td id="total_monthly_gross" class="text-end">
                                        {{ GBP.format(quote.total_monthly_gross) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total one off costs</th>
                                    <td id="total_net" class="text-end">
                                        {{ GBP.format(quote.total) }}
                                    </td>
                                    <td id="total_vat" class="text-end">
                                        {{ GBP.format(quote.total_vat) }}
                                    </td>
                                    <td id="total_gross" class="text-end">
                                        {{ GBP.format(quote.total_gross) }}
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
            <button class="btn btn-outline-primary me-2" @click="createOrder(quote.id)">
                <font-awesome-icon class="me-2" :icon="['fas', 'thumbtack']">
                </font-awesome-icon>Send to Order
            </button>
            <button v-if="modelValue.items.length == 0" class="btn btn-outline-primary me-2" type="button" disabled>
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview PDF
            </button>
            <a v-else class="btn btn-outline-primary me-2" type="submit" :href="route('quotes.preview', { quote: quote.id })">
                <font-awesome-icon :icon="['far', 'file-pdf']"></font-awesome-icon>
                Preview PDF
            </a>
            <a :href="route('dashboard')" class="btn btn-primary" type="submit" >
                Save and close
            </a>
        </div>
    </div>
</template>

<style>
    .router-model {
        font-size: .625rem!important;
    }
    .delete-btn {
        all: unset;
        height: 25px;
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
        appearance: textfield;
    }
</style>
