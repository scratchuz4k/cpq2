<script setup>
import { usePage } from "@inertiajs/vue3";
import queryString from "query-string";
import Button from "primevue/button";
import Card from "primevue/card";
import Dialog from "primevue/dialog";
import { onMounted, reactive, ref, watch } from "vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import axios from "axios";
import Paginator from "primevue/paginator";
import _ from "lodash";
import InputText from "primevue/inputtext";
import InputIcon from "primevue/inputicon";
import IconField from "primevue/iconfield";

const page = usePage();

let event = ref(null);
let showModal = ref(false);
let selectedItem = ref(null);
const images = ref([]);
const global = ref("");
const acceptedFiles = ref([]);
const extensions = ref("");
const meta = reactive({
  page: 1,
  total: 0,
  first: 1,
  limit: 10,
});
const getImages = async () => {
    images.value = []
    extensions.value = Object.fromEntries(
        acceptedFiles.value.map(mimeType => [mimeType, `.${mimeType.split('/')[1]}`])
    )
    let string = queryString.stringify({
        page: meta.page,
        order: 'desc',
        orderBy: 'created_at',
        search: global.value,
        limit: meta.limit,
        filters: JSON.stringify([{
            column: 'mime_type',
            operator: 'in',
            value: acceptedFiles.value.join(',')
        }]),
    })
    let res = await axios.get('/api/resources?' + string)
    images.value = res.data.data
    meta.total = res.data.meta.total
    meta.first = (meta.page - 1) * meta.limit
}

const handlePaginator = (ev) => {
  meta.limit = ev.rows;
  meta.page = ev.page + 1;
  getImages();
};

const selectItem = (item) => {
  selectedItem.value = item;
};

const remove = async (item) => {
  swal
    .fire({
      title: "Are you sure?",
      customClass: {
        container: "deleteFileConfirm",
      },
    })
    .then(async (result) => {
      if (["cancel", "backdrop"].includes(result.dismiss)) {
        return;
      }
      try {
        let res = await axios.delete("/api/resources/" + item.id);
        selectedItem.value = null;
        getImages();
        notyf.success("Removed with success");
        return;
      } catch (err) {
        notyf.error(err?.response?.data?.message ?? "Something went wrong");
      }
    });
};

const saveItem = () => {
    showModal.value = !showModal.value
    emitter.emit(event.value, selectedItem.value);
    selectedItem.value = null
    global.value = ''
}

const closeModal = () => {
    showModal.value = !showModal.value
    selectedItem.value = null
    global.value = ''
}

emitter.on("resources:open", (params) => {
  acceptedFiles.value = params.acceptedFiles;
  event.value = params.event;
  showModal.value = true;
  getImages();
});

watch(
  [global],
  _.debounce(function () {
    meta.page = 1;
    getImages();
  }, 500)
);

onMounted(() => {
  getImages();
});
</script>
<template>
    <Dialog v-model:visible="showModal" modal :style="{ marginTop: '3rem', width: '80rem' }" :draggable="false"
            position="top" class="resources-modal">
        <template #header>

        </template>
        <dropzone @resource:created="getImages" :acceptedFiles="Object.values(extensions).join(', ')">
            <div class="dz-default dz-message m-0">
                <div class="d-flex align-content-center flex-column">
                    <font-awesome-icon :icon="['fa-solid', 'images']" class="color-purple p-2" style="font-size: 5rem"/>
                    <p class="fw-bold fs-5 lh-sm m-auto" style="max-width: 150px">Drag & Drop or <span class="color-purple">browse</span></p>
                    <p>Only {{ Object.values(extensions).join(', ')}} files can be selected</p>
                </div>
            </div>
        </dropzone>
        <div class="justify-content-between d-flex">
            <h5 class="mb-4">Resources Library</h5>
            <IconField>
                <InputIcon id="resources_search_icon">
                    <font-awesome-icon :icon="['fas', 'search']" />
                </InputIcon>
                <InputText id="resources_search" v-model="global" type="search" placeholder="Keyword Search" />
            </IconField>
        </div>
        <p v-if="images.length === 0" class="h5 text-center">No files found</p>
        <div v-else class="row row-cols-5" id="library-container">
            <div v-for="image in images" class="col mb-4">
                <Card :class="[
                        image == selectedItem ? 'selected' : '',
                        'd-flex', 'justify-center',
                        'file-card',
                        'h-100',
                        'position-relative'
                    ]"
                    :pt="{
                        body: {class: 'justify-content-between h-100'}
                    }"
                      @click="selectItem(image)"
                >
                    <template #header>
                        <div class="d-flex justify-content-center w-100 position-relative">
                            <div v-if="image === selectedItem" class=delete-button-container>
                                <button class="d-inline-block p-0 m-0 btn delete-button" @click="remove(image)" label="Remove File">
                                    <font-awesome-icon icon="fas fa-trash-alt" />
                                </button>
                            </div>
                            <img alt="user header" :src="image.mime_type === 'text/xml' ? '/images/xml_icon.svg' : image.route"
                                 style="height: 50px;max-width:100%; object-fit: scale-down;object-position: center;" />
                        </div>
                    </template>
                    <template #title>
                        <div class="d-flex justify-content-between">
                            <p class="w-100 overflow-hidden loaded-file-title">
                                {{ image.title }}
                            </p>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
        <Paginator v-model:first="meta.first" :rows="meta.limit" :totalRecords="meta.total"
                   :rowsPerPageOptions="[10, 20, 50]" @page="handlePaginator">
        </Paginator>
        <div class="d-flex justify-content-center action-button-wrapper">
            <Button label="Close" class="btn btn-secondary close-modal me-2" @click="() => closeModal()" />
            <Button :disabled="!selectedItem" label="Save" class="btn btn-primary" severity="success" @click="() => saveItem()" />
        </div>
    </Dialog>
</template>
<style scoped>
#resources_search_icon {
  --p-icon-size: 1.5rem;
}
.loaded-file-title{
    text-overflow: ellipsis;
}
.file-card:hover {
  box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px, rgb(51, 51, 51) 0px 0px 0px 1px;
  cursor: pointer;
}
.selected {
  box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px, rgb(51, 51, 51) 0px 0px 0px 1px;
  background-color: whitesmoke;
}
.delete-button-container {
    position: absolute;
    top: -10px;
    right: 10px;

  &:hover {
    .delete-button {
      transform: scale(1.2);
    }
  }

  .delete-button {
    font-size: 20px;
    transition: 0.3s all ease-in-out;
  }
}
.action-button-wrapper button.p-button {
    padding-left: 8rem;
    padding-right: 8rem;
}
</style>
