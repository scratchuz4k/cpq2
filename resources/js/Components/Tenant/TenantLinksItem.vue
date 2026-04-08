<template>
    <tr>
        <td style="width: 5%">
            <button :id="`${props.id}-${index}-link-move`" class="btn p-2 border-0 handle">
                <font-awesome-icon icon="fas fa-bars" />
            </button>
        </td>
        <td>
            <p :id="`${props.id}-${index}-link-label`" class="py-2" v-if="!isEditing">{{ proxyValue.label }}</p>
            <input
                :id="`${props.id}-${index}-link-edit-label`"
                v-else
                class="form-control"
                v-model="temp.label"
                placeholder="Label"
                maxlength="20"
            />
        </td>
        <td>
            <p :id="`${props.id}-${index}-link-url`" class="py-2" v-if="!isEditing">{{ proxyValue.url }}</p>
            <input
                :id="`${props.id}-${index}-link-edit-url`"
                v-else
                class="form-control"
                v-model="temp.url"
                placeholder="URL"
            />
        </td>
        <td>
            <p class="py-2 text-center">
                <Checkbox
                    v-if="!isEditing"
                    :id="`${props.id}-${index}link-is_active`"
                    v-model="proxyValue.is_active"
                    :checked="!!proxyValue.is_active"
                    class="ms-2 opacity-100"
                    binary
                    trueValue="1"
                    falseValue="0"
                    :disabled="true"
                />
                <Checkbox
                    v-else
                    :id="`${props.id}-${index}link-edit-is_active`"
                    v-model="temp.is_active"
                    :checked="!!temp.is_active"
                    class="ms-2 opacity-100"
                    binary
                    trueValue="1"
                    falseValue="0"
                />
            </p>
        </td>
        <td>
            <div class="d-flex justify-content-end">
                <template v-if="!isEditing">
                    <a
                        :id="`${props.id}-${index}-link-edit`"
                        class="btn p-2 mx-1 border-0"
                        role="button"
                        @click="edit()"
                        ><font-awesome-icon class="text-primary" :icon="['fas', 'edit']"
                    /></a>
                    <a
                        :id="`${props.id}-${index}-link-remove`"
                        class="btn p-2 mx-1 border-0"
                        role="button"
                        @click="remove()"
                        ><font-awesome-icon class="text-secondary" :icon="['fas', 'trash']"
                    /></a>
                </template>
                <template v-else>
                    <a
                        :id="`${props.id}-${index}-link-cancel`"
                        class="btn p-2 mx-1 border-0"
                        role="button"
                        @click="cancel()"
                    >
                        <font-awesome-icon class="text-secondary" :icon="['fas', 'cancel']" />
                    </a>
                    <a
                        :id="`${props.id}-${index}-link-save`"
                        class="btn p-2 mx-1 border-0"
                        role="button"
                        @click="save()"
                    >
                        <font-awesome-icon class="text-primary" :icon="['fas', 'save']" />
                    </a>
                </template>
            </div>
        </td>
    </tr>
</template>
<script setup>
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    modelValue: Object,
    id: String,
    index: Number
});

const emit = defineEmits(['update:modelValue', 'removed', 'edited']);
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
    temp.label = '';
    temp.url = '';
    temp.is_active = false;
    isEditing.value = false;
};

const save = () => {
    temp.label = temp.label.trim();
    temp.url = temp.url.trim();

    if (!temp.label) {
        notyf.error(`Please enter a label`);
        return;
    }

    if (!isValidHttpUrl(temp.url)) {
        notyf.error('Please enter a valid URL');
        return;
    }
    if (temp.label.length > 20) {
        notyf.error('Label must be less than 20 characters');
        return;
    }

    proxyValue.value.label = temp.label;
    proxyValue.value.url = temp.url;
    proxyValue.value.is_active = temp.is_active;
    isEditing.value = false;
    emit('edited');
};

const remove = () => {
    emit('removed', proxyValue);
};

const isValidHttpUrl = (str) => {
    try {
        const url = new URL(str);
        return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (error) {
        return false;
    }
};
</script>
