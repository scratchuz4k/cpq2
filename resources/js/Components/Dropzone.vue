<template>
         <form action="/api/resources" class="dropzone mb-5" id="dropzone">
         <slot></slot>
         </form>
</template>



<script setup>
import Dropzone from "dropzone"
import { onMounted } from "vue"
const emit = defineEmits(["resource:created"])

onMounted(() => {
     let element = document.getElementById("dropzone");

     new Dropzone(element, {
         url: '/api/resources',
         paramName: "file",
         maxFilesize: 5000,
         uploadMultiple: true,
         acceptedFiles: "image/*,application/pdf, text/*",
         headers: {
             "X-CSRF-TOKEN": document
                 .querySelector('meta[name="csrf-token"]')
                 .getAttribute("content"),
             },
         init: function () { },
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
