<script setup>
import { computed } from 'vue';
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
    6: 'fas fa-cloud'
};
</script>

<template>
    <h5 class="font-bold mb-3">Now, select an solution type</h5>
    <div class="row row-cols-5 mb-3">
        <div class="col mb-3" v-for="solution in props.solutionType">
            <div
                class="option"
                @click="() => (proxyValue.solution_type = solution)"
                :class="[proxyValue.solution_type == solution ? 'active' : '']"
            >
                <font-awesome-icon :icon="icons[solution.code]" class="me-3"></font-awesome-icon>
                {{ solution.name }}
            </div>
        </div>
    </div>
    <ButtonStep v-model="proxyValue" :disabled="proxyValue.solution_type == null"></ButtonStep>
</template>

<style lang="scss" scoped>
.option {
    background-color: var(--white);
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 30px;
}
.option:hover {
    box-shadow: var(--color-purple) 0px 0px 0px 2px, var(--color-purple) 0px 4px 6px -1px,
        var(--color-purple) 0px 1px 0px inset;
    cursor: pointer;
}
.active {
    box-shadow: var(--color-purple) 0px 0px 0px 2px, var(--color-purple) 0px 4px 6px -1px,
        var(--color-purple) 0px 1px 0px inset;
}
</style>
