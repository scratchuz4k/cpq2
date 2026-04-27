<template>
    <tr>
        <td style="width: 5%">
            <button :id="`${props.id}-${index}-link-move`" class="btn p-2 border-0 handle">
                <font-awesome-icon icon="fas fa-bars" />
            </button>
        </td>
        <td>
            <p :id="`${props.id}-${index}-link-label`" class="py-2" v-if="!isEditing">
                <font-awesome-icon class="me-3" v-if="proxyValue.icon" :icon="['fa-brands', proxyValue.icon]" />
                {{ proxyValue.label }}
            </p>
            <template v-else>
                <Select
                    v-if="props.links?.length > 0"
                    :inputId="`${props.id}-new-link-ion`"
                    v-model="temp.icon"
                    :options="props.links"
                    placeholder="Select a social network"
                    class="w-100"
                >
                    <template #option="{ option }">
                        <div class="d-flex align-items-center gap-2">
                            <font-awesome-icon :icon="['fa-brands', option.icon]" />
                            <span class="me-3">{{ option.value }}</span>
                        </div>
                    </template>

                    <template #value="{ value }">
                        <div v-if="value" class="d-flex align-items-center gap-2">
                            <font-awesome-icon :icon="['fa-brands', value.icon]" />
                            <span class="me-3">{{ value.label ?? value.value }}</span>
                        </div>
                    </template>
                </Select>
                <input
                    :id="`${props.id}-${index}-link-edit-label`"
                    v-else
                    class="form-control"
                    v-model="temp.label"
                    placeholder="Label"
                    maxlength="20"
                />
            </template>
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
                    >
                        <font-awesome-icon class="text-primary" :icon="['fas', 'edit']" />
                    </a>
                    <a
                        :id="`${props.id}-${index}-link-remove`"
                        class="btn p-2 mx-1 border-0"
                        role="button"
                        @click="remove()"
                    >
                        <font-awesome-icon class="text-secondary" :icon="['fas', 'trash']" />
                    </a>
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
import Select from 'primevue/select';

const props = defineProps({
    modelValue: Object,
    id: String,
    index: Number,
    links: {
        type: Array,
        default: null
    },
    linkLimit: {
        type: Number,
        default: null
    },
    allLinks: {
        type: Array,
        default: []
    }
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
    if (proxyValue.value.icon) {
        temp.icon = { icon: proxyValue.value.icon, label: proxyValue.value.label };
    }

    isEditing.value = true;
};

const cancel = () => {
    temp.label = '';
    temp.url = '';
    temp.is_active = false;
    isEditing.value = false;
};

const save = () => {
    const label = temp.icon?.value ?? temp.label;
    temp.label = label.trim();
    temp.url = temp.url.trim();

    const activeLinks = props.allLinks.filter((item, idx) => idx !== props.index && !!item.is_active);
    if (props.linkLimit && activeLinks.length >= props.linkLimit && !!temp.is_active) {
        notyf.error(`You can only add up to ${props.linkLimit} links`);
        return;
    }

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

    if (temp.icon) {
        proxyValue.value.icon = temp.icon.icon;
    }

    isEditing.value = false;
    emit('edited');
};

const remove = async () => {
    let result = await swal.fire({
        title: 'Are you sure?'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return;
    }

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
