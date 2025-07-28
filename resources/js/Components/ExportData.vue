<script setup>
import Select from 'primevue/select';
import axios from 'axios';
import PickList from 'primevue/picklist';
import { reactive, ref, watch } from 'vue';
import Button from 'primevue/button';
import queryString from 'query-string';
import TooltipWithIcon from '@/Components/TooltipWithIcon.vue';
import { DateTime } from 'luxon';

const emit = defineEmits(['close-dialog']);
const props = defineProps({
    export: String,
    dateRange: null
});

const columns = ref([[], []]);

const export_data = reactive({
    export: null,
    columns: [],
    dateRange: null
});

const getColumns = async () => {
    try {
        const res = await axios.get('/api/export/columns?export=' + props.export);
        columns.value = [res.data.columns, []];
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    }
};

const exportData = async () => {
    try {
        let query = queryString.stringify({
            columns: export_data.columns.map((x) => x.id).join(','),
            date_interval: JSON.stringify(props.dateRange),
            export: props.export
        });
        let res = await axios
            .get('/api/export?' + query, {
                responseType: 'blob'
            })
            .then((res) => {
                if (res.status === 204) {
                    notyf.open({
                        type: 'warning',
                        message: "There's no data to export for these filters. Please redefine your filters."
                    });
                    return;
                }

                const url = window.URL.createObjectURL(new Blob([res.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', res.headers['content-disposition'].split('filename=')[1]);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        emit('close-dialog');
        return true;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    }
};

getColumns();

watch(columns, () => {
    export_data.columns = columns.value[1];
});
</script>

<template>
    <div id="export-data-container">
        <div class="mb-3" v-if="columns[0]?.length || columns[1]?.length">
            <label class="form-label">
                Columns to extract:
                <tooltip-with-icon :info_text="'If no column is selected, all will be exported'" />
            </label>
            <PickList v-model="columns" optionLabel="title" :showSourceControls="false" :showTargetControls="false">
                <template #option="{ option }">
                    {{ option.title }}
                </template>
            </PickList>
            <div class="mb-3">
                <button
                    id="submit-export-data"
                    type="submit"
                    class="btn btn-primary mt-3 float-end"
                    @click="exportData"
                >
                    <font-awesome-icon class="me-2" :icon="['fas', 'save']" />
                    Export
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
