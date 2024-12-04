<template>
    <MultiSelect :id="'select-' + props.id" :inputId="'select-' + props.id + '-input'" v-model="proxyValue"
        :options="options" showClear :optionLabel="props.field" placeholder="Select multiple options" class="w-100"
        display="chip">
        <template #value="{ value, placeholder }">
            <div >
                <span v-for="(item, index) in value" class="chip">
                    {{ item ? item[props.field] : placeholder }}
                    <font-awesome-icon :id="'select-' + props.id + '-remove-' + index" @click="remove(index)"
                        class="ms-1" :icon="['far', 'circle-xmark']"></font-awesome-icon>
                </span>
            </div>
        </template>
        <template #header>
            <IconField class="p-1">
                <InputIcon>
                    <font-awesome-icon icon="fas fa-search" />
                </InputIcon>
                <InputText :id="'select-' + props.id + '-search'" class="w-100" v-model="searchInput"
                    placeholder="Search" />
            </IconField>
        </template>
    </MultiSelect>
</template>
<script setup>

import _ from 'lodash'
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';

import { ref, computed, watch } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`
        }
    },
    url: String,
    field: String,
    modelValue: Object
})

const emit = defineEmits(["update:modelValue"])
const options = ref([])
const isLoading = ref(false)
const searchInput = ref(null)

const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const search = _.debounce(async () => {
    if (!props.url) {
        return
    }
    let string = searchInput.value ? `search=${searchInput.value}` : ''
    isLoading.value = true
    let res = await axios.get(`/api${props.url}?${string}`)
    isLoading.value = false
    options.value = res.data.data.map((e) => {
        let item = { id: e.id }
        item[props.field] = e[props.field]
        return item
    })
}, 500)

const remove = (index) => {
    proxyValue.value.splice(index, 1)
}
watch(searchInput, () => {
    search()
})
search()

</script>

<style >
.select-options {
    min-height: 2.5em;
    height: auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.select-option {
    border: 1px solid hsl(0, 0%, 86%);
    margin-right: 5px;
    border-radius: 4px;
    cursor: pointer;
    background-color: whitesmoke;
}

.select-items {
    position: absolute;
    visibility: hidden;
    pointer-events: none;
    width: 100%;
    max-height: 300px;
    overflow: auto;
    background-color: white;
    padding: 10px;
    z-index: 100;
    border: 1px solid hsl(0, 0%, 86%);
    border-radius: 4px;
    color: hsl(0, 0%, 21%);
}

.select-item:hover {
    font-weight: bold;
    cursor: pointer;
}

.show {
    visibility: visible !important;
    pointer-events: auto !important;
}

.hide {
    visibility: hidden !important;
    pointer-events: auto !important;
}

.disabled {
    pointer-events: none;
    background-color: whitesmoke;
}

.text-bold {
    font-weight: bold;
}

.chip {
    background-color: whitesmoke;
    padding: 0 5px;
    border-radius: 5px;
    margin-left: 5px
}
.p-multiselect-label{
    white-space: normal!important;
}
</style>
