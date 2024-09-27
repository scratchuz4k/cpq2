<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue'

const props = defineProps({
    modelValue: Object
})

const hardwareProducts = ref([])

const getHardwareProducts = () => {
    props.modelValue.items.forEach(element => {
        hardwareProducts.value.push(element)
    });
}
const remove = async (id) => {
    try {
        await axios.delete(`/api/order-items/${id}`)
        router.get(`/orders/${props.modelValue.id}`)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

getHardwareProducts()

</script>
<template>

    <div class="panel panel-mobile">
        <div class="row">
            <div class="col-lg-8 mb-5 price-summary">
                <InfoOrder :order="modelValue"></InfoOrder>
                <div class="mb-5">
                    <div
                        class="mobile-price-group-heading border-bottom mb-3 d-flex justify-content-between align-items-center">
                        <h3>Hardware</h3>
                        <!-- <a href="/quotes/mobile/configure/{{ $order->id }}?s=3">Make a change</a> -->
                    </div>
                    <div>
                        <div v-for="p in hardwareProducts" class="item mb-2">
                            <span>{{ p.name }}</span>
                            <div>
                                <button v-if="p.id" class="badge btn btn-danger btn-sm me-2" @click="remove(p.id)">
                                    <font-awesome-icon :icon="['fas', 'trash']"></font-awesome-icon>
                                </button>
                                <span class="badge bg-light-grey dark">{{ p.quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
