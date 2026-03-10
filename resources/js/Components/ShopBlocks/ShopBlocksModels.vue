<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { categories } from '@composables/custom/shopblocks';

const props = defineProps({
    category: Object,
    modelValue: Object
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const getFilters = () => {
    let array = props.category ? [props.category] : [];
    let items = [];
    if (props.category) {
        do {
            let current = array.pop();
            items.push(current);
            let temp = categories.value.filter((x) => x.parent_id == current.id);
            array.push(...temp);
        } while (array.length != 0);
    }
    return items.length ? [{ column: 'products.category.id', operator: 'in', value: items.map((x) => x.id) }] : [];
};
</script>
<template>
    <InputSelectMultiple
        id="select-model"
        url="/shop-blocks/models"
        :fields="['name']"
        v-model="proxyValue"
        placeholder="Select a Model"
        :query="getFilters()"
    >
    </InputSelectMultiple>
    <!-- <ul style="padding: 0; list-style: none">
        <li v-if="proxyValue">
            <div
                class="btn btn-light d-flex justify-content-between pointer btn-category-current"
                @click="proxyValue = null"
            >
                <p class="m-0 text-left">{{ proxyValue.name }}</p>
            </div>
        </li>
        <template v-for="model in models">
            <li v-if="proxyValue?.id != model.id">
                <div
                    class="btn btn-light d-flex justify-content-between pointer"
                    :class="[model.id == proxyValue?.id ? 'btn-category-current' : '']"
                    @click="proxyValue = model"
                >
                    <p class="m-0 text-left">{{ model.name }}</p>
                </div>
            </li>
        </template>
        <li v-if="meta.current != meta.last_page">
            <div class="btn btn-light d-flex justify-content-between pointer" @click="nextPage()">
                <p class="m-0 text-left">Load More...</p>
            </div>
        </li>
    </ul> -->
</template>
