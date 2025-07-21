<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import RangeDateFilter from '@/Components/RangeDateFilter.vue';
import ChartCard from '@/Components/ChartCard.vue';
import format from '@/Composables/format.js';
import queryString from 'query-string';

const props = defineProps({
    registered_users: Array,
    total_quotes: Array
});

const data = ref([]);

const dateRange = ref({});

const updateDateRange = (rangeDate) => {
    dateRange.value = format.parsedDateRange(rangeDate);
    getData();
};

const getData = async () => {
    let query = queryString.stringify(dateRange.value);

    try {
        let res = await axios.get('api/reporting-data?' + query);
        data.value = res.data;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};

const items = ref([{ label: 'Home', url: '/' }, { label: 'My Account', url: '/myaccount' }, { label: 'Reporting' }]);

getData();
</script>

<template>
    <Head title="Reporting" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Reporting" :breadcrumbs="items" />

            <range-date-filter @date-range-change="updateDateRange" />
            <chart-card
                :title="'Users Registered'"
                :showButton="true"
                :buttonTitle="'Export to XLS'"
                :icon="['far', 'user']"
                :data="data?.users"
                :dateRange="dateRange"
                :export="'Users'"
                :label="'Total Users'"
            />
            <chart-card
                :title="'Active Users'"
                :showButton="true"
                :buttonTitle="'Export to XLS'"
                :icon="['fas', 'users-viewfinder']"
                :data="data?.active_users"
                :dateRange="dateRange"
                :export="'ActiveUsers'"
                :label="'Total Logged in Users'"
            />
            <chart-card
                :title="'Quotes Created'"
                :showButton="true"
                :buttonTitle="'Export to XLS'"
                :icon="['fas', 'users-viewfinder']"
                :data="data?.quotes"
                :dateRange="dateRange"
                :export="'Quotes'"
                :label="'Total Quotes'"
            />
            <chart-card
                :title="'Suppliers Selected for Quotes'"
                :showButton="true"
                :buttonTitle="'Export to XLS'"
                :icon="['far', 'file-lines']"
                :data="data?.quotes_suppliers"
                :dateRange="dateRange"
                :export="'Suppliers'"
            />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
