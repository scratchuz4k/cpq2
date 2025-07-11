<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'nextStep']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});
</script>

<template>
    <div class="row mb-3">
        <div class="col-lg-6">
            <div class="panel mb-3">
                <div class="row">
                    <div class="col-12">
                        <h5 class="font-bold mb-3">Select an account to start a quote</h5>
                        <div class="mb-3">
                            <label for="account_id" class="form-label">
                                Account <span class="text-primary fw-bold">*</span></label
                            >
                            <InputSelect
                                id="account_id"
                                v-model="proxyValue.account"
                                url="/accounts"
                                :fields="['name', 'reference']"
                                :placeholder="'Select an Account'"
                                :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                            >
                            </InputSelect>
                        </div>
                        or create a
                        <a id="link-to-create-account" href="/accounts/create" class="text-primary">new account</a>
                    </div>
                </div>
            </div>
            <ButtonStep v-model="proxyValue" :disabled="proxyValue.account == null"></ButtonStep>
        </div>
    </div>
</template>
