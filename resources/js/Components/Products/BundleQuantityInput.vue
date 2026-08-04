<script setup>
import { nextTick, ref, watch } from 'vue';
import { checkValue, integerOnly } from '@/Composables/input.js';

const props = defineProps({
    data: Object,
    value: Number
});

const quantity = ref(props.value ?? 1);
const isEditing = ref(false);
const input = ref(null);

watch(
    () => props.value,
    (newValue) => {
        quantity.value = newValue ?? 1;
    }
);

const edit = async () => {
    isEditing.value = true;
    await nextTick();
    input.value?.focus();
};

const update = async () => {
    isEditing.value = false;

    if (Number(quantity.value) === Number(props.value)) return;

    try {
        await axios.put(`/api/product-bundles/${props.data.id}`, { quantity: quantity.value });
        props.data.quantity = quantity.value;
        notyf.success('Updated with Success');
    } catch (err) {
        quantity.value = props.value ?? 1;
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};
</script>
<template>
    <div class="d-flex justify-content-center align-items-center">
        <input
            v-if="isEditing"
            ref="input"
            class="form-control text-center p-0 px-1"
            style="width: 80px"
            type="number"
            min="1"
            v-model="quantity"
            @keydown="integerOnly"
            @keydown.enter="input?.blur()"
            @input="checkValue"
            @blur="update"
        />
        <button
            v-else
            type="button"
            class="btn border-0 bg-transparent p-0 d-flex align-items-center gap-2"
            @click="edit"
        >
            <font-awesome-icon class="text-primary" :icon="['far', 'pen-to-square']" />
            <p class="small mb-0 text-body">{{ quantity }}</p>
        </button>
    </div>
</template>
