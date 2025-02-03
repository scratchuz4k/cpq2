<template>
    <form action="/api/resources" class="dropzone mb-5" id="dropzone">
        <slot></slot>
    </form>
</template>


<script setup>
import Dropzone from "dropzone"
import {onMounted} from "vue"

const props = defineProps({
    acceptedFiles: String
})
const emit = defineEmits(["resource:created"])

onMounted(() => {
    let element = document.getElementById("dropzone");

    new Dropzone(element, {
        url: '/api/resources',
        paramName: "file",
        maxFilesize: 2,
        uploadMultiple: true,
        acceptedFiles: props.acceptedFiles,
        addRemoveLinks: true,
        dictRemoveFile: "×",
        dictCancelUpload: null,
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        init: function () {
            this.on("addedfile", function(file) {
                const maxSize = this.options.maxFilesize * 1024 * 1024;
                const acceptedFiles = this.options.acceptedFiles ? this.options.acceptedFiles.split(",") : [];
                const isAcceptedType = acceptedFiles.some(ext => {
                    return file.name.toLowerCase().endsWith(ext.trim());
                });

                if (file.size > maxSize || !isAcceptedType) {
                    this.removeFile(file);
                }
            });
        },
        success: (res, data) => onUpload(data),
        error: (res, data) => onError(data)
    })
})

const onUpload = (data) => {
    window.notyf.success(data.message)
    emit("resource:created")
}

const onError = (data) => {
    window.notyf.error(data.message ?? data)
}
</script>


<style lang="scss" scoped>
.dropzone {
    min-height: 150px;
    border: 2px dashed #a34796;
    background: white;
    padding: 20px 20px;
}
</style>
