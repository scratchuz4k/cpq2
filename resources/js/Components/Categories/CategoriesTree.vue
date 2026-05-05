<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    categories: Array,
    level: {
        default: 0,
        type: Number
    },
    modelValue: Object
});

const drillDown = ref([]);
const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const selectCategory = (category) => {
    drillDown.value.push(category);
    proxyValue.value = category;
};

const rollback = (item) => {
    if (item == null) {
        drillDown.value = [];
        proxyValue.value = null;
        return;
    }
    let index = drillDown.value.findIndex((x) => x.id == item.id);
    drillDown.value.splice(index + 1, drillDown.value.length - (index + 1));
    proxyValue.value = item;
};

const styles = {
    0: { backgroundColor: 'white' },
    1: { backgroundColor: 'white', border: '1px solid black', borderWidth: '1px 0 0 0' },
    2: {
        backgroundColor: 'white',
        padding: '0 0 0 10px',
        border: '1px solid black',
        borderWidth: '1px 0 0 0'
    },
    3: {
        backgroundColor: 'lightgray',
        padding: '0 0 0 10px',
        border: '1px solid black',
        borderWidth: '1px 0 0 0'
    }
};
</script>
<template>
    <div class="d-flex">
        <ul style="margin: 0; padding: 5px 0; list-style: none" v-if="!!drillDown.length" class="d-flex">
            <li class="me-1 border-1 mb-1">
                <div class="btn btn-category d-flex justify-content-between py-1" @click="rollback(null)">
                    <p class="m-0">All</p>
                </div>
            </li>
            <li v-for="(item, index) in drillDown" class="me-1 border-1 mb-1" @click="rollback(item)">
                <div
                    class="btn btn-category d-flex justify-content-between py-1"
                    :class="[drillDown.length == index + 1 ? 'btn-category-current' : '']"
                    @click="rollback(item)"
                >
                    <p class="m-0">{{ item.name }}</p>
                </div>
            </li>
        </ul>
        <ul style="margin: 0; padding: 5px 0; list-style: none" :style="styles[props.level]" class="d-flex">
            <template v-for="category in categories.filter((x) => x.parent_id == proxyValue?.id)">
                <li class="me-1 border-1 mb-1">
                    <div
                        class="btn btn-light d-flex justify-content-between pointer py-1"
                        @click="selectCategory(category)"
                    >
                        <p class="m-0 text-left">{{ category.name }}</p>
                        <div class="" v-if="categories.find((x) => x.parent_id == category.id)">
                            <font-awesome-icon class="ps-2" :icon="['fas', 'arrow-right']" />
                        </div>
                    </div>
                </li>
            </template>
        </ul>
    </div>
</template>
