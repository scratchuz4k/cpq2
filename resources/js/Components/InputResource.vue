<template>
    <div class="mt-2" @click="openResources()" style="cursor: pointer">
        <div v-if="item">
            <img
                :src="coverImage(item)"
                style="max-height: 100px; max-width: 300px; object-fit: scale-down; object-position: left"
            />
            <small class="d-block mt-2 text-muted">
                {{ item?.title }}
            </small>
        </div>
        <div
            v-else
            class="d-flex justify-center my-3 input-resource"
            style="height: 120px; width: 120px; align-items: center; border: 3px dotted #a34796; border-radius: 10px"
        >
            <font-awesome-icon class="w-100 mx-auto" style="font-size: 4rem" :icon="['fas', 'plus']" />
        </div>
    </div>
    <div>
        <button
            v-if="item"
            type="button"
            class="btn btn-outline-primary mt-2"
            @click="remove()"
            id="remove-file-button"
        >
            <font-awesome-icon icon="fas fa-trash-alt" />
            Remove file
        </button>
    </div>
</template>
<script setup>
import { v4 as uuid } from 'uuid';
import { computed, ref, watch } from 'vue';
import Button from 'primevue/button';
const props = defineProps({
    resource: Object,
    modelValue: Number,
    showLikeLine: Boolean,
    acceptedFiles: Array
});

const emit = defineEmits(['update:modelValue', 'change']);
const eventFire = `resources:images:${uuid()}`;

const item = ref(props.resource);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const coverImage = (item) => {
    let image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (image_types.includes(item.mime_type)) {
        return item.route;
    } else {
        return '/images/xml_icon.svg';
    }
};
const remove = () => {
    item.value = null;
    proxyValue.value = null;
    emit('change', null);
};

const openResources = () => {
    emitter.emit('resources:open', { event: eventFire, acceptedFiles: props.acceptedFiles });
};
emitter.on(eventFire, (res) => {
    item.value = res;
    proxyValue.value = res.id;

    emit('change', item.value);
});

watch(
    () => props.resource,
    (newValue, oldValue) => {
        item.value = newValue;
    }
);
</script>
