<template>
    <button :id="'btn-add-' + id" class="btn btn-primary" @click="showModal = true">
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
        :pt="{ id: props.id + '-dialog' }"
    >
        <template #header>
            <h2>Add Products</h2>
        </template>
        <div class="mb-3">
            <p class="big bold mb-3">Filter</p>

            <div class="d-flex flex-wrap gap-3">
                <div>
                    <label for="search-sku" class="form-label">SKU</label>
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon :icon="['fas', 'search']" />
                        </InputIcon>
                        <InputText
                            :id="props.id + '-search-sku'"
                            v-model="search.sku"
                            type="search"
                            placeholder="Search"
                            size="small"
                        />
                    </IconField>
                </div>
                <div>
                    <label for="search-name" class="form-label">Product Name</label>
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon :icon="['fas', 'search']" />
                        </InputIcon>
                        <InputText
                            :id="props.id + '-search-name'"
                            v-model="search.name"
                            type="search"
                            placeholder="Search"
                            size="small"
                        />
                    </IconField>
                </div>
                <div>
                    <label for="search-category" class="form-label">Category</label>
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon :icon="['fas', 'search']" />
                        </InputIcon>
                        <InputText
                            :id="props.id + '-search-category'"
                            v-model="search.category"
                            type="search"
                            placeholder="Search"
                            size="small"
                        />
                    </IconField>
                </div>
            </div>
        </div>
        <div style="height: 40vh; overflow-y: auto">
            <table :id="props.id + '-table'" class="table">
                <thead>
                    <tr class="relation-table-header">
                        <th style="background-color: lightgray"></th>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Categories</th>
                    </tr>
                </thead>
                <tbody v-if="!api.isLoading">
                    <tr
                        v-for="(product, index) in api.items"
                        :key="product.id"
                        :class="{ 'relation-table-active': !!checked.find((temp) => temp.id === product.id) }"
                    >
                        <td>
                            <Checkbox
                                @change="(ev) => toggleChecked(product)"
                                :id="props.id + '-checkbox-' + index"
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
                <tbody v-else>
                    <tr>
                        <td colspan="5" class="text-center">
                            <font-awesome-icon :icon="['fas', 'spinner']" spin size="2x" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

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
            <div v-else-if="checked.length > 0" class="d-flex justify-content-center align-items-center me-2">
                <p class="small">{{ checked.length }} selected ...</p>
            </div>
            <button
                :id="'btn-relation-cancel-' + props.id"
                class="btn btn-outline-secondary me-2"
                @click="showModal = false"
            >
                Cancel
            </button>
            <button :id="'btn-relation-add-' + props.id" class="btn btn-primary" @click="addProducts">Add</button>
        </div>
    </Dialog>
</template>
<script setup>
import { IconField, InputIcon, InputText } from 'primevue';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import { ref, reactive, watch } from 'vue';
import { Api, FilterOperators } from '../../Composables/useApi';
import _ from 'lodash';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`;
        }
    },
    product: Object,
    route: String,
    column: String,
    queries: Array
});
const current = ref(0);
const showModal = ref(false);
const showProgress = ref(false);
const checked = ref([]);
const search = reactive({
    sku: null,
    name: null,
    category: null
});

const api = reactive(new Api('products'));

const getData = _.debounce(async () => {
    api.clearFilters();
    api.addGroupFilter('main', 'id', FilterOperators.NOT_EQUALS, props.product.id);

    props.queries.forEach((query) => {
        api.addGroupFilter(query.group, query.column, query.operator, query.value);
    });

    if (search.sku) {
        api.addGroupFilter('product', 'sku', FilterOperators.CONTAINS, search.sku);
    }
    if (search.name) {
        api.addGroupFilter('product', 'name', FilterOperators.CONTAINS, search.name, 'orWhere');
        api.addGroupFilter('product', 'description', FilterOperators.CONTAINS, search.name);
    }
    if (search.category) {
        api.addGroupFilter('category', 'categories.name', FilterOperators.CONTAINS, search.category);
    }
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
    api.meta.page = 1;
    getData();
    showProgress.value = false;
    checked.value = [];
    emitter.emit('datatable-refresh:' + props.route);
};
watch(search, () => {
    getData();
});

watch(showModal, () => {
    if (showModal.value) {
        api.isLoading = true;
        getData();
    }
});
</script>
<style scoped>
.relation-table-header > th {
    position: sticky;
    top: 0;
    background-color: lightgray;
    color: gray;
    font-size: 14px;
}
.relation-table-active > td {
    background-color: var(--lighter-grey);
}
</style>
