<script setup>
import { computed } from 'vue';
import queryString from 'qs';
import { categories, drillDown } from '@composables/custom/shopblocks';

const props = defineProps({
    categories: {
        default: [],
        type: Array
    },
    level: {
        default: 0,
        type: Number
    },
    modelValue: Object,
    defaultCategory: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const getShopBlocksCategories = async () => {
    categories.value = [];
    let current_page = 1;
    let last_page = 1;
    let filters = props.defaultCategory
        ? [
              {
                  column: 'id',
                  operator: 'equals',
                  value: props.defaultCategory
              }
          ]
        : [];

    if (props.defaultCategory) {
        filters.push({ column: 'id', operator: 'equals', value: props.defaultCategory });
    }

    do {
        let string = queryString.stringify({
            limit: 500,
            page: current_page,
            filters: filters
        });
        let res = await axios.get('/api/shop-blocks/categories?' + string);
        categories.value.push(...res.data.data);
        last_page = res.data.meta?.last_page;
        current_page++;
    } while (current_page != last_page + 1);
};

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

getShopBlocksCategories();
</script>
<template>
    <ul style="margin: 0; padding: 5px 0; list-style: none" v-if="!!drillDown.length">
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
    <ul style="padding: 0; list-style: none" :style="styles[props.level]">
        <template
            v-if="categories.length > 1"
            v-for="category in categories.filter((x) => x.parent_id == proxyValue?.id)"
        >
            <li>
                <div class="btn btn-light d-flex justify-content-between pointer" @click="selectCategory(category)">
                    <p class="m-0 text-left">{{ category.name }}</p>
                    <div class="" v-if="categories.find((x) => x.parent_id == category.id)">
                        <font-awesome-icon :icon="['fas', 'arrow-right']" />
                    </div>
                </div>
            </li>
        </template>
        <template v-else>
            <li>
                <div class="btn btn-light d-flex justify-content-between pointer">
                    <p class="m-0 text-left">{{ categories[0]?.name }}</p>
                </div>
            </li>
        </template>
    </ul>
</template>
