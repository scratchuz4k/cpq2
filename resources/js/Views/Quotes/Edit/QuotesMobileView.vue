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
    if (current_step.value == active_steps.value.length) {
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
            <div class="col">
                <div v-if="!loading">
                    <div v-for="(step, index) in active_steps">
                        <StepProductsView
                            :step="step"
                            :account="props.modelValue.account"
                            v-model="current_step"
                            :index="index"
                            :total_steps="active_steps.length + 1"
                        >
                        </StepProductsView>
                    </div>

                    <ShopBlocksProductsView
                        v-if="
                            (current_step == active_steps.length && current_step > 0) ||
                            (active_steps.length == 0 && current_step == 0)
                        "
                        :current_step="current_step"
                        :quote="quote"
                        :category="22"
                        v-model="current_step"
                        @save="saveQuote"
                    >
                    </ShopBlocksProductsView>
                </div>
            </div>
        </div>
    </div>
</template>
