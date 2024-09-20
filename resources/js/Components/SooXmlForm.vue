<script setup>
import {reactive, ref} from 'vue';
import {router, usePage, useForm} from "@inertiajs/vue3";

const page = usePage();


const props = defineProps({
    tenantName: String,
    tenant: Object,
    modelValue: Object,
});

const ssoText = ref(props.modelValue?.file_content)

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
    router.post(`/tenant/${props.tenant.id}`,
    {
        _method: "PUT",
        file: formFile.file
    }
)};

const submitTextForm = () => {
    router.post(`/tenant/${props.tenant.id}`,
        {
            _method: "PUT",
            sso_text_area: ssoText.value
        }
    )
}
</script>

<template>
    <br>
    <h4>Import XML file for {{ tenantName }}</h4>
    <p>The file to upload must be a .xml</p>

    <form @submit.prevent="submitFileForm" enctype="multipart/form-data">
        <input type="file" accept=".xml" required @input="onFileChange" />
        <button type="submit" class="btn btn-success">Upload</button>
    </form>
    <div class="separator pt-4"> OR </div>
    <div class="mt-4">
        <h4>Input SSO configuration for {{tenantName}}</h4>
        <p>Please provide a valid xml syntax</p>

        <form @submit.prevent="submitTextForm" enctype="multipart/form-data">
            <textarea v-model="ssoText" class="w-100 p-4" rows="10"></textarea>
            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>
</template>

<style scoped></style>
