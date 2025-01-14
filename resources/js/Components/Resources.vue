<template>
    <Dialog v-model:visible="showModal" modal :style="{ marginTop: '4rem', width: '80rem' }" :draggable="false"
            position="top">
        <template #header>

        </template>
        <dropzone @resource:created="getImages"></dropzone>
        <h5 class="mb-4 text-center">Only {{ Object.values(extensions).join(', ')}} files can be selected</h5>
        <div class="row row-cols-6 gap-3 mb-3">
            <Card v-for="image in images" :class="[image == selectedItem ? 'selected' : '', 'd-flex', 'justify-center']" style="overflow:hidden">
                <template #header>
                    <div class="d-flex justify-content-center w-100">
                        <img alt="user header" :src="image.mime_type === 'text/xml' ? '/images/xml_icon.svg' : image.route"
                             style="height: 8rem;object-fit: cover;object-position: center;" />
                    </div>
                </template>
                <template #footer>
                    <div class="flex justify-end">
                        <Button label="Select" :severity="image == selectedItem ? '' : 'secondary'"
                                @click="selectItem(image)" />
                    </div>
                </template>
            </Card>
        </div>
        <div class="d-flex justify-end">
            <Button label="Close" class="me-2" @click="() => showModal = !showModal" />
            <Button label="Save" severity="success" @click="() => saveItem()" />
        </div>
    </Dialog>
</template>
<script setup>
import { usePage } from '@inertiajs/vue3';
import queryString from 'query-string';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Dialog from "primevue/dialog";
import { onMounted, ref } from 'vue';

const page = usePage()

let event = ref(null)
let showModal = ref(false)
let selectedItem = ref(null)
const images = ref([])
const global = ref('')
const acceptedFiles = ref([])

const extensions = ref('')

const getImages = async () => {
    images.value = []
    extensions.value = Object.fromEntries(
        acceptedFiles.value.map(mimeType => [mimeType, mimeType.split('/')[1]])
    )
    let string = queryString.stringify({
        page: 1,
        order: 'desc',
        orderBy: 'created_at',
        search: global.value,
        filters: JSON.stringify([{
            column: 'mime_type',
            operator: 'in',
            value: acceptedFiles.value.join(',')
        }]),
    })
    let res = await axios.get('/api/resources?' + string)
    images.value = res.data.data
}

const selectItem = (item) => {
    selectedItem.value = item
}

const saveItem = () => {
    showModal.value = !showModal.value
    emitter.emit(event.value, selectedItem.value);
}

emitter.on("resources:open", (params) => {
    acceptedFiles.value = params.acceptedFiles
    event.value = params.event
    showModal.value = true
    getImages()
});

onMounted(() => {
    getImages()
})
</script>
<style scoped>
.selected {
    box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px, rgb(51, 51, 51) 0px 0px 0px 1px;
}
</style>
