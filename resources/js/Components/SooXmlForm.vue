<script setup>
import {reactive, ref} from 'vue';
import {router, usePage, useForm} from "@inertiajs/vue3";

const page = usePage();

const props = defineProps({
    titleImport: String,
    subTitleImport: String,
    titleTextArea: String,
    subTitleTextArea: String,
    files: Array,
    tenant: Number
});

/**
 * Handle file form
 */
const onFileChange = (event) => {
    formFile.file = event.target.files[0];
};

const formFile = reactive({
    file: null
});

const submitFileForm = () => {
    router.post(`/tenant/${props.tenant}`,
    {
        _method: "PUT",
        file: formFile.file
    }
)};


/**
 * Handle Textarea form
 */
const formTextArea = reactive({
    sso_text_area: null
})

const submitTextForm = () => {
    router.post(`/tenant/${props.tenant}`,
        {
            _method: "PUT",
            sso_text_area: props.files[props.tenant]
        }
    )
}
</script>

<template>
<!--    {{ files }}-->
    <br>
    <h4>{{ titleImport }}</h4>
    <p>{{ subTitleImport }}</p>

    <form @submit.prevent="submitFileForm" enctype="multipart/form-data">
        <input type="file" accept=".xml" required @input="onFileChange" />
        <button type="submit" class="btn btn-success">Upload</button>
    </form>
    <div class="separator pt-4"> OR </div>
    <div class="mt-4">
        <h4>{{ titleTextArea }}</h4>
        <p>{{ subTitleTextArea }}</p>

        <form @submit.prevent="submitTextForm" enctype="multipart/form-data">
            <textarea v-model="files[tenant]" class="w-100 p-4" rows="10"></textarea>
            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>

</template>

<style scoped>

</style>
