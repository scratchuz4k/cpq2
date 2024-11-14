<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue'

const props = defineProps({
    modelValue: Object
})

const isLoading = ref(false)
const quote = reactive(props.modelValue)
const recurringNewLine = reactive({
    name: null,
    contract_length: 1,
    discount: 0,
    quantity: 0,
    price: 0,
    price_plan_code: 'monthly'
})

const oneOffNewLine = reactive({
    name: null,
    discount: 0,
    quantity: 0,
    price: 0,
    price_plan_code: 'single'
})


const temp = reactive({
    contract_length: 1,
    discount: 0,
    quantity: 0
})

const planProducts = ref([
]);

const boltOnProducts = ref([
]);

const hardwareProducts = ref([])

const getHardwareProducts = () => {
    quote.items.forEach(element => {
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
            await axios.delete(`/api/quote-items/${id}`)
            router.get(`/quotes/${quote.id}`)
        } catch (ex) {
            notyf.error(ex.response.data.message)
        }

    })
}

const edit = (p) => {
    temp.quantity = p.quantity
    temp.discount = p.discount
    temp.contract_length = p.contract_length
    quote.items.forEach((e) => e.is_editing = false)
    p.is_editing = !p.is_editing

}
const save = async (p) => {
    p.quantity = temp.quantity
    p.discount = temp.discount
    p.contract_length = temp.contract_length
    try {
        let res = await axios.put(`/api/quotes/${quote.id}`,
            {
                items: quote.items,
            })
        Object.assign(quote, res.data)
        p.is_editing = !p.is_editing
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}

const saveQuote = async (p) => {
    isLoading.value = true
    if (quote.discount === '') {
        isLoading.value = false
        return
    }
    try {
        let res = await axios.put(`/api/quotes/${quote.id}`,
            {
                discount: quote.discount,
            })
        Object.assign(quote, res.data)
        isLoading.value = false
    } catch (ex) {
        isLoading.value = false
        notyf.error(ex.response.data.message)
        isLoading.value = false
    }
}

const resetQuote = async () => {
    isLoading.value = true
    let result = await swal.fire({
        title: 'Are you sure?'
    })
    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        isLoading.value = false
        return
    }

    quote.items.forEach((e) => { e.discount = 0; })
    try {
        let res = await axios.put(`/api/quotes/${quote.id}`,
            {
                discount: 0,
                items: quote.items,
            })
        Object.assign(quote, res.data)
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
        let res = await axios.put(`/api/quotes/${quote.id}`,
            {
                items: [...quote.items, item],
            })
        Object.assign(quote, res.data)
        item.show = false
        Object.assign(item, {
            name: null,
            margin: 0,
            discount: 0,
            quantity: 0,
            price: 0,
        })
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}

const quoteTotal = (key) => {
    return quote.items.filter((x) => x.price_plan && x.price_plan.code == key).reduce((acc, item) => acc += item.total, 0)
}

getHardwareProducts()

const items = ref([
    { label: 'Home', url: '/' },
    { label: `Quote #${quote?.id}`, url: quote ? `/quotes/${quote.id}/edit` : '#' },
    { label: 'Summary' },
]);

</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
            <div class="col-lg-8 mb-5 price-summary">
                <Header title="Build your deal" :breadcrumbs="items"></Header>
                <div class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>Recurring charges</h3>
                        <h3 id="recurring_total">{{ GBP.format(quoteTotal('monthly')) }}</h3>
                    </div>
                    <table id="table-recurring" class="table">
                        <thead>
                            <tr>
                                <th style="width:40%">Product</th>
                                <th class="text-center" style="width:10%"> Qty </th>
                                <th class="text-right" style="width:10%"> Your Price</th>
                                <th class="text-center" style="width:10%"> Margin</th>
                                <th class="text-center" style="width:15%"> Contract Len.</th>
                                <th class="text-center" style="width:10%"> Discount</th>
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
                            <tr v-for="(p, i) in quote.items.filter((x) => x.price_plan && x.price_plan.code == 'monthly')"
                                class="recurring-item bg-light-grey">
                                <td>
                                    {{ p.name }}
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-quantity'">
                                        x{{ p.quantity }}
                                    </span>
                                    <input :id="'input-recurring-' + i + '-quantity'" v-else
                                        class="form-control text-center p-0 px-1" type="number" min="0" max="100"
                                        v-model="temp.quantity">
                                </td>
                                <td class="text-right"> {{ GBP.format(p.price) }}</td>
                                <td class="text-center">
                                    <span :id="'recurring-' + i + '-margin'">
                                        {{ GBP.format(p.margin) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-contract_length'">
                                        {{ p.contract_length }}
                                    </span>
                                    <input :id="'input-recurring-' + i + '-contract_length'" v-else
                                        class="form-control text-center p-0 px-1" type="number" min="0" max="100"
                                        v-model="temp.contract_length">
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'recurring-' + i + '-discount'">
                                        {{ p.discount }} %
                                    </span>
                                    <input :id="'input-recurring-' + i + '-discount'" v-else
                                        class="form-control text-center p-0 px-1" type="number" min="0" max="100"
                                        v-model="temp.discount">
                                </td>
                                <td class="text-right fw-bold">
                                    {{ GBP.format(p.total) }}
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
                                    <input :id="'input-new-recurring-name'" class="form-control p-0 px-1" type="text"
                                        v-model="recurringNewLine.name">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-recurring-qty'" class="form-control text-center p-0 px-1"
                                        type="number" v-model="recurringNewLine.quantity">
                                </th>
                                <th>
                                    <input :id="'input-new-recurring-price_monthly'"
                                        class="form-control text-center p-0 px-1" type="number"
                                        v-model="recurringNewLine.price">
                                </th>
                                <th></th>
                                <th class="text-center">
                                    <input :id="'input-new-recurring-contract_length'"
                                        class="form-control text-center p-0 px-1" type="number"
                                        v-model="recurringNewLine.contract_length">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-recurring-discount'"
                                        class="form-control text-center p-0 px-1" type="number"
                                        v-model="recurringNewLine.discount">
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
                <div class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>One off charges</h3>
                        <h3 id="one_off_total">{{ GBP.format(quoteTotal('single')) }}</h3>
                    </div>
                    <table id="table-one_off" class="table">
                        <thead>
                            <tr>
                                <th style="width:40%">Product</th>
                                <th class="text-center" style="width:10%"> Qty </th>
                                <th class="text-right" style="width:10%"> Your Price</th>
                                <th class="text-center" style="width:10%"> Margin</th>
                                <th class="text-center" style="width:10%"> Discount</th>
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
                            <tr v-for="(p, i) in quote.items.filter((x) => !x.price_plan || x.price_plan?.code == 'single')"
                                class="one_off-item bg-light-grey">
                                <td>{{ p.name }}</td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'one_off-' + i + '-quantity'">
                                        x{{ p.quantity }}
                                    </span>
                                    <input :id="'input-one_off-' + i + '-quantity'" v-else
                                        class="form-control text-center p-0 px-1" type="number" min="0" max="100"
                                        v-model="temp.quantity">
                                </td>
                                <td class="text-right"> {{ GBP.format(p.original_price) }}</td>
                                <td class="text-center">
                                    <span :id="'one_off-' + i + '-margin'">
                                        {{ GBP.format(p.margin) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span v-if="!p.is_editing" :id="'one_off-' + i + '-discount'">
                                        {{ p.discount }} %
                                    </span>
                                    <input v-else :id="'input-one_off-' + i + '-discount'"
                                        class="form-control text-center p-0 px-1" type="number" min="0" max="100"
                                        v-model="temp.discount">
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
                                    <input :id="'input-new-one_off-name'" class="form-control p-0 px-1" type="text"
                                        v-model="oneOffNewLine.name">
                                </th>
                                <th class="text-center">
                                    <input :id="'input-new-one_off-qty'" class="form-control text-center p-0 px-1"
                                        type="number" v-model="oneOffNewLine.quantity">
                                </th>
                                <th>
                                    <input :id="'input-new-one_off-price'" class="form-control text-center p-0 px-1"
                                        type="number" v-model="oneOffNewLine.price">
                                </th>
                                <th class="text-center"></th>
                                <th class="text-center">
                                    <input :id="'input-new-one_off-discount'" class="form-control text-center p-0 px-1"
                                        type="number" v-model="oneOffNewLine.discount">
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
                            {{ GBP.format(quoteTotal('single')) }}<br>
                            followed by
                            <span class="text-primary"> {{ quote.contract_length }} monthly payments</span> of
                            {{ GBP.format(quoteTotal('monthly')) }}
                        </p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a class="btn text-primary" :href="route('quotes.edit', { quote: quote.id }) + '?step=3'">
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
                            <b style="font-size: 16px;">Quote {{ quote.id }} for {{ quote.account.name }}</b>
                            <br>
                            Contact Number: 0124567890<br>
                            Email: firstname.lastname@gmail.com
                        </div>
                    </InfoBox>
                    <div class="price-mobile-sidebar px-4 pt-4 pb-2 mb-3">
                        <h3 class="mb-3">Overall Informations</h3>
                        <div class="mb-3">
                            <label class="form-label">Quote Discount</label>
                            <input id="input-global-discount" type="number" class="form-control"
                                v-model="quote.discount">
                        </div>
                        <button id="btn-apply-global-discount" class="btn btn-secondary w-100 mb-2" @click="saveQuote()"
                            :disabled="isLoading">
                            Apply to quote
                        </button>
                        <button id="btn-reset-quote" class="btn w-100" @click="resetQuote()" :disabled="isLoading">
                            Reset quote
                        </button>
                    </div>
                    <div class="price-mobile-sidebar px-4 pt-4 pb-2 mb-3">
                        <h3 class="mb-3">Commission Calculator</h3>
                        <p style="font-size: 14px;"> Currently, you will make {{ GBP.format(quote.commission_value) }}
                            commission and
                            {{ quote.margin.toFixed(2) }} %
                            margin on
                            this quote.</p>
                        <div class="p-3" style="background: #E9EDF0;">
                            <div class="mb-3">
                                <label class="form-label">Margin</label>
                                <input id="input-margin-order" class="form-control"
                                    :value="quote.margin.toFixed(2) + ' %'" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Commission (<span id="order-tax-commission">
                                        {{ quote.commission_rate }}</span>%)
                                </label>
                                <input id="input-commission_value-order" class="form-control"
                                    :value="GBP.format(quote.commission_value)" disabled>
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

.form-control {
    font-size: 0.9rem;
    line-height: 1.5rem;
}
</style>
