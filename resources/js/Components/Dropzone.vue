<template>
    <form action="/api/resources" class="dropzone mb-3" id="dropzone">
        <slot></slot>
    </form>
</template>

<script setup>
import Dropzone from 'dropzone';
import { onMounted } from 'vue';

const props = defineProps({
    acceptedFiles: String
});
const emit = defineEmits(['resource:created']);

onMounted(() => {
    let element = document.getElementById('dropzone');

    new Dropzone(element, {
        url: '/api/resources',
        paramName: 'file',
        maxFilesize: 2,
        uploadMultiple: true,
        acceptedFiles: props.acceptedFiles,
        addRemoveLinks: true,
        dictRemoveFile: '×',
        dictCancelUpload: null,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        init: function () {
            this.on('error', function (file, response) {
                window.notyf.error(response.message ?? response);
                this.removeFile(file);
            });
        },
        success: (res, data) => onUpload(data)
    });
});

const onUpload = (data) => {
    window.notyf.success(data.message);
    emit('resource:created');
};
</script>

<style lang="scss" scoped>
.dropzone {
    min-height: 150px;
    border: 2px dashed #a34796;
    background: white;
    padding: 20px 20px;
}
</style>
