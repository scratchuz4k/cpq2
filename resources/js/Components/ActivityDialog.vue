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
    <Dialog v-model:visible="visible" modal :style="{ width: '60rem' }" position="top" :draggable="false">
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
            <table class="table w-100">
                <thead>
                    <tr style="position: sticky; top: 0">
                        <th style="width: 40%">Field</th>
                        <th style="width: 30%">From</th>
                        <th>To</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="activity in activityLog">
                        <tr @click="toggle(activity)" style="cursor: pointer">
                            <td colspan="2">
                                <p>
                                    <!-- <button class="btn p-0">
                                        <font-awesome-icon v-if="!activity.show_data"
                                            :icon="['fas', 'plus']"></font-awesome-icon>
                                        <font-awesome-icon v-else :icon="['fas', 'minus']"></font-awesome-icon>
                                    </button> -->

                                    <span class="me-3">{{ activity.user.name }}</span>
                                    <span class="smaller badge bg-secondary text-white">
                                        {{ activity.activitable_type }}
                                    </span>
                                </p>
                            </td>
                            <td>
                                <p class="text-end">
                                    {{ format.date(activity.created_at) }}
                                    <span
                                        class="badge text-white ms-2"
                                        :class="[activity.operation == 'created' ? 'bg-success' : 'bg-primary']"
                                        >{{ activity.operation }}</span
                                    >
                                </p>
                            </td>
                        </tr>
                        <tr v-for="item in activity.data" v-if="activity.show_data" class="field-info">
                            <td>
                                <p class="small">{{ item.field }}</p>
                            </td>
                            <td>
                                <p class="small">{{ item.from ?? 'null' }}</p>
                            </td>
                            <td>
                                <p class="small">{{ item.to ?? 'null' }}</p>
                            </td>
                        </tr>
                    </template>
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
        </div>
    </Dialog>
</template>
<script setup>
import { ref, onMounted, watch, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import queryString from 'query-string';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import Paginator from 'primevue/paginator';
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

const toggle = (activity) => {
    activity.show_data = !activity.show_data;
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
<style scoped>
.field-info > td {
    background-color: whitesmoke;
}
</style>
