<template>
    <p>Links</p>

    <table class="table w-100">
        <thead>
            <tr>
                <th>Label</th>
                <th>Link</th>
                <th>Active?</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <template v-if="proxyValue && proxyValue.length > 0">
                <tr v-for="link in proxyValue" :key="link.id">
                    <td>{{ link.label }}</td>
                    <td>{{ link.url }}</td>
                    <td>{{ link.is_active ? 'Yes' : 'No' }}</td>
                    <td>
                        <Button
                            type="button"
                            severity="danger"
                            @click="proxyValue.value = proxyValue.value.filter((l) => l.id !== link.id)"
                        >
                            <font-awesome-icon :icon="['fas', 'trash']" />
                        </Button>
                    </td>
                </tr>
            </template>
            <tr v-else>
                <td colspan="4" class="text-center">No links added yet.</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th>
                    <input class="form-control" v-model="temp.label" placeholder="Label" />
                </th>
                <th>
                    <input class="form-control" v-model="temp.url" placeholder="URL" />
                </th>
                <th>
                    <div class="d-flex align-items-center">
                        <Checkbox
                            inputId="unassigned_licenses"
                            v-model="temp.is_active"
                            binary
                            trueValue="1"
                            falseValue="0"
                        />
                    </div>
                </th>
                <th>
                    <Button type="button" :class="['me-2']" severity="primary" @click="add()">
                        <font-awesome-icon :icon="['fas', 'add']" />
                    </Button>
                </th>
            </tr>
        </tfoot>
    </table>
</template>
<script setup>
import { computed, reactive } from 'vue';
import { Button } from 'primevue';

const props = defineProps({
    modelValue: Array
});

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
    if (temp.label && temp.url) {
        const newLink = {
            label: temp.label,
            url: temp.url,
            is_active: temp.is_active
        };
        proxyValue.value.push(newLink);
        temp.label = '';
        temp.url = '';
        temp.is_active = false;
    }
};
</script>
