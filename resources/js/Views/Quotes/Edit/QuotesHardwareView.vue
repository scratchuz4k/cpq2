<script setup>
import { quote, setQuote } from '@composables/useQuote';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { active_steps, getStepsWithProducts, loading } from '@/Composables/useQuoteSteps.js';

const props = defineProps({
    modelValue: Object
});

const solution_type = props.modelValue.solution_type;

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

getStepsWithProducts(solution_type);
</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
            <div class="col">
                <template v-if="!loading" v-for="(step, index) in active_steps">
                    <StepProductsView
                        :step="step"
                        :account="props.modelValue.account"
                        v-model="current_step"
                        :index="index"
                        :total_steps="active_steps.length + 1"
                        :quote="quote"
                    >
                    </StepProductsView>
                </template>

                <ShopBlocksProductsView
                    v-if="
                        !loading &&
                        ((current_step == active_steps.length && current_step > 0) ||
                            (active_steps.length == 0 && current_step == 0))
                    "
                    :current_step="current_step"
                    :quote="quote"
                    v-model="current_step"
                    @save="saveQuote"
                >
                </ShopBlocksProductsView>
            </div>
        </div>
    </div>
</template>
