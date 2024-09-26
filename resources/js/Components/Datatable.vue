<script setup>
import _ from "lodash";
import swal from "sweetalert2";
import { Link } from '@inertiajs/vue3';

import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Paginator from 'primevue/paginator';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

import { reactive, ref, watch } from 'vue';
import queryString from 'query-string';
import axios from 'axios';

const props = defineProps({
    route: String,
    columns: Array,
    allow: {
        type: Object,
        default: {
            create: false,
            update: false,
            delete: false,
            link: false,
        }
    }
})

const activeFilters = ref(false)
const activeCreate = ref(false)
const data = ref([])

const global = reactive({
    column: props.columns.map(x => x.data).join(","),
    operator: 'search',
    value: null
})

const form = reactive({
    filters: [],
    order: "desc",
    orderBy: "id",
    limit: 10,
})

const meta = reactive({
    page: 1,
    total: 0,
    first: 1
})

const setFilters = () => {
    props.columns.forEach((el) => form.filters.push({ column: el.data, value: null, operator: 'contains' }))
}

const getData = _.debounce(async () => {
    meta.total = 0
    let string = queryString.stringify({
        ...form,
        page: meta.page,
        filters: JSON.stringify([...form.filters, global]),
    })

    let res = await axios.get('/api' + props.route + '?' + string)
    data.value = res.data.data
    meta.total = res.data.meta.total
    meta.first = (meta.page - 1) * form.limit
}, 500)

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
        icon: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No, cancel.',
        confirmButtonColor: '#2d72d9',
        cancelButtonColor: '#E53A40',
        confirmButtonText: 'Yes, delete!'
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

setFilters()

watch(form, () => {
    meta.page = 1
    getData()
})

watch(global, () => {
    meta.page = 1
    getData()
})
getData()

</script>
<template>
    <DataTable :value="data" :rows="form.limit" stripedRows showGridlines>
        <template #header>
            <div class="d-flex justify-content-between">
                <div>
                    <Button v-if="allow.create" :severity="activeCreate ? 'primary' : 'secondary'" :class="['me-2']"
                        type="button" @click="() => activeCreate = !activeCreate">
                        <font-awesome-icon :icon="['fas', 'add']" />
                    </Button>
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
                    <InputText v-model="global.value" placeholder="Keyword Search" />
                </IconField>
            </div>
        </template>
        <Column v-for="(column, key) in columns" :field="column.data">
            <template #header>
                <div class="d-flex justify-content-between align-items-center w-100">
                    <b v-if="column.data.split('.').length > 1" class="column-title">
                        {{ column.title }}
                    </b>
                    <b v-else class="column-title sortable" @click="sortBy(column.data)">
                        {{ column.title }}
                        <font-awesome-icon :icon="['fas', 'sort']" />
                    </b>
                    <div v-if="activeFilters" style="position: relative;z-index: 1000">
                        <button class="btn btn-sm btn-filter">
                            <font-awesome-icon :icon="['fas', 'filter']" />
                        </button>
                        <div class="filter-content" :class="{ 'last': key + 1 == columns.length }">
                            <div class="mb-3">
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
                            </div>
                            <div class="mb-3">
                                <label class="form-label" :for="`filter-operator-${key}`">Value</label>
                                <input class="form-control me-2 form-control-sm" v-model="form.filters[key].value" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Column>
        <Column v-if="allow.update || allow.link || allow.delete">
            <template #body="{ data, index }">
                <div class="d-flex text-right">
                    <template v-if="allow.update">
                        <Link :href="props.route + '/' + data.id + '/edit'" class="btn p-0 me-2">
                        <font-awesome-icon :icon="['fas', 'edit']" />
                        </Link>
                    </template>

                    <template v-if="allow.link">
                        <Link :href="props.route + '/' + data.id" class="btn p-0 me-2">
                        <font-awesome-icon :icon="['fas', 'eye']" />
                        </Link>
                    </template>

                    <template v-if="allow.delete">
                        <button class="btn p-0" @click="deleteRecord(data.id)">
                            <font-awesome-icon icon="fas fa-trash-alt"></font-awesome-icon>
                        </button>
                    </template>
                </div>
            </template>
        </Column>
    </DataTable>
    <Paginator  v-model:first="meta.first" :rows="form.limit" :totalRecords="meta.total"
        :rowsPerPageOptions="[10, 20, 30]" @page="handlePaginator">
    </Paginator>

    <Dialog v-model:visible="activeCreate" modal header="Add a new Record" :style="{ width: '50rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <div class="mb-3" v-for="column in columns.filter(x => x.options && x.options.create)">
            <label for="username" class="form-label">{{ column.title }}</label>
            <InputText id="username" class="form-control" autocomplete="off" />
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary me-2" @click="activeCreate = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="activeCreate = false">Save</button>
        </div>
    </Dialog>
</template>
<style>
.p-datatable {
    z-index: 1;
}

.p-datatable-table-container {
    overflow: visible !important;
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
    transition: all .1s ease-in-out .3s;
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
