<script setup>
import { computed, reactive, ref, watch } from 'vue';
import queryString from 'query-string';
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
const meta = reactive({
    last_page: 0,
    current: 1
});
const models = ref([]);
const getShopBlocksModels = async () => {
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
    let string = queryString.stringify({
        limit: 10,
        page: meta.current,
        filters: JSON.stringify(
            items.length ? [{ column: 'products.category.id', operator: 'in', value: items.map((x) => x.id) }] : []
        )
    });
    let res = await axios.get('/api/shop-blocks/models?' + string);
    meta.last_page = res.data.meta?.last_page;
    models.value.push(...res.data.data);
};

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

const nextPage = () => {
    meta.current++;
    getShopBlocksModels();
};

watch(
    () => props.category,
    () => {
        meta.current = 1;
        meta.last_page = 0;
        models.value = [];
        getShopBlocksModels();
    }
);

getShopBlocksModels();
</script>
<template>
    <InputSelect
        id="select-model"
        url="/shop-blocks/models"
        :fields="['name']"
        v-model="proxyValue"
        placeholder="Select a Model"
        :query="getFilters()"
    >
    </InputSelect>
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
