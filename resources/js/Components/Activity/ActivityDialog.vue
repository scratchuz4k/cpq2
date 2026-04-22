<script setup>
import Button from 'primevue/button';
import { can, hasRole } from '@/Composables/useAuth.js';
import { ref, onMounted, watch, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import queryString from 'qs';
import { exportData } from '@/Composables/export.js';
import { Dialog, Timeline, MultiSelect, Select, Paginator, Card } from 'primevue';
const props = defineProps({
    model: String,
    filters: Array
});

const page = usePage();
const tenant = page.props.tenant;

const entityLabels = {
    Quote: 'Quote Properties',
    QuoteItem: 'Line Item',
    QuoteDiscount: 'Discount'
};

const options = props.filters.map((x) => ({
    label: entityLabels[x.entity] || x.entity,
    value: x.entity
}));
const entities = ref([]);
const has_export_permission = can(`export activity`) || hasRole('Super Admin');
const has_viewAny_permission = can(`viewAny activity`) || hasRole('Super Admin');
const meta = reactive({
    page: 1,
    total: 0,
    first: 1,
    limit: 20,
    isLoading: false
});

const today = new Date();

// Last day (24 hours ago)
const lastDay = new Date(today);
lastDay.setDate(today.getDate() - 1);

// Last week (7 days ago)
const lastWeek = new Date(today);
lastWeek.setDate(today.getDate() - 7);

// Last month (same day previous month)
const lastMonth = new Date(today);
lastMonth.setMonth(today.getMonth() - 1);

const dateFilter = [
    {
        label: 'All',
        date: null
    },
    {
        label: 'Last 24 hours',
        date: lastDay.toISOString()
    },
    {
        label: 'Last Week',
        date: lastWeek.toISOString()
    },
    {
        label: 'Last Month',
        date: lastMonth.toISOString()
    }
];
const timeStamp = ref(null);

const getFilters = () => {
    const selectedEntities = entities.value.length === 0 ? props.filters.map((e) => e.entity) : entities.value;

    let temp = [];
    props.filters.forEach((e, i) => {
        if (!selectedEntities.includes(e.entity)) {
            return;
        }
        let item = {
            column: 'activitable_type',
            operator: 'equals',
            value: 'App\\Models\\' + e.entity,
            function: 'orWhere'
        };
        item.function = i == 0 ? 'where' : 'orWhere';
        temp.push(item);
        temp.push({
            column: 'activitable_id',
            operator: 'in',
            value: e.values
        });
        if (timeStamp.value) {
            let time = {
                column: 'created_at',
                operator: 'greater_than_or_equal_to',
                value: timeStamp.value
            };
            temp.push(time);
        }
    });

    return temp;
};

const emit = defineEmits(['update:modelValue']);
const visible = ref(false);
const activityLog = ref([]);
const getActivity = async () => {
    if (!(tenant.configurations.log_system_is_active === '1') || !has_viewAny_permission) {
        return;
    }
    let string = queryString.stringify({
        page: meta.page,
        filters: getFilters(),
        order: 'desc',
        orderBy: 'created_at',
        limit: meta.limit
    });
    const res = await axios.get('/api/activity' + '?' + string);
    activityLog.value = res.data.data;
    meta.total = res.data.meta.total;
    meta.first = (meta.page - 1) * meta.limit;
};

onMounted(() => {
    getActivity();
});

watch(visible, () => {
    if (visible) {
        getActivity();
    }
});
watch(timeStamp, () => {
    getActivity();
});
watch(entities, () => {
    meta.page = 1;
    getActivity();
});

const exportDisabled = ref(false);
const getExportData = async () => {
    if (exportDisabled.value) return;
    exportDisabled.value = true;

    let filters = {
        filters: JSON.stringify(getFilters())
    };

    exportData(filters, '/activities');

    setTimeout(() => {
        exportDisabled.value = false;
    }, 5000);
};

const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    const content = document.querySelector('.p-dialog-content');
    if (content) {
        content.scrollTo({ top: 0, behavior: 'smooth' });
    }
    getActivity();
};

function getEntityLabel(type) {
    return entityLabels[type] || type;
}
</script>
<template>
    <button
        :hidden="!(tenant.configurations.log_system_is_active === '1') || !has_viewAny_permission"
        id="btn-activity-open"
        class="btn btn-outline-primary ms-2"
        @click="() => (visible = !visible)"
    >
        <font-awesome-icon :icon="['fas', 'clock-rotate-left']"></font-awesome-icon>
        Activity
    </button>
    <Dialog
        v-model:visible="visible"
        modal
        :style="{ width: '60rem' }"
        position="top"
        :draggable="false"
        :maximizable="true"
    >
        <template #header>
            <div>
                <h2>Activity</h2>
                <p>This dialog contains the last activities for the selected quote</p>
            </div>
        </template>
        <div style="position: relative">
            <div class="d-flex justify-content-between mb-3">
                <div class="d-flex">
                    <div class="me-2">
                        <label class="form-label">Activity Type:</label>
                        <MultiSelect
                            v-model="entities"
                            :options="options"
                            optionLabel="label"
                            optionValue="value"
                            size="small"
                            class="custom-multiselect w-100 p-0"
                            placeholder="All"
                        >
                            <template #value="{ value }">
                                <span v-if="!value || value.length === 0 || value.length === options.length">
                                    All
                                </span>
                            </template>
                        </MultiSelect>
                    </div>
                    <div class="me-2">
                        <label class="form-label">Filter:</label>
                        <Select
                            v-model="timeStamp"
                            :options="dateFilter"
                            optionLabel="label"
                            optionValue="date"
                            placeholder="All"
                            size="small"
                            class="w-100"
                        >
                        </Select>
                    </div>
                </div>

                <div class="d-flex justify-content-end align-items-end" v-if="has_export_permission">
                    <div>
                        <Button
                            :disabled="!activityLog.length || exportDisabled"
                            :severity="'secondary'"
                            :class="['me-2']"
                            @click="getExportData()"
                            type="button"
                        >
                            <font-awesome-icon :icon="['fas', 'file-export']" />
                        </Button>
                    </div>
                </div>
            </div>

            <Timeline :value="activityLog">
                <template #opposite="slotProps">
                    <small
                        class="text-surface-500 dark:text-surface-400"
                        v-tooltip
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        :data-bs-original-title="format.formattedDate(slotProps.item.created_at)"
                        >{{ format.date(slotProps.item.created_at) }}</small
                    >
                </template>
                <template #marker="slotProps">
                    <span
                        class="p-timeline-event-marker text-white"
                        :class="[
                            slotProps.item.operation == 'deleted'
                                ? 'bg-danger'
                                : slotProps.item.operation == 'created'
                                  ? 'bg-success'
                                  : 'bg-primary'
                        ]"
                    >
                    </span>
                </template>
                <template #content="slotProps">
                    <div class="mb-3">
                        <p>
                            <span class="smaller badge bg-secondary text-white">
                                {{ getEntityLabel(slotProps.item.activitable_type) }}
                            </span>
                            {{ slotProps.item.activitable?.name }}
                            {{ slotProps.item.operation }} by
                            <b>{{ slotProps.item.user.name }}</b>
                        </p>
                        <ActivityDetails :item="slotProps.item"></ActivityDetails>
                    </div>
                </template>
            </Timeline>
            <Paginator
                v-model:first="meta.first"
                :rows="meta.limit"
                :totalRecords="meta.total"
                :rowsPerPageOptions="[20, 30, 50]"
                @page="handlePaginator"
            >
            </Paginator>
        </div>
    </Dialog>
</template>
<style scope>
.field-info > td {
    background-color: whitesmoke;
}
.table-activity thead tr th,
.table-activity tbody tr td {
    padding: 0;
    font-size: 12px;
}
</style>
