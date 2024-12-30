<template>
    <Dialog v-model:visible="proxyValue" modal :style="{ width: '60rem', marginTop: '100px' }" position="top"
        :draggable="false">
        <template #header>
            <h2>Activity</h2>
        </template>
        <table class="table w-100">
            <tbody>
                <template v-for="activity in activityLog">
                    <tr>
                        <td colspan="2">
                            <button class="btn p-0" @click="toggle(activity)">
                                <font-awesome-icon v-if="!activity.show_data"
                                    :icon="['fas', 'plus']"></font-awesome-icon>
                                <font-awesome-icon v-else :icon="['fas', 'minus']"></font-awesome-icon>
                            </button>

                            {{ activity.user.name }}
                            <p class="smaller badge bg-secondary text-white ">{{ activity.activitable_type }}</p>
                            <p class="badge  text-white mx-2"
                                :class="[activity.operation == 'created' ? 'bg-success' : 'bg-primary']">{{
                                    activity.operation }}</p>
                        </td>
                        <td>
                            <p class="text-end"> {{ format.date(activity.created_at) }}</p>
                        </td>
                    </tr>
                    <tr v-for="item in activity.data" v-if="activity.show_data">
                        <td colspan="3">
                            <p class="small">{{ item }}</p>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
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
