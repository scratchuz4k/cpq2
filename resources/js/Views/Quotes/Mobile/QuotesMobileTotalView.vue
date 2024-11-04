<script setup>
import {Link, router} from '@inertiajs/vue3';
import { ref } from 'vue'

const props = defineProps({
    modelValue: Object
})


const planProducts = ref([
    {
        id: props.modelValue.items.find(x => x.data?.name == 'Basic / 5 GB / 100min')?.id ?? null,
        label: 'Basic',
        name: 'Basic / 5 GB / 100min',
        data: '5 GB',
        minutes: 100,
        priceMonthly: 10,
        quantity: props.modelValue.items.find(x => x.data?.name == 'Basic / 5 GB / 100min')?.quantity ?? 0,
    },
    {
        id: props.modelValue.items.find(x => x.data?.name == 'Premium / 20 GB / 500min')?.id ?? null,
        label: 'Premium',
        name: 'Premium / 20 GB / 500min',
        data: '20 GB',
        minutes: 500,
        priceMonthly: 20,
        quantity: props.modelValue.items.find(x => x.data?.name == 'Premium / 20 GB / 500min')?.quantity ?? 0,
    },
    {
        id: props.modelValue.items.find(x => x.data?.name == 'Traveller / Unlimited / 1000min')?.id ?? null,
        label: 'Traveller',
        name: 'Traveller / Unlimited / 1000min',
        data: 'Unlimited',
        minutes: 1000,
        priceMonthly: 30,
        quantity: props.modelValue.items.find(x => x.data?.name == 'Traveller / Unlimited / 1000min')?.quantity ?? 0,
    },
]);

const boltOnProducts = ref([
    {
        id: props.modelValue.items.find(x => x.data?.name == '500 UK minute bundle')?.id ?? null,
        name: '500 UK minute bundle',
        priceMonthly: 10,
        quantity: props.modelValue.items.find(x => x.data?.name == '500 UK minute bundle')?.quantity ?? 0,
    },
    {
        id: props.modelValue.items.find(x => x.data?.name == 'Share bundle')?.id ?? null,
        name: 'Share bundle',
        priceMonthly: 20,
        quantity: props.modelValue.items.find(x => x.data?.name == 'Share bundle')?.quantity ?? 0,
    },
]);

const hardwareProducts = ref([])

const getHardwareProducts = () => {
    props.modelValue.items.forEach(element => {
        if (boltOnProducts.value.find(x => x.name == element.name) || planProducts.value.find(x => x.name == element.name)) {
            return
        }
        hardwareProducts.value.push(element)
    });
}
const remove = async (id) => {
    try {
        await axios.delete(`/api/quote-items/${id}`)
        router.get(`/quotes/${props.modelValue.id}`)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

getHardwareProducts()

</script>
<template>

    <div class="panel panel-mobile">
        <div class="row">
            <div class="col-lg-7 mb-5 price-summary">
                <InfoQuote :quote="modelValue"></InfoQuote>
                <div class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>Products</h3>
                        <Link :href="route('quotes.edit', { quote: modelValue.id }) + '?step=1'">Make a change</Link>
                    </div>
                    <div>
                        <div v-for="p in planProducts" class="item mb-2">
                            <span>{{ p.label }}</span>

                            <div>
                                <span class="badge bg-light-grey dark">{{ p.quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>Bolt-ons</h3>
                        <Link :href="route('quotes.edit', { quote: modelValue.id }) + '?step=2'">Make a change</Link>
                    </div>
                    <div>
                        <div v-for="p in boltOnProducts" class="item mb-2">
                            <span>{{ p.name }}</span>
                            <div>
                                <span class="badge bg-light-grey dark">{{ p.quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>Add-ons</h3>
                        <Link :href="route('quotes.edit', { quote: modelValue.id}) + '?step=3'">Make a change</Link>
                    </div>
                    <div>
                        <div v-for="p in hardwareProducts" class="item mb-2">
                            <span>{{ p.name }}</span>
                            <div>
                                <span class="badge bg-light-grey dark">{{ p.quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <div>
                        <p class="payment-info border-bottom px-5 pb-3">1 payment of {{ GBP.format(modelValue.totals.oneoff_vat) }} followed by 12 monthly payments of {{ GBP.format(modelValue.totals.monthly_vat) }}</p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a class="btn btn-outline-primary me-3" :href="route('quotes.edit', { quote: modelValue.id}) + '?step=3'">
                            <font-awesome-icon :icon="['fas', 'arrow-left']"></font-awesome-icon> Back</a>
                        <div>
                            <button class="btn btn-primary">Email this quote
                                <font-awesome-icon :icon="['fas', 'arrow-right']"></font-awesome-icon></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col"></div>
            <div class="col-lg-4">
                <div class="price-mobile-sidebar px-4 pt-4 pb-2 mb-3" style="position: sticky; top: 25px">
                    <h3 class="mb-3">Your quote: #{{ modelValue.id }}</h3>

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
                    <div class="sidebar_items border-bottom mb-4">
                        <div class="item mb-2">
                            <span><b>One-off Subtotal</b></span>
                            <span class="badge bg-light-grey dark">
                                {{ GBP.format(modelValue.totals.oneoff_vat) }}
                            </span>
                        </div>
                    </div>
                    <p class="payment-info px-3">
                        1 payment of
                        {{ GBP.format(modelValue.totals.oneoff_vat) }} followed
                        by 12 monthly payments of {{ GBP.format(modelValue.totals.monthly_vat) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
