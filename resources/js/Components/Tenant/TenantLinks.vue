<template>
    <p>Links</p>

    <table class="table w-100">
        <thead>
            <tr>
                <th></th>
                <th style="width: 40%">Label</th>
                <th style="width: 35%">Link</th>
                <th style="width: 10%">Active?</th>
                <th></th>
            </tr>
        </thead>
        <template v-if="proxyValue && proxyValue.length > 0">
            <draggable v-model="proxyValue" item-key="id" tag="tbody" v-bind="dragOptions" handle=".handle">
                <template #item="{ element, index }">
                    <TenantLinksItem
                        v-model="proxyValue[index]"
                        @remove="proxyValue.splice(index, 1)"
                    ></TenantLinksItem>
                </template>
            </draggable>
        </template>
        <tbody v-else>
            <tr>
                <td colspan="5" class="text-center">No links added yet.</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th>
                    <input id="tenant-new-link-label" class="form-control" v-model="temp.label" placeholder="Label" />
                </th>
                <th>
                    <input id="tenant-new-link-url" class="form-control" v-model="temp.url" placeholder="URL" />
                </th>
                <th>
                    <div class="d-flex align-items-center justify-content-center">
                        <Checkbox
                            id="tenant-new-link-is-active"
                            inputId="unassigned_licenses"
                            v-model="temp.is_active"
                            :checked="temp.is_active"
                            binary
                            trueValue="1"
                            falseValue="0"
                        />
                    </div>
                </th>
                <th>
                    <font-awesome-icon id="tenant-new-link-add" :icon="['fas', 'add']" @click="add()" />
                </th>
            </tr>
        </tfoot>
    </table>
</template>
<script setup>
import { computed, reactive, ref } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    modelValue: Array,
    linkLimit: {
        type: Number,
        default: null
    }
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const temp = reactive({
    label: '',
    url: '',
    is_active: false
});

const add = () => {
    if (props.linkLimit && proxyValue.value.length >= props.linkLimit) {
        notyf.error(`You can only add up to ${props.linkLimit} links`);
        return;
    }

    if (!temp.label || !temp.url) {
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

    const newLink = {
        label: temp.label,
        url: temp.url,
        is_active: temp.is_active
    };
    proxyValue.value.push(newLink);
    temp.label = '';
    temp.url = '';
    temp.is_active = false;
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
