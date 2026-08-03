<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    data: Object,
    value: Number | Boolean,
    route: String,
    eventName: {
        type: String,
        default: 'datatable-refresh'
    }
});

const localActive = ref(Boolean(props.value));

watch(
    () => props.value,
    (newValue) => {
        localActive.value = Boolean(newValue);
    },
    { immediate: true }
);

const isActive = computed(() => Boolean(localActive.value));

const toggle_is_active = async (url, data) => {
    let result = await swal.fire({
        title: 'Do you want to change the active status?'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return false;
    }

    const nextValue = !isActive.value;

    try {
        await axios.put(`/api/${url}/` + data.id, {
            is_active: nextValue
        });

        localActive.value = nextValue;
        if (data) {
            data.is_active = nextValue;
        }
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
            v-if="isActive"
            :icon="['fas', 'circle-check']"
            style="font-size: 1.125rem"
            class="text-primary"
        />
        <font-awesome-icon v-else :icon="['far', 'circle-xmark']" style="font-size: 1.125rem" class="text-secondary" />
    </div>
</template>
