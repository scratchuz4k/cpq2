<template>
    <button
        :hidden="!(tenant.configurations.log_system_is_active === '1')"
        id="btn-activity-open"
        class="btn btn-outline-primary me-2"
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
                <p>This dialog contains the last activities for the selected entities</p>
            </div>
        </template>
        <div style="position: relative">
            <div class="d-flex mb-3">
                <div class="me-2">
                    <label class="form-label">Entities:</label>
                    <MultiSelect v-model="entities" :options="options" class="form-select p-0" :maxSelectedLabels="1">
                    </MultiSelect>
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
                    <small class="text-surface-500 dark:text-surface-400">{{
                        format.date(slotProps.item.created_at)
                    }}</small>
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
                                {{ slotProps.item.activitable_type }} #{{ slotProps.item.activitable_id }}
                            </span>
                            {{ slotProps.item.operation }} by <b>{{ slotProps.item.user.name }}</b>
                        </p>
                        <DefaultActivity
                            v-if="!customActivities.includes(slotProps.item.activitable_type)"
                            :item="slotProps.item"
                        ></DefaultActivity>
                        <component
                            :is="slotProps.item.activitable_type + 'Activity'"
                            :item="slotProps.item"
                        ></component>
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
<script setup>
import Button from 'primevue/button';
import { can, hasRole } from '@/Composables/useAuth.js';
import { ref, onMounted, watch, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import queryString from 'query-string';
import { exportData } from '@/Composables/export.js';
import { Dialog, Timeline, MultiSelect, Paginator, Card } from 'primevue';
const props = defineProps({
    model: String,
    filters: Array
});

const page = usePage();
const tenant = page.props.tenant;
const options = props.filters.map((x) => x.entity);
const entities = ref(props.filters.map((x) => x.entity));
const customActivities = ['QuoteItem', 'QuoteDiscount'];
const has_export_permission = can(`export activity`) || hasRole('Super Admin');
const meta = reactive({
    page: 1,
    total: 0,
    first: 1,
    limit: 20,
    isLoading: false
});

const getFilters = () => {
    let temp = [];
    props.filters.forEach((e, i) => {
        if (!entities.value.includes(e.entity)) {
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
    });
    return temp;
};

const emit = defineEmits(['update:modelValue']);
const visible = ref(false);
const activityLog = ref([]);
const getActivity = async () => {
    let string = queryString.stringify({
        page: meta.page,
        filters: JSON.stringify(getFilters()),
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
watch(entities, () => {
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
    getActivity();
};
</script>
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
