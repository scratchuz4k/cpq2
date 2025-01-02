<template>
    <Dialog v-model:visible="proxyValue" modal :style="{ width: '60rem' }" position="top" :draggable="false">
        <template #header>
            <div>
                <h2>Activity</h2>
                <p>This dialog contains the last activities for the selected entities</p>
            </div>
        </template>
        <div style="position: relative;">
            <table class="table w-100">
                <thead>
                    <tr style="position:sticky; top: 0;">
                        <th style="width: 40%;">Field</th>
                        <th style="width: 30%;">From</th>
                        <th>To</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="activity in activityLog">
                        <tr @click="toggle(activity)" style="cursor: pointer;">
                            <td colspan="2">
                                <p>
                                    <!-- <button class="btn p-0">
                                        <font-awesome-icon v-if="!activity.show_data"
                                            :icon="['fas', 'plus']"></font-awesome-icon>
                                        <font-awesome-icon v-else :icon="['fas', 'minus']"></font-awesome-icon>
                                    </button> -->

                                    <span class="me-3">{{ activity.user.name }}</span>
                                    <span class="smaller badge bg-secondary text-white ">
                                        {{ activity.activitable_type }}
                                    </span>
                                </p>
                            </td>
                            <td>
                                <p class="text-end">
                                    {{ format.date(activity.created_at) }}
                                    <span class="badge text-white ms-2"
                                        :class="[activity.operation == 'created' ? 'bg-success' : 'bg-primary']">{{
                                            activity.operation }}</span>
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
        </div>
    </Dialog>
</template>
<script setup>
import { ref, onMounted, computed, watch } from "vue"
import queryString from 'query-string';
import Dialog from "primevue/dialog";
const props = defineProps({
    model: String,
    id: Number,
    modelValue: Boolean,
    filters: Array
})
const emit = defineEmits(["update:modelValue"])
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});
const activityLog = ref([])
const getActivity = async () => {
    let string = queryString.stringify({
        page: 1,
        filters: JSON.stringify(props.filters),
        order: "desc",
        orderBy: "created_at",
        limit: 50
    })
    const res = await axios.get('/api/activity' + '?' + string)
    activityLog.value = res.data.data
}

const toggle = (activity) => {
    activity.show_data = !activity.show_data
}
onMounted(() => {
    getActivity()
})
watch(proxyValue, () => {
    getActivity()

})
</script>
<style scoped>
.field-info>td {
    background-color: whitesmoke
}
</style>
