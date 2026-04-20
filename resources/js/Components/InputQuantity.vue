<template>
    <div class="d-flex w-100">
        <button
            :id="'btn-' + props.id + '-remove-1'"
            class="btn-quantity btn-minus-quantity"
            type="button"
            @click="() => proxyValue--"
            :disabled="!proxyValue"
        >
            <font-awesome-icon :icon="['fas', 'minus']" />
        </button>
        <input
            :id="'input-' + props.id + '-quantity'"
            type="number"
            :value="proxyValue"
            :max="props.maxValue"
            class="input-quantity"
            @input="checkValue($event)"
        />
        <button
            :id="'btn-' + props.id + '-add-1'"
            class="btn-quantity btn-add-quantity"
            type="button"
            @click="() => proxyValue++"
            :disabled="props.disabled || proxyValue >= props.maxValue"
        >
            <font-awesome-icon :icon="['fas', 'plus']" />
        </button>
    </div>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({
    id: String,
    modelValue: Number,
    disabled: Boolean,
    maxValue: {
        type: Number,
        default: 9999
    }
});

const emit = defineEmits(['update:modelValue', 'change']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
        emit('change', val);
    }
});

const checkValue = (event) => {
    const temp = parseInt(event.target.value, 10);

    let newValue = temp;
    if (isNaN(newValue)) {
        newValue = 0;
    }
    const cap = Number.isFinite(props.maxValue) ? props.maxValue : 9999;
    if (newValue > cap) {
        newValue = cap;
    }

    proxyValue.value = newValue;
    event.target.value = String(newValue);
};
</script>
<style scoped>
.input-quantity {
    height: 32px;
    padding: 8px 12px 8px 12px;
    gap: 8px;
    border-width: 1px;
    border-style: solid;
    border-color: #bdbdbd;
    text-align: center;
}

.btn-quantity {
    outline: none;
    border-style: solid;
    border-color: #bdbdbd;
}

.btn-minus-quantity {
    width: 56px;
    height: 32px;
    gap: 0px;
    border-radius: 6px 0px 0px 6px;
    background: var(--color-white);
    border-width: 1px 0px 1px 1px;
    border-style: solid;
    border-color: #bdbdbd;
}

.btn-add-quantity {
    width: 56px;
    height: 32px;
    gap: 0px;
    border-radius: 0px 6px 6px 0px;
    background: var(--color-white);
    border-width: 1px 1px 1px 0px;
}
</style>
