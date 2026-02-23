<script setup>
import format from '@/Composables/format';
import { computed, onMounted, watch } from 'vue';
import { isDisabled } from '@/Composables/useQuoteSteps.js';

const props = defineProps({
    solutionType: Array,
    modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'nextStep']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});
const icons = {
    3: 'fas fa-ethernet',
    4: 'fas fa-mobile-screen-button',
    5: 'fas fa-microchip',
    6: 'fas fa-cloud-arrow-down',
    7: 'fas fa-cloud',
    8: 'fas fa-phone',
    9: 'fas fa-shield-alt',
    10: 'fab fa-microsoft',
    11: 'fas fa-building-shield'
};

onMounted(() => {
    isDisabled.value = proxyValue.value.solution_type === null;
});

watch(
    () => [proxyValue.value.solution_type],
    () => {
        if (proxyValue.value.solution_type === null) {
            isDisabled.value = true;
        } else {
            isDisabled.value = false;
        }
    }
);
</script>

<template>
    <h5 class="font-bold mb-3">Now, select an solution type</h5>
    <div class="row">
        <div class="col-12">
            <template v-if="props.solutionType?.length">
                <div class="row row-cols-4 mb-3">
                    <div class="col mb-2" v-for="solution in props.solutionType">
                        <div
                            :id="'solution_type-' + format.toSnakeCase(solution.name)"
                            class="option"
                            @click="() => (proxyValue.solution_type = solution)"
                            :class="[proxyValue.solution_type == solution ? 'active' : '']"
                        >
                            <font-awesome-icon :icon="icons[solution.code]" class="me-3"></font-awesome-icon>
                            <b>{{ solution.name }}</b>
                        </div>
                    </div>
                </div>
                <div class="pt-2 pb-4">
                    <p class="text-center" v-if="proxyValue.solution_type?.estimated_time">
                        Estimated time to price: {{ proxyValue.solution_type?.estimated_time }}
                    </p>
                </div>
            </template>
            <div v-else class="py-5 text-primary fst-italic">
                <font-awesome-icon class="me-2" :icon="['fas', 'circle-exclamation']"></font-awesome-icon>
                No solution types available
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.option {
    cursor: pointer;
    background-color: var(--white);
    border-radius: var(--radius);
    padding: 25px 20px;
    margin-bottom: 10px;
    border: 3px solid var(--white);
    transition:
        border-color 0.1s ease-in-out,
        box-shadow 0.1s ease-in-out;
    box-shadow: 0px 10px 15px -3px var(--light-grey);
}
.option:hover,
.option.active {
    border: 3px solid var(--color-purple);
}
.option.active {
    border: 3px solid var(--color-purple);
    box-shadow: 0px 10px 15px -3px var(--color-purple-light);
}
</style>
