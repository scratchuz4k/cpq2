<template>
    <button class="btn btn-primary" @click="showModal = true">
        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
        New Product
    </button>
    <Dialog
        v-model:visible="showModal"
        modal
        :style="{ marginTop: '4rem', minWidth: '60vw' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
        position="top"
    >
        <template #header>
            <h2>Add Products</h2>
        </template>
        <div class="mb-3">
            <p class="bold">Filter</p>

            <IconField>
                <InputIcon>
                    <font-awesome-icon :icon="['fas', 'search']" />
                </InputIcon>
                <InputText v-model="search" type="search" placeholder="Search" size="small" />
            </IconField>
        </div>
        <table class="table">
            <thead>
                <tr class="relation-table-header">
                    <th style="background-color: lightgray"></th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Categories</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="product in api.items"
                    :key="product.id"
                    :class="{ 'relation-table-active': !!checked.find((temp) => temp.id === product.id) }"
                >
                    <td>
                        <Checkbox
                            class="form-check-input"
                            @change="(ev) => toggleChecked(product)"
                            :id="'checkbox-' + product.id"
                            :checked="!!checked.find((temp) => temp.id === product.id)"
                        />
                    </td>
                    <td>
                        <p>{{ product.sku }}</p>
                    </td>
                    <td>
                        <p>{{ product.name }}</p>
                    </td>
                    <td>
                        <p>{{ product.description }}</p>
                    </td>
                    <td>
                        <p>{{ product.categories.map((category) => category.name).join(', ') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <Paginator
            v-model:first="api.meta.first"
            :rows="api.meta.limit"
            :totalRecords="api.meta.total"
            :rowsPerPageOptions="[10, 20, 50]"
            @page="api.handlePaginator"
        >
        </Paginator>
        <div class="d-flex justify-content-end">
            <div v-if="showProgress" class="d-flex justify-content-center align-items-center me-2">
                <p class="small">Adding {{ checked[current].name }}...</p>
                <progress :value="current" :max="checked.length - 1" class="w-full h-2"></progress>
            </div>
            <button class="btn btn-outline-secondary me-2" @click="showModal = false">Cancel</button>
            <button class="btn btn-primary" @click="addProducts">Add</button>
        </div>
    </Dialog>
</template>
<script setup>
import { IconField, InputIcon, InputText } from 'primevue';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import { ref, reactive, watch } from 'vue';
import { Api, FilterOperators } from '@composables/useApi';
import _ from 'lodash';

const props = defineProps({
    product: Object,
    route: String,
    column: String
});
const current = ref(0);
const showModal = ref(false);
const showProgress = ref(false);
const checked = ref([]);
const search = ref(null);

const api = reactive(new Api('products'));

const getData = _.debounce(async () => {
    api.addGroupFilter('main', 'id', FilterOperators.NOT_EQUALS, props.product.id);
    api.addGroupFilter('parent', 'parents.id', FilterOperators.IS_NULL, props.product.id);
    api.setSearch(search.value);
    await api.getData();
}, 500);

const toggleChecked = (product) => {
    let index = checked.value.findIndex((temp) => temp.id === product.id);
    if (index > -1) {
        checked.value.splice(index, 1);
    } else {
        checked.value.push(product);
    }
};

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const addProducts = async () => {
    showProgress.value = true;
    for (current.value = 0; current.value < checked.value.length; current.value++) {
        // wait 1 second before next iteration
        await sleep(200);
        let item = checked.value[current.value];
        try {
            let body = {
                product_id: props.product.id,
                [props.column]: item.id
            };

            await axios.post('/api/' + props.route, body);
        } catch (ex) {
            notyf.error(ex.response.data.message);
        }
    }
    notyf.success('Products added successfully');
    getData();
    showProgress.value = false;
    checked.value = [];
    emitter.emit('datatable-refresh:product-links');
};
watch(search, () => {
    getData();
});
getData();
</script>
<style scoped>
.relation-table-header > th {
    background-color: lightgray;
    color: gray;
    font-size: 14px;
}
.relation-table-active > td {
    background-color: var(--lighter-grey);
}
</style>
