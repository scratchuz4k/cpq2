<script setup>
import TreeTableItem from '@/Components/TreeTableItem.vue';
import queryString from 'query-string';
import axios from 'axios';
import { reactive, ref, useSlots, watch } from 'vue';
import Paginator from 'primevue/paginator';
import Dialog from 'primevue/dialog';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import _ from 'lodash';
import Button from 'primevue/button';
import { can, hasRole } from '@/Composables/useAuth.js';
import pluralize from 'pluralize';
import { exportData } from '@/Composables/export.js';

const slots = useSlots();
const usedSlots = Object.keys(slots);

const props = defineProps({
    id: String,
    route: String,
    columns: Array,
    modelValue: Array,
    allow: {
        type: Object,
        default: {
            update: true,
            delete: true,
            link: false,
            commission: false
        }
    }
});

const meta = reactive({
    page: 1,
    total: 0,
    first: 1,
    limit: 20,
    order: 'asc',
    orderBy: 'id'
});

let data = ref([]);
let selectedItem = ref(null);
let showModal = ref(false);
const global = ref(null);
const emptyData = ref(true);
const has_export_permission =
    can(`export ${pluralize.singular(props.route.replace(/^\//, ''))}`) || hasRole('Super Admin');

const getData = async () => {
    let filters = !global.value
        ? [
              {
                  column: 'parent_id',
                  operator: 'is_null',
                  value: 'null'
              }
          ]
        : [];

    let string = queryString.stringify({
        ...meta,
        filters: JSON.stringify(filters),
        search: global.value
    });

    let res = await axios.get('/api' + props.route + '?' + string);
    data.value = res.data.data;
    emptyData.value = !data.value.length;
    meta.total = res.data.meta?.total ?? res.data.total;
    meta.first = (meta.page - 1) * meta.limit;
};
getData();

emitter.on('change-selected', (item) => {
    selectedItem.value = item;
    showModal.value = !showModal.value;
});

emitter.on('treetable-refresh', () => {
    getData();
});

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    getData();
};

const sortBy = (column) => {
    let changeSort = meta.order == 'asc' ? 'desc' : 'asc';
    meta.order = meta.orderBy === column ? changeSort : meta.order;
    meta.orderBy = column;
};

const exportDisabled = ref(false);

const getExportData = async () => {
    if (exportDisabled.value) return;
    exportDisabled.value = true;
    let filters = !global.value
        ? [
              {
                  column: 'parent_id',
                  operator: 'is_null',
                  value: 'null'
              }
          ]
        : [];

    let string = queryString.stringify({
        ...meta,
        filters: JSON.stringify(filters),
        search: global.value
    });

    exportData(string, props.route);

    setTimeout(() => {
        exportDisabled.value = false;
    }, 5000);
};

watch(
    () => [meta.order, meta.orderBy],
    function () {
        emitter.emit('reset-items');
        getData();
    }
);
watch(
    () => [global.value],
    _.debounce(function () {
        emitter.emit('reset-items');
        getData();
    }, 500)
);
</script>

<template>
    <div>
        <div class="d-flex justify-content-between">
            <Button
                v-if="has_export_permission"
                type="button"
                :severity="'secondary'"
                :class="['me-2']"
                @click="getExportData()"
                :disabled="emptyData || exportDisabled"
            >
                <font-awesome-icon :icon="['fas', 'file-export']" />
            </Button>
            <IconField>
                <InputIcon>
                    <font-awesome-icon :icon="['fas', 'search']" />
                </InputIcon>
                <InputText id="treetable-search" v-model="global" placeholder="Keyword Search" type="search" />
            </IconField>
        </div>
        <table :id="props.id" class="table w-100">
            <thead>
                <tr>
                    <th style="width: 5%"></th>
                    <th v-for="(column, key) in columns" :key="key">
                        <p
                            @click="sortBy(column.data)"
                            :style="{
                                cursor: 'pointer',
                                textDecoration: column.data == meta.orderBy ? 'underline' : 'none'
                            }"
                        >
                            {{ column.title }}
                            <span class="text-center" style="width: 20px">
                                <template v-if="column.data == meta.orderBy">
                                    <font-awesome-icon v-if="meta.order == 'desc'" :icon="['fas', 'sort-down']" />
                                    <font-awesome-icon v-else :icon="['fas', 'sort-up']" />
                                </template>
                            </span>
                        </p>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <TreeTableItem
                    v-for="(item, key) in data"
                    :key="item.id"
                    :item="item"
                    :index="key"
                    :route="route"
                    :allow="allow"
                    :columns="columns"
                    :usedSlots="usedSlots"
                    v-model="props.modelValue"
                >
                    <template v-for="column in columns" #[column.data]="{ value }">
                        <slot v-if="usedSlots.includes(column.data)" :name="column.data" :value="value"></slot>
                        <template v-else>{{ value }}</template>
                    </template>
                </TreeTableItem>
                <tr v-if="!data.length">
                    <td :colspan="columns.length + 2">
                        <div class="text-center my-1">No records found</div>
                    </td>
                </tr>
            </tbody>
        </table>
        <Paginator
            v-model:first="meta.first"
            :rows="meta.limit"
            :totalRecords="meta.total"
            :rowsPerPageOptions="[20, 30, 50]"
            @page="handlePaginator"
        >
        </Paginator>
        <Dialog
            v-model:visible="showModal"
            modal
            :style="{ marginTop: '4rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            :draggable="false"
            position="top"
        >
            <template #header> </template>
            <slot name="dialog_content" :data="selectedItem" :toggleModal="() => (showModal = !showModal)" />
        </Dialog>
    </div>
</template>

<style scoped></style>
