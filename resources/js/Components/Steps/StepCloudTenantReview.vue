<script setup>
import { computed } from 'vue';
const props = defineProps({
    solutionType: Array,
    modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'nextStep']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const nextStep = () => {
    emit('nextStep');
};
</script>

<template>
    <div class="row justify-content-between mb-3">
        <div class="col-lg-8">
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <h3>Current Cost</h3>
                <h3>£3,692.00</h3>
            </div>
            <hr class="m-0" />
            <table id="table-simple " class="table table-dealmaker table-bordered table-hover mt-3">
                <!-- Headers -->
                <thead>
                    <tr>
                        <th style="width: 60%">Products</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Microsodt 365 Business Premium</td>
                        <td>x27</td>
                        <td>£2370.00</td>
                    </tr>
                    <tr>
                        <td>Microsodt Vision Plan 2</td>
                        <td>x12</td>
                        <td>£1322.00</td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <h3>Recommendations</h3>
            </div>
            <hr class="m-0" />

            <InfoBox severity="info" class="mt-3">
                <small>Optional Savings require a Professional Services call to assess the tenant in more detail</small>
            </InfoBox>
        </div>
        <div class="col-lg-3">teste</div>
    </div>
    <ButtonStep v-model="proxyValue" :disabled="proxyValue.account == null"></ButtonStep>
</template>
