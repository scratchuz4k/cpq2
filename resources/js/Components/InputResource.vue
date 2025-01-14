<template>
    <div class="" @click="openResources()" style="cursor: pointer;">
        <p>Upload file</p>
        <img v-if="item" :src="coverImage(item)" style="object-fit: contain;height: 150px;">
        <div v-else class="d-flex justify-center my-3"
             style="height: 120px; width:120px; align-items: center;border:3px dotted grey;border-radius: 10px;">
            <font-awesome-icon class="w-100" style="font-size: 4rem" :icon="['fas', 'plus']" />
        </div>
    </div>
    <div>
        <button v-if="item" type="button" class="btn btn-outline-primary mt-2" @click="remove">
            <font-awesome-icon icon="fas fa-trash-alt" />
            Remove file
        </button>
    </div>
</template>
<script setup>
import { v4 as uuid } from "uuid"
import { computed, ref } from 'vue';
import Button from "primevue/button";
const props = defineProps({
    resource: Object,
    modelValue: Number,
    showLikeLine: Boolean,
    acceptedFiles: Array
})

const emit = defineEmits(["update:modelValue"])
const eventFire = `resources:images:${uuid()}`

const item = ref(props.resource)
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    },
});

const coverImage = (item) => {
    let image_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (image_types.includes(item.mime_type)) {
        return item.route
    } else {
        return '/images/xml_icon.svg'
    }
}
const remove = () => {
    item.value = null
    proxyValue.value = null
}
const openResources = () => {
    emitter.emit("resources:open", { event: eventFire, acceptedFiles: props.acceptedFiles})
}
emitter.on(eventFire, (res) => {
    item.value = res
    proxyValue.value = res.id
})
</script>



