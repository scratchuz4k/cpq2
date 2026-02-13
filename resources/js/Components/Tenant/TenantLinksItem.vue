<template>
    <tr>
        <td style="width: 5%">
            <button class="btn p-1 border-0 handle">
                <font-awesome-icon icon="fas fa-bars" />
            </button>
        </td>
        <td>
            <p v-if="!isEditing">{{ proxyValue.label }}</p>
            <input v-else class="form-control" v-model="temp.label" placeholder="Label" />
        </td>
        <td>
            <p v-if="!isEditing">{{ proxyValue.url }}</p>
            <input v-else class="form-control" v-model="temp.url" placeholder="URL" />
        </td>
        <td>
            <p v-if="!isEditing">{{ proxyValue.is_active ? 'Yes' : 'No' }}</p>
            <div v-else class="d-flex align-items-center justify-content-center">
                <Checkbox
                    inputId="unassigned_licenses"
                    v-model="temp.is_active"
                    :checked="false"
                    binary
                    trueValue="1"
                    falseValue="0"
                />
            </div>
        </td>
        <td>
            <template v-if="!isEditing">
                <font-awesome-icon role="button" class="text-primary me-3" :icon="['fas', 'edit']" @click="edit()" />
                <font-awesome-icon role="button" class="text-secondary" :icon="['fas', 'trash']" @click="remove()" />
            </template>
            <template v-else>
                <font-awesome-icon
                    role="button"
                    class="text-secondary me-3"
                    :icon="['fas', 'cancel']"
                    @click="cancel()"
                />
                <font-awesome-icon role="button" class="text-primary" :icon="['fas', 'save']" @click="save()" />
            </template>
        </td>
    </tr>
</template>
<script setup>
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'remove']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const isEditing = ref(false);
const temp = reactive({
    label: '',
    url: '',
    is_active: false
});

const edit = () => {
    temp.label = proxyValue.value.label;
    temp.url = proxyValue.value.url;
    temp.is_active = proxyValue.value.is_active;
    isEditing.value = true;
};

const cancel = () => {
    isEditing.value = false;
};

const save = () => {
    proxyValue.value.label = temp.label;
    proxyValue.value.url = temp.url;
    proxyValue.value.is_active = temp.is_active;
    isEditing.value = false;
};

const remove = () => {
    emit('remove', proxyValue);
};
</script>
