<script setup>
import _ from "lodash";
import swal from "sweetalert2";
import { Link } from '@inertiajs/vue3';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Paginator from 'primevue/paginator';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

import { reactive, ref, watch, useSlots } from 'vue';
import queryString from 'query-string';
import axios from 'axios';

const slots = useSlots()
const usedSlots = Object.keys(slots)

const props = defineProps({
    route: String,
    columns: Array,
    filters: {
        type: Object,
        default: {}
    },
    allow: {
        type: Object,
        default: {
            update: false,
            delete: false,
            link: false,
        }
    }
})

const activeFilters = ref(false)
const data = ref([])

const global = ref(null)

const form = reactive({
    filters: [],
    order: "desc",
    orderBy: "id",
    limit: 20,
})

const meta = reactive({
    page: 1,
    total: 0,
    first: 1
})

const setFilters = () => {
    props.columns.forEach((el) => form.filters.push({ column: el.data, value: null, operator: 'contains' }))
}

const getData = async () => {
    meta.total = 0
    let string = queryString.stringify({
        ...form,
        page: meta.page,
        columns: props.columns.map(x => x.data).join(","),
        filters: JSON.stringify([...form.filters]),
        search: global.value
    })

    let res = await axios.get('/api' + props.route + '?' + string)
    data.value = res.data.data
    meta.total = res.data.meta.total
    meta.first = (meta.page - 1) * form.limit
}

const sortBy = (column) => {
    let changeSort = form.order == 'asc' ? 'desc' : 'asc'
    form.order = form.orderBy === column ? changeSort : form.order
    form.orderBy = column
}

const handlePaginator = (ev) => {
    form.limit = ev.rows
    meta.page = ev.page + 1
    getData()
}
const clearFilters = () => {
    form.filters.forEach(x => x.value = null)
}
const deleteRecord = async (id) => {
    swal.fire({
        title: 'Are you sure?',
        buttonsStyling: true,
        showCancelButton: true,
        confirmButtonColor: '#ec008c',
        width: '20em',
        customClass: {
            confirmButton: 'btn text-light btn-lg mr-2 fs-6',
            cancelButton: 'btn btn-outline-primary border-light-purple btn-lg fs-6',
            loader: 'custom-loader',
            title: 'h3'
        },
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            bulkOperation.value = ''
            return
        }
        try {
            let res = await axios.delete('/api' + props.route + '/' + id)
            getData()
            notyf.success('Removed with success');
            return
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        }

    })

}

const getValue = (item, key) => {
    let items = key.split(".")
    let result = item;

    for (let i = 0; i < items.length; i++) {
        const prop = items[i];

        // Check if the current property exists in the current object
        if (result && typeof result === 'object' && prop in result) {
            result = result[prop];
            // Access the nested object using the current property
            if (Array.isArray(result) && i < items.length - 1) {
                result = result.length > 0 ? result[0] : undefined;
            }
        } else {
            // Property not found, return undefined
            return undefined;
        }
    }

    return result;
}

setFilters()

watch(() => [
    form.order,
    form.orderBy,
    form.limit,
], function() {
    meta.page = 1
    getData()
})

watch([global, form.filters], _.debounce(function() {
    meta.page = 1
    getData()
}, 500))

getData()

</script>
<template>
    <DataTable :value="data" :rows="form.limit" stripedRows :reorderableColumns="false">
        <template #header>
            <div class="d-flex justify-content-between">
                <div>
                    <Button :severity="activeFilters ? 'primary' : 'secondary'" type="button" :class="['me-2']"
                        @click="() => activeFilters = !activeFilters">
                        <font-awesome-icon :icon="['fas', 'filter']" />
                    </Button>
                    <Button v-if="activeFilters" type="button"
                        :severity="form.filters.find(x => x.value) ? 'primary' : 'secondary'" @click="clearFilters()">
                        <font-awesome-icon :icon="['fas', 'eraser']" />
                    </Button>
                </div>
                <IconField>
                    <InputIcon>
                        <font-awesome-icon :icon="['fas', 'search']" />
                    </InputIcon>
                    <InputText v-model="global" type="search" placeholder="Keyword Search" />
                </IconField>
            </div>
        </template>
        <Column v-for="(column, key) in columns.filter(x => !x.hide)" :field="column.data">
            <template #header>
                <div class="d-flex justify-content-between align-items-center w-100 pe-3 position-relative">
                    <b v-if="column.data.split('.').length > 1" class="column-title">
                        {{ column.title }}
                    </b>
                    <b v-else class="column-title sortable d-inline-flex align-items-center" @click="sortBy(column.data)" :style="{textDecoration: column.data == form.orderBy ? 'underline': 'none'}">
                        {{ column.title }}
                        <span class="text-center" style="width: 20px;">
                            <template v-if="column.data == form.orderBy">
                                <font-awesome-icon v-if="form.order == 'desc'" :icon="['fas', 'sort-down']" />
                                <font-awesome-icon v-else :icon="['fas', 'sort-up']" />
                            </template>
                        </span>
                    </b>
                    <div v-if="activeFilters" style="position: absolute; right: 0; z-index: 10">
                        <button class="btn btn-sm btn-filter">
                            <font-awesome-icon :icon="['fas', 'filter']" />
                        </button>
                        <div class="filter-content" :class="{ 'last': key + 1 == columns.length }">
                            <!-- <div class="mb-3">
                                <label class="form-label" :for="`filter-operator-${key}`">Operator</label>
                                <select :id="`filter-operator-${key}`" class="form-select w-100"
                                    v-model="form.filters[key].operator">
                                    <option value="contains">Contains</option>
                                    <option value="not_contains">Not Contains</option>
                                    <option value="starts_with">Starts With</option>
                                    <option value="ends_with">Ends With</option>
                                    <option value="equals" v-html="'='"></option>
                                    <option value="not_equals" v-html="'!='"></option>
                                    <option value="greater_than" v-html="'>'"></option>
                                    <option value="less_than" v-html="'<'"></option>
                                    <option value="greater_than_or_equal_to" v-html="'>='"></option>
                                    <option value="less_than_or_equal_to" v-html="'<='"></option>
                                </select>
                            </div> -->
                            <template v-if="Object.keys(props.filters).includes(column.data)">
                                <div class="mb-3">
                                    <label class="form-label" :for="`filter-value-${key}`">Options</label>
                                    <select :id="`filter-value-${key}`" class="form-select w-100"
                                        v-model="form.filters[key].value">
                                        <option :value="null">All</option>
                                        <option v-for="option in filters[column.data].options" :value="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>
                            </template>
                            <div v-else class="mb-3">
                                <label class="form-label" :for="`filter-value-${key}`">Search</label>
                                <input :id="`filter-value-${key}`" type="search" class="form-control me-2 form-control-sm"
                                    v-model="form.filters[key].value" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template #body="{ data, field }">
                <slot v-if="usedSlots.includes(field)" :name="field" :value="getValue(data, field)" />
                <template v-else>{{ getValue(data, field) }}</template>
            </template>
        </Column>
        <Column v-if="allow.update || allow.link || allow.delete" :class="['p-0']">
            <template #body="{ data, index }">
                <div class="d-flex justify-content-center">
                    <template v-if="allow.update">
                        <Link :href="props.route + '/' + data.id + '/edit'" class="btn p-2 mx-1 border-0">
                        <font-awesome-icon :icon="['fas', 'edit']" />
                        </Link>
                    </template>

                    <template v-if="allow.link">
                        <Link :href="props.route + '/' + data.id" class="btn p-2 mx-1 border-0">
                        <font-awesome-icon :icon="['fas', 'eye']" />
                        </Link>
                    </template>

                    <template v-if="allow.delete">
                        <button class="btn p-2 mx-1 border-0" @click="deleteRecord(data.id)">
                            <font-awesome-icon icon="fas fa-trash-alt" />
                        </button>
                    </template>
                </div>
            </template>
        </Column>
    </DataTable>
    <Paginator v-model:first="meta.first" :rows="form.limit" :totalRecords="meta.total"
        :rowsPerPageOptions="[20, 30, 50]" @page="handlePaginator">
    </Paginator>
</template>
<style>
.p-select-option.p-select-option-selected.p-focus {
    background-color: #ec008c!important;
    color: white!important;
}

.p-datatable {
    z-index: 1;
}

.p-datatable-header {
    padding: 0.75rem 0rem 0rem !important;
    border-bottom: 0 !important;
}

.p-datatable-table-container {
    overflow: visible !important;
}

.p-datatable tr td:not(.p-0),
.p-datatable tr th {
    padding: 0.5rem .5rem !important;;
}

.column-title {
    padding: 0.5rem 0.5rem 0.5rem 0;
}

.column-title {
    margin: 0;
    width: 100%;
    cursor: pointer;
}

.sortable:hover {
    text-decoration: underline;
}

.btn-filter:hover~.filter-content {
    max-height: 500px;
    padding: 10px;
    border: 1px solid #e2e8f0;
}

.filter-content {
    max-height: 0;
    overflow: hidden;
    position: absolute;
    top: 100%;
    left: 0;
    width: 250px;
    background-color: white;
    border-radius: 5px;
    box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 12px;
    padding: 0 10px;
    z-index: 9999
}

.filter-content.last {
    left: auto;
    right: 0;
}

.filter-content:hover {
    max-height: 500px;
    padding: 10px;
    border: 1px solid #e2e8f0;
}
</style>
