<script setup>
const props = defineProps({
    data: Object,
    value: Number | Boolean,
    route: String,
    eventName: {
        type: String,
        default: 'datatable-refresh'
    }
});

const toggle_is_active = async (url, data) => {
    let result = await swal.fire({
        title: 'Do you want to change the active status?'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return false;
    }

    try {
        let res = await axios.put(`/api/${url}/` + data.id, {
            is_active: !data.is_active
        });
        emitter.emit(props.eventName);

        notyf.success('Updated with Success');
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};
</script>
<template>
    <div role="button" @click="toggle_is_active(props.route, props.data)">
        <font-awesome-icon
            v-if="props.value"
            :icon="['fas', 'circle-check']"
            style="font-size: 1.125rem"
            class="text-primary"
        />
        <font-awesome-icon v-else :icon="['far', 'circle-xmark']" style="font-size: 1.125rem" class="text-secondary" />
    </div>
</template>
