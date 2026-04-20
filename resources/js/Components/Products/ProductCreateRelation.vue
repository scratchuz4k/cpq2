<template>
    <button :id="'btn-add-' + id" class="btn btn-primary" @click="showModal = true">
        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
        Add Product
    </button>
    <Dialog
        v-model:visible="showModal"
        modal
        :style="{ marginTop: '4rem', minWidth: '60vw' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
        position="top"
        :dismissableMask="true"
        :pt="{ id: props.id + '-dialog' }"
        @hide="closeModal()"
    >
        <template #header>
            <h2>Add Products</h2>
        </template>
        <div class="mb-3">
            <div
                v-if="props.warningMessage"
                class="alert alert-warning d-flex align-items-center gap-2 mb-3 small"
                role="alert"
            >
                <font-awesome-icon :icon="['fas', 'circle-info']" />
                {{ props.warningMessage }}
            </div>

            <div class="row">
                <div class="col">
                    <label for="search-global" class="form-label">Search</label>
                    <IconField class="w-100">
                        <InputIcon>
                            <font-awesome-icon :icon="['fas', 'search']" />
                        </InputIcon>
                        <InputText
                            :id="props.id + '-search-global'"
                            v-model="search.global"
                            type="search"
                            placeholder="Search"
                            size="small"
                            class="w-100 py-2"
                        />
                    </IconField>
                </div>
                <div class="col">
                    <label for="search-category" class="form-label">Category</label>

                    <InputSelectMultiple
                        id="select-category"
                        url="/categories"
                        class="w-100"
                        :fields="['name']"
                        v-model="search.categories"
                        placeholder="Select a Categories"
                    ></InputSelectMultiple>
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
                        <th>Steps</th>
                    </tr>
                </thead>
                <tbody v-if="!api.isLoading">
                    <tr
                        v-for="(product, index) in api.items"
                        :key="product.id"
                        :class="{
                            'relation-table-active': !!checked.find((temp) => temp.id === product.id),
                            'relation-table-disabled': props.isRowDisabled(product)
                        }"
                    >
                        <td>
                            <Checkbox
                                v-if="!props.isRowDisabled(product)"
                                @change="(ev) => toggleChecked(product)"
                                :id="props.id + '-checkbox-' + index"
                                :checked="!!checked.find((temp) => temp.id === product.id)"
                            />
                        </td>
                        <td>
                            <p class="small">{{ product.sku }}</p>
                        </td>
                        <td>
                            <p class="small">{{ product.name }}</p>
                        </td>
                        <td>
                            <p class="small">{{ product.description }}</p>
                        </td>
                        <td>
                            <p class="small" v-if="product.categories.length == 1">
                                {{ product.categories[0].name }}
                            </p>
                            <p
                                class="small"
                                v-else-if="product.categories.length > 1"
                                v-tooltip
                                data-bs-toggle="tooltip"
                                data-bs-placement="bottom"
                                :title="
                                    product.categories
                                        .slice(1)
                                        .map((category) => category.name)
                                        .join(', ')
                                "
                                style="cursor: pointer"
                            >
                                {{ product.categories[0].name }}
                                <span class="color-purple-dark fw-semibold ms-1"
                                    >+{{ product.categories.length - 1 }}</span
                                >
                            </p>
                        </td>

                        <td>
                            <p class="small" v-if="product.steps.length == 1">
                                {{ product.steps[0].name }}
                            </p>
                            <p
                                class="small"
                                v-else-if="product.steps.length > 1"
                                v-tooltip
                                data-bs-toggle="tooltip"
                                data-bs-placement="bottom"
                                :title="
                                    product.steps
                                        .slice(1)
                                        .map((step) => step.name)
                                        .join(', ')
                                "
                                style="cursor: pointer"
                            >
                                {{ product.steps[0].name }}
                                <span class="color-purple-dark fw-semibold ms-1">+{{ product.steps.length - 1 }}</span>
                            </p>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="100%" class="text-center">
                            <font-awesome-icon :icon="['fas', 'spinner']" spin size="2x" />
                        </td>
                    </tr>
                </tbody>
                <tbody v-if="!api.items.length && !api.isLoading">
                    <tr>
                        <td colspan="100%">
                            <p class="w-full text-center">No records found</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center align-items-center" style="position: relative">
            <p class="small text-muted" style="position: absolute; left: 0; top: 0">{{ api.meta.total }} total</p>
            <Paginator
                v-model:first="api.meta.first"
                :rows="api.meta.limit"
                :totalRecords="api.meta.total"
                :rowsPerPageOptions="[10, 20, 50]"
                @page="api.handlePaginator"
            >
            </Paginator>
        </div>
        <div class="d-flex justify-content-end">
            <div v-if="showProgress" class="d-flex justify-content-center align-items-center me-2">
                <p class="small">Adding {{ checked[current].name }}...</p>
                <progress :value="current" :max="checked.length - 1" class="w-full h-2"></progress>
            </div>
            <div v-else-if="checked.length > 0" class="d-flex justify-content-center align-items-center me-2">
                <p class="small">{{ checked.length }} selected</p>
            </div>
            <button
                :id="'btn-relation-cancel-' + props.id"
                class="btn btn-outline-secondary me-2"
                @click="showModal = false"
            >
                Cancel
            </button>
            <button
                :id="'btn-relation-add-' + props.id"
                class="btn btn-primary"
                @click="addProducts"
                :disabled="isDisabled || checked.length === 0"
            >
                Add
            </button>
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
    queries: Function,
    warningMessage: String,
    isRowDisabled: {
        type: Function,
        default: () => false
    }
});

const emit = defineEmits(['product-added']);
const current = ref(0);
const showModal = ref(false);
const showProgress = ref(false);
const isDisabled = ref(false);
const checked = ref([]);
const search = reactive({
    sku: null,
    name: null,
    categories: []
});

const api = reactive(new Api('products'));

const getData = _.debounce(async () => {
    api.clearFilters();
    api.addGroupFilter('main', 'id', FilterOperators.NOT_EQUALS, props.product.id);

    if (props.queries) {
        props.queries().forEach((query) => {
            api.addGroupFilter(query.group, query.column, query.operator, query.value);
        });
    }

    if (search.global) {
        api.setSearch(search.global);
    }

    if (search.categories.length > 0) {
        search.categories.forEach((category) => {
            api.addGroupFilter('category', 'categories.name', FilterOperators.CONTAINS, category.name);
        });
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

const resetState = () => {
    api.meta.page = 1;
    getData();
    showProgress.value = false;
    isDisabled.value = false;
    checked.value = [];
};

const addProducts = async () => {
    showProgress.value = true;
    isDisabled.value = true;

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
            notyf.error('Error on ' + item.name + ': ' + ex.response.data.message);
            resetState();
            return;
        }
    }
    emit('product-added');
    notyf.success('Products added successfully');
    resetState();
    emitter.emit('datatable-refresh:' + props.route);
};

const closeModal = () => {
    showModal.value = false;
    search.global = null;
    search.categories = [];
    resetState();
};

watch(
    search,
    () => {
        getData();
    },
    { deep: true }
);

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
    z-index: 1;
}
.relation-table-active > td {
    background-color: var(--lighter-grey);
}
.relation-table-disabled > td {
    opacity: 0.45;
    cursor: not-allowed;
}
</style>
