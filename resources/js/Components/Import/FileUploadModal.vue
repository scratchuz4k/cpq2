<script setup>
import { reactive, ref, watch } from 'vue';
import { Dialog, Checkbox, Select } from 'primevue';
import SvgIcon from '@/Components/Custom/SvgIcon.vue';
import { getTemplate, importPreview } from '@/Composables/import.js';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    model: {
        type: String,
        default: ''
    },
    imports: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:visible', 'preview']);

const form = reactive({
    file: null,
    overwrite: 0
});

const isLoading = ref(false);

const onFileChange = (event) => {
    form.file = event.target.files[0];
};

const submitFile = async () => {
    isLoading.value = true;

    try {
        const data = await importPreview(form.file, props.model);
        emit('preview', { data, form: { ...form } });
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    } finally {
        emit('update:visible', false);
        isLoading.value = false;
    }
};
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="(val) => emit('update:visible', val)"
        id="csv-import-form"
        modal
        :style="{ marginTop: '4rem', width: '50rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
        position="top"
        header="Import by CSV file"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-2">
            <hr class="mt-0" />
            <form @submit.prevent="submitFile" enctype="multipart/form-data">
                <div class="row g-3">
                    <div v-if="props.imports.length > 0" class="col-md-3 mb-1">
                        <label for="import" class="form-label">Import Type:</label>
                        <Select
                            inputId="import"
                            v-model="form.model"
                            :options="props.imports"
                            optionLabel="name"
                            optionValue="value"
                            placeholder="Select a Import"
                            class="w-100"
                        />
                    </div>
                    <div :class="props.imports.length > 0 ? 'col-md-9' : ''" class="col-md-9 mb-1">
                        <label for="csv-file" class="form-label">CSV file:</label>
                        <input
                            id="csv-file"
                            type="file"
                            accept=".csv"
                            required
                            @input="onFileChange"
                            class="form-control"
                        />
                    </div>
                </div>
                <p class="py-2 text-muted">
                    <SvgIcon width="20" height="20" name="info" />
                    <i class="small">
                        Download a
                        <a
                            role="button"
                            @click="getTemplate(props.model)"
                            class="color-purple"
                            id="products-import-template-link"
                            >sample CSV template</a
                        >
                        to see and example of the format required. Maximum file upload size is 2 MB.
                    </i>
                </p>
                <div v-if="form.model === 'Product'" class="form-check px-0 pt-2">
                    <Checkbox
                        inputId="overwrite-products"
                        v-model="form.overwrite"
                        binary
                        trueValue="1"
                        falseValue="0"
                    />
                    <label class="form-check-label ms-2" for="overwrite-products">
                        Update existing products that have the same SKU
                    </label>
                </div>
                <hr />
                <button type="submit" class="btn btn-primary" :disabled="isLoading">
                    <span v-if="isLoading">
                        <font-awesome-icon :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                        Uploading...
                    </span>
                    <span v-else>Upload</span>
                </button>
            </form>
        </div>
    </Dialog>
</template>
