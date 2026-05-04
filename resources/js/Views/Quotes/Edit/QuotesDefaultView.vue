<script setup>
import _ from 'lodash';
import { quote, setQuote } from '@composables/useQuote';
import axios from 'axios';
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import queryString from 'qs';
import format from '@/Composables/format.js';
import { getStepsWithProducts, loading } from '@/Composables/useQuoteSteps.js';

const props = defineProps({
    modelValue: Object
});

const page = usePage();
const tenant = page.props.tenant;

const urlParams = new URLSearchParams(window.location.search);
const params = new Proxy(urlParams, {
    get: (searchParams, prop) => searchParams.get(prop)
});
const current_step = ref(params['current_step'] ?? 0);
const solution_type = props.modelValue.solution_type;
const active_steps = ref([]);

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

getStepsWithProducts(solution_type);
</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
            <div class="col">
                <div v-if="!loading">
                    <div v-for="(step, index) in active_steps">
                        <StepProductsView
                            v-if="index === current_step"
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
            <div v-if="!active_steps.length && !loading" class="col-12">
                <div class="text-center py-5">
                    <h5 class="mb-2">No steps available</h5>
                    <p class="mb-0">There are no active step with products for this solution.</p>
                </div>
            </div>
        </div>
    </div>
</template>
