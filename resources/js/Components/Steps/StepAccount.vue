<script setup>
import { computed, onMounted, watch } from 'vue';
import { isDisabled } from '@/Composables/useQuoteSteps.js';
import queryString from 'qs';

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

const urlParams = new URLSearchParams(window.location.search);
const params = new Proxy(urlParams, {
    get: (searchParams, prop) => searchParams.get(prop)
});
const account_reference = params['account_reference'] ?? null;
const getAccount = async () => {
    if (!account_reference) {
        return;
    }
    let string = queryString.stringify({
        filters: [
            {
                column: 'reference',
                operator: 'equals',
                value: account_reference
            }
        ]
    });

    const res = await axios.get('/api/accounts' + '?' + string);
    if (res.data.data.length) {
        proxyValue.value.account = res.data.data[0];
    }
};

onMounted(() => {
    isDisabled.value = proxyValue.value.account === null;
});

watch(
    () => [proxyValue.value.account],
    () => {
        if (proxyValue.value.account === null) {
            isDisabled.value = true;
        } else {
            isDisabled.value = false;
        }
    }
);

getAccount();
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
        </div>
    </div>
</template>
