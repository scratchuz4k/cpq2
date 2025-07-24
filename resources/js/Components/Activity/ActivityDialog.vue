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
            <div class="d-flex">
                <div class="mb-3">
                    <label class="form-label">Entities:</label>
                    <MultiSelect v-model="entities" :options="options" class="form-select p-0" :maxSelectedLabels="1">
                    </MultiSelect>
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
                            <b>{{ slotProps.item.user.name }}</b> {{ slotProps.item.operation }}
                            {{ slotProps.item.activitable_type }}
                            {{ slotProps.item.activitable_id }}
                        </p>
                        <p class="small">{{ slotProps.item.activity?.name }}</p>
                        <table v-if="slotProps.item.readmore" class="table table-activity mt-3">
                            <thead>
                                <tr>
                                    <th style="width: 30%">Field</th>
                                    <th style="width: 35%">From</th>
                                    <th style="width: 35%">To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in slotProps.item.data" class="field-info">
                                    <td class="">
                                        <p class="small">{{ item.field }}</p>
                                    </td>
                                    <td>
                                        <p class="small">{{ item.from ?? 'null' }}</p>
                                    </td>
                                    <td>
                                        <p class="small">{{ item.to ?? 'null' }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <a
                            class="text-secondary"
                            v-if="slotProps.item.data?.length"
                            href="#"
                            @click="() => (slotProps.item.readmore = !slotProps.item.readmore)"
                        >
                            Show {{ !slotProps.item.readmore ? 'more' : 'less' }}...
                        </a>
                        <a v-else class="text-secondary m-0 p-0">Nothing to show</a>
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
import { ref, onMounted, watch, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import queryString from 'query-string';
import { Dialog, Timeline, MultiSelect, Paginator, Card } from 'primevue';
const props = defineProps({
    model: String,
    filters: Array
});

const page = usePage();
const tenant = page.props.tenant;
const options = props.filters.map((x) => x.entity);
const entities = ref(props.filters.map((x) => x.entity));
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
