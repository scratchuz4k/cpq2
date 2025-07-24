<script setup>
import Chart from 'primevue/chart';
import { onMounted, ref, watch } from 'vue';
import CountUp from 'vue-countup-v3';
import Dialog from 'primevue/dialog';
import ExportData from '@/Components/ExportData.vue';

const props = defineProps({
    title: String,
    showButton: Boolean,
    buttonTitle: String,
    startValue: {
        type: Number,
        default: 0
    },
    endValue: {
        type: [Number, String, null],
        default: 6543
    },
    duration: {
        type: Number,
        default: 3
    },
    icon: Array,
    data: Object,
    dateRange: Object,
    export: String,
    label: String
});

const providersColors = [
    {
        borderColor: `rgba(163, 71, 150, 1)`,
        backgroundColor: `rgba(163, 71, 150, 0.1)`
    },
    {
        borderColor: `rgba(163, 71, 150, 0.2)`,
        backgroundColor: `rgba(163, 71, 150, 0.02)`
    },
    {
        borderColor: `rgba(163, 71, 150, 0.4)`,
        backgroundColor: `rgba(163, 71, 150, 0.03)`
    },
    {
        borderColor: `rgba(163, 71, 150, 0.6)`,
        backgroundColor: `rgba(163, 71, 150, 0.04)`
    },
    {
        borderColor: `rgba(163, 71, 150, 0.8)`,
        backgroundColor: `rgba(163, 71, 150, 0.05)`
    },
    {
        borderColor: `rgba(163, 71, 150, 0.9)`,
        backgroundColor: `rgba(163, 71, 150, 0.06)`
    }
];

let showModal = ref(false);
const chartData = ref();
const chartOptions = ref();

const setChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    return {
        labels: props.data?.labels,
        datasets: Array.isArray(props.data?.data)
            ? [
                  {
                      data: props.data?.data,
                      fill: true,
                      borderColor: documentStyle.getPropertyValue('--color-purple'),
                      tension: 0.4
                  }
              ]
            : Object.entries(props.data.data).map(([key, value], index) => ({
                  label: `${value.label} (${value.total})`,
                  data: value.data,
                  fill: true,
                  tension: 0.4,
                  borderColor: providersColors[index]?.borderColor,
                  backgroundColor: providersColors[index]?.backgroundColor
              }))
    };
};
const setChartOptions = () => {
    return {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: !Array.isArray(props.data?.data)
            }
        },
        scales: {
            y: {
                ticks: {
                    stepSize: 1,
                    callback: function (value) {
                        if (Number.isInteger(value)) {
                            return value;
                        }
                    }
                }
            }
        }
    };
};

onMounted(() => {
    if (props.data) {
        chartData.value = setChartData();
        chartOptions.value = setChartOptions();
    }
});

watch(
    () => props.data,
    (newVal, oldVal) => {
        if (newVal) {
            chartData.value = setChartData();
            chartOptions.value = setChartOptions();
        }
    }
);
</script>

<template>
    <section class="row mb-4 bg-light p-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <h4 class="mb-0">
                    <font-awesome-icon :icon="icon" class="me-2 text-primary"></font-awesome-icon>
                    {{ title }}
                </h4>
                <button v-if="showButton" class="export-btn btn btn-sm btn-outline-primary" @click="showModal = true">
                    {{ buttonTitle }}
                </button>
            </div>
            <div class="row align-items-center mt-3">
                <div class="col-md-3 text-center">
                    <div v-if="Array.isArray(props.data?.data)" class="totals">
                        <div class="partial mb-5">
                            <count-up
                                v-if="!isNaN(endValue)"
                                :duration="duration"
                                :endVal="props.data?.partial ?? 0"
                                :startVal="startValue"
                            />
                        </div>
                        <div
                            v-if="props.data?.partial !== props.data?.total"
                            class="total d-flex justify-content-center"
                        >
                            <span v-if="props.label" class="px-1">( </span>
                            <count-up
                                v-if="!isNaN(endValue)"
                                :duration="duration"
                                :endVal="props.data?.total ?? 0"
                                :startVal="startValue"
                            />
                            <span v-if="props.label" class="px-1"> {{ props.label }} )</span>
                        </div>
                    </div>
                    <div v-else>
                        <div class="d-flex justify-content-between fw-bold px-4 fs-4" v-for="supp in props.data?.data">
                            <span class="text-muted fw-normal fst-italic fs-5">{{ supp.label }}</span>
                            <count-up :duration="duration" :endVal="supp.total ?? 0" :startVal="startValue" />
                        </div>
                    </div>
                </div>
                <div class="col-md-9" style="height: 250px">
                    <Chart :data="chartData" :options="chartOptions" type="line" />
                </div>
            </div>
        </div>
    </section>
    <Dialog
        v-model:visible="showModal"
        modal
        :style="{ marginTop: '4rem', minWidth: '60vw' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
        position="top"
    >
        <template #header>
            <h2>Export Report</h2>
        </template>
        <div class="container m-0 p-0">
            <export-data :export="props.export" :dateRange="dateRange" @close-dialog="showModal = false" />
        </div>
    </Dialog>
</template>

<style scoped>
.p-chart {
    height: 100% !important;
    width: 100% !important;
}
.partial {
    font-size: 3.5rem;
    font-weight: bold;
}
.total {
    font-size: 1rem;
    font-weight: bold;
}
</style>
