<template>
    <table class="table w-100">
        <thead>
            <tr>
                <th></th>
                <th style="width: 40%">Label</th>
                <th style="width: 35%">Link</th>
                <th class="text-center" style="width: 5%">Active?</th>
                <th></th>
            </tr>
        </thead>
        <template v-if="proxyValue && proxyValue.length > 0">
            <draggable
                v-model="proxyValue"
                item-key="id"
                tag="tbody"
                v-bind="dragOptions"
                handle=".handle"
                @end="emit('change')"
            >
                <template #item="{ element, index }">
                    <TenantLinksItem
                        v-model="proxyValue[index]"
                        :id="props.id"
                        :index="index"
                        :links="availableSocialLinks"
                        @removed="() => removeRow(index)"
                        @edited="
                            () => {
                                emit('change');
                                availableSocialLinks = getAvailableSocialLinks();
                            }
                        "
                    ></TenantLinksItem>
                </template>
            </draggable>
        </template>
        <tbody v-else>
            <tr>
                <td colspan="5" class="text-center py-3">No links added yet.</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th>
                    <Select
                        v-if="availableSocialLinks?.length > 0"
                        inputId="${props.id}-new-link-ion"
                        v-model="temp.icon"
                        :options="socialLinks"
                        placeholder="Select an Icon"
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
                                <span class="me-3">{{ value.value }}</span>
                            </div>
                        </template>
                    </Select>

                    <input
                        v-else
                        :id="`${props.id}-new-link-label`"
                        class="form-control"
                        v-model="temp.label"
                        placeholder="Label"
                        maxlength="20"
                    />
                </th>
                <th>
                    <input :id="`${props.id}-new-link-url`" class="form-control" v-model="temp.url" placeholder="URL" />
                </th>
                <th>
                    <div class="d-flex align-items-center justify-content-center">
                        <Checkbox
                            :id="`${props.id}-new-link-is_active`"
                            inputId="unassigned_licenses"
                            v-model="temp.is_active"
                            :checked="temp.is_active"
                            class="ms-2"
                            binary
                            trueValue="1"
                            falseValue="0"
                        />
                    </div>
                </th>
                <th class="text-end">
                    <a :id="`${props.id}-new-link-add`" class="btn p-2 mx-1 border-0" role="button" @click="add()">
                        <font-awesome-icon :icon="['fas', 'add']" />
                    </a>
                </th>
            </tr>
        </tfoot>
    </table>
</template>
<script setup>
import { computed, reactive, ref } from 'vue';
import draggable from 'vuedraggable';
import Select from 'primevue/select';

const props = defineProps({
    modelValue: Array,
    id: String,
    linkLimit: {
        type: Number,
        default: null
    },
    socialLinks: {
        type: Array,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'change']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const getAvailableSocialLinks = () =>
    (props.socialLinks ?? []).filter(
        (link) =>
            !proxyValue.value?.some(
                (item) => item.icon === link.icon && item.is_active !== 0 && item.is_active !== false
            )
    );

const availableSocialLinks = ref(getAvailableSocialLinks());

const temp = reactive({
    label: '',
    url: '',
    is_active: false
});

const removeRow = (index) => {
    const removed = proxyValue.value[index];
    proxyValue.value.splice(index, 1);
    availableSocialLinks.value = getAvailableSocialLinks();
    emit('change');
};

const add = () => {
    debugger;
    // const activeLinks = proxyValue.value.filter(item => !!item.is_active);
    if (props.linkLimit && activeLinks.length >= props.linkLimit) {
        notyf.error(`You can only add up to ${props.linkLimit} links`);
        return;
    }

    const newLink = {
        label: temp.label.trim() ? temp.label.trim() : temp.icon.value,
        url: temp.url.trim(),
        is_active: temp.is_active,
        ...(temp.icon && {
            icon: temp.icon.icon
        })
    };

    if (!newLink.label) {
        notyf.error(`Please enter a label`);
        return;
    }

    if (!isValidHttpUrl(newLink.url)) {
        notyf.error('Please enter a valid URL');
        return;
    }
    if (newLink.label.length > 20) {
        notyf.error('Label must be less than 20 characters');
        return;
    }

    proxyValue.value.push(newLink);
    temp.label = '';
    temp.url = '';
    temp.is_active = false;
    delete temp.icon;
    availableSocialLinks.value = getAvailableSocialLinks();
    emit('change');
};

const isValidHttpUrl = (str) => {
    try {
        const url = new URL(str);
        return url.protocol === 'http:' || url.protocol === 'https:';
    } catch (error) {
        return false;
    }
};

const dragOptions = {
    animation: 200,
    group: 'description',
    disabled: false,
    ghostClass: 'ghost'
};
</script>
