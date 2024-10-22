<script setup>
import TreeTableItem from "@/Components/TreeTableItem.vue";
import queryString from "query-string";
import axios from "axios";
import {reactive, ref, watch} from "vue";
import Modal from "@/Components/Modal.vue";
import Dialog from "primevue/dialog";
import IconField from "primevue/iconfield";
import InputText from "primevue/inputtext";
import InputIcon from "primevue/inputicon";
import _ from "lodash";

const props = defineProps({
    route:String,
    columns: Array,
    modelValue: Array,
    allow: {
        type: Object,
        default:{
            update: true,
            delete: true,
            link: false,
            commission: false
        }
    }
})
let data = ref([]);
let selectedItem = ref(null)
let showModal = ref(false)
const global = ref(null)


const state = reactive({});

const getData = async () => {
    let string = queryString.stringify({
        filters: JSON.stringify([{
            'column': 'parent_id',
            'operator': 'is_null ',
            'value': 'null'
        }]),
        search: global.value
    })

    let res = await axios.get('/api' + props.route + '?' + string)
    data.value = res.data.data;
}
getData()

emitter.on('change-selected', (item) => {
    selectedItem.value = item
    showModal.value = !showModal.value
})

watch([global], _.debounce(function() {
    getData()
}, 500))
</script>

<template>
    <div>
        <div class="d-flex justify-content-end">
            <IconField>
                <InputIcon>
                    <font-awesome-icon :icon="['fas', 'search']" />
                </InputIcon>
                <InputText v-model="global" type="search" placeholder="Keyword Search" />
            </IconField>
        </div>
        <table class="table w-100">
            <thead>
            <tr>
                <th v-for="(column, key) in columns" :key="key">
                    {{column.title}}
                </th>
                <th>

                </th>
            </tr>
            </thead>
            <tbody>
            <TreeTableItem :data="data" :columns="columns" :route="route" :state="state" v-model="props.modelValue" :allow="allow"></TreeTableItem>
            </tbody>
        </table>
        <Dialog v-model:visible="showModal" modal :style="{ marginTop: '4rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" :draggable="false" position="top">
            <template #header>

            </template>
            <slot name="dialog_content" :data="selectedItem" :toggleModal="() => showModal = !showModal"/>
        </Dialog>
    </div>
</template>

<style scoped>

</style>
