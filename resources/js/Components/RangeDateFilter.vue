<script setup>
import { ref } from 'vue';
import DatePicker from 'primevue/datepicker';
import { DateTime } from 'luxon';

const emit = defineEmits(['dateRangeChange']);
const dateNow = DateTime.now();
const minDate = DateTime.fromISO('2024-01-01');

const rangeDate = ref({
    start: null,
    end: null
});

const filterData = () => {
    if (rangeDate.value.start && rangeDate.value.end && rangeDate.value.start > rangeDate.value.end) {
        const temp = rangeDate.value.start;
        rangeDate.value.start = rangeDate.value.end;
        rangeDate.value.end = temp;
    }
    emit('dateRangeChange', { ...rangeDate.value });
};

const resetFilters = () => {
    rangeDate.value.start = null;
    rangeDate.value.end = null;
    emit('dateRangeChange', { ...rangeDate.value });
};
</script>

<template>
    <div class="row mt-3">
        <div class="col mb-3">
            <div class="row">
                <div class="col-2 p-2 m-0">Date Filter:</div>
                <div class="col-4">
                    <DatePicker
                        :inputId="'start_date'"
                        class="w-100"
                        v-model="rangeDate.start"
                        placeholder="Start Date"
                        showIcon
                        iconDisplay="input"
                        dateFormat="dd/mm/yy"
                        :minDate="minDate.toJSDate()"
                        :maxDate="dateNow.toJSDate()"
                    />
                </div>

                <div class="col-4">
                    <DatePicker
                        :inputId="'end_date'"
                        class="w-100"
                        v-model="rangeDate.end"
                        placeholder="End Date"
                        showIcon
                        iconDisplay="input"
                        dateFormat="dd/mm/yy"
                        :maxDate="dateNow.toJSDate()"
                    />
                </div>
                <div class="col-2 d-flex gap-2">
                    <button id="filter-data-btn" class="btn btn-primary w-100" @click="filterData">Filter</button>
                    <button id="reset-data-btn" class="btn btn-outline-primary w-100" @click="resetFilters">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
