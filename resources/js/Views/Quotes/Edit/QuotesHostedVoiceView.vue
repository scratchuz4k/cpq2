<script setup>
import _ from 'lodash';
import { quote, setQuote } from '@composables/useQuote';
import axios from 'axios';
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import queryString from 'query-string';
import format from '@/Composables/format.js';

const props = defineProps({
    modelValue: Object
});

const page = usePage();
const tenant = page.props.tenant;

const solution_type = props.modelValue.solution_type;
const active_steps = ref([]);
const loading = ref(true);

const getStepsWithProducts = async () => {
    loading.value = true;
    let query = queryString.stringify({
        limit: 200,
        filters: JSON.stringify([
            {
                column: 'id',
                operator: 'in',
                value: solution_type.steps.map((x) => x.id).join(',')
            },
            {
                column: 'products.id',
                operator: 'not_equals',
                value: ''
            },
            {
                column: 'is_active',
                operator: 'equals',
                value: 1
            }
        ])
    });
    let res = await axios.get('/api/steps' + '?' + query);

    const steps_order = solution_type.steps.map((x) => x.id);

    active_steps.value = res.data.data.sort((a, b) => {
        return steps_order.indexOf(a.id) - steps_order.indexOf(b.id);
    });
    loading.value = false;
};

const urlParams = new URLSearchParams(window.location.search);
const params = new Proxy(urlParams, {
    get: (searchParams, prop) => searchParams.get(prop)
});
const current_step = ref(params['current_step'] ?? 0);

setQuote(props.modelValue);

const saveQuote = async () => {
    if (current_step.value == active_steps.value.length - 1) {
        router.get(`/quotes/${props.modelValue.id}`);
        return;
    }
    current_step.value++;
};

emitter.on('save-quote', () => {
    saveQuote();
});

getStepsWithProducts();
</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
            <div class="col-lg-7">
                <div v-if="!loading">
                    <div v-for="(step, index) in active_steps">
                        <StepProductsView
                            :step="step"
                            :account="props.modelValue.account"
                            v-model="current_step"
                            :index="index"
                            :total_steps="active_steps.length"
                        >
                        </StepProductsView>
                    </div>
                </div>
            </div>
            <div v-if="active_steps.length" class="col-lg-4 order-first order-lg-last mb-5">
                <div style="position: sticky; top: 25px; max-width: 82%; margin-right: 0; margin-left: auto">
                    <InfoBox>
                        <div style="font-size: 14px">
                            <b style="font-size: 16px">Quote {{ modelValue.id }} for {{ modelValue.account.name }}</b>
                            <br />
                            Contact Number: 0124567890<br />
                            Email: firstname.lastname@gmail.com
                        </div>
                    </InfoBox>
                    <InfoBox v-if="['QUOTE', 'PRICE', 'CONFIGURE'].includes(modelValue.status)">
                        <div style="font-size: 14px">
                            This quote will expire on
                            <b style="font-size: 16px">
                                {{
                                    format.expireDate(
                                        modelValue.created_at,
                                        tenant.configurations?.quote_expiration_days
                                    )
                                }}
                            </b>
                        </div>
                    </InfoBox>
                    <div class="configure-mobile-sidebar px-4 py-4">
                        <div v-for="(step, index) in active_steps">
                            <h4 id="sidebar_products" data-total="0">{{ index + 1 }}. {{ step.name }}</h4>
                            <div class="sidebar_items mb-3">
                                <div v-for="product in step.products" class="item mb-2">
                                    <span>{{ product.name }}</span>
                                    <span class="badge bg-light-grey dark">
                                        {{ quote.items.find((x) => x.product_id == product.id)?.quantity ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button id="next-step-btn" class="btn btn-primary mt-2" @click="saveQuote()">Next</button>
                    </div>
                </div>
            </div>
            <div v-if="!active_steps.length && !loading" class="col-12">
                <div class="text-center py-5">
                    <h5 class="mb-2">No steps available</h5>
                    <p class="mb-0">There are no active step with products for this solution.</p>
                </div>
            </div>
        </div>
    </div>
</template>
