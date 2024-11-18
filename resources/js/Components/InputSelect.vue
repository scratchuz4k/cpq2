<template>
    <div style="position: relative">
        <div :id="'select-' + props.id" class="select-options input form-select p-1" @click="openOption()">
            <p v-if="proxyValue" class="select-option px-2">
                <template v-for="(key, i) in props.fields">
                    <span v-if="i != 0"> | </span>
                    <span>{{ proxyValue[key] }}</span>
                </template>
                <font-awesome-icon icon="fa-close" class="ms-2" @click="removeOption(i)" />
            </p>
        </div>
        <div :id="'select-items-' + props.id" class="select-items p-3"
            :class="{ 'show': showOptions, 'hide': !showOptions }">
            <input type="text" class="form-control " v-model="searchInput" placeholder="Search" @input="search">

            <div class="mt-2">
                <template v-if="options && options.length">
                    <p class="select-item" :class="[checkIfExists(item) ? 'text-bold' : null, 'select-item-' + key]"
                        v-for="( item, key ) in options " :key="key" @click="selectOption(item)">
                        <template v-for="(key, i) in props.fields">
                            <span v-if="i != 0"> | </span>
                            <span>{{ item[key] }}</span>
                        </template>
                    </p>
                </template>

                <template v-else-if="!isLoading">
                    <p class="select-item">
                        No Records
                    </p>
                </template>

                <template v-else>
                    <!-- <app-loader></app-loader> -->
                </template>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`
        }
    },
    url: String,
    fields: Array,
    modelValue: Object
})

const emit = defineEmits(["update:modelValue"])
const showOptions = ref(false)
const options = ref([])
const isLoading = ref(false)
const searchInput = ref(null)

const proxyValue = ref(props.modelValue);


const openOption = () => {
    showOptions.value = !showOptions.value
}

const search = async () => {
    if (!props.url) {
        return
    }
    let string = searchInput.value ? `search=${searchInput.value}` : ''
    isLoading.value = true
    let res = await axios.get(`/api${props.url}?${string}`)
    isLoading.value = false
    options.value = res.data.data

}

const selectOption = (item) => {
    proxyValue.value = item
    emit("update:modelValue", proxyValue.value)
}

const removeOption = () => {
    proxyValue.value = null
    emit("update:modelValue", proxyValue.value)
}

const checkIfExists = (item) => {
    return proxyValue.value ? proxyValue.value.id == item.id : false
}

onMounted(() => {
    document.addEventListener('click', function (event) {
        const parent = document.getElementById('select-' + props.id)
        const div = document.getElementById('select-items-' + props.id)
        const targetElement = event.target
        if (parent && parent.contains(targetElement)) {
            return
        }
        if (div && !div.contains(targetElement)) {
            showOptions.value = false
        }
    })
})
search()

</script>

<style scoped>
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
</style>
