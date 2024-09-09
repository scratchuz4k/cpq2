<script setup>
const props = defineProps({
    accounts: Array,
    modelValue: Object
})
</script>
<template>
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="account_id" class="form-label">Select the Account:</label>
                <select id="account_id" class="form-select" v-model="modelValue.account">
                    <option :value="null">Select an account</option>
                    <option v-for="account in accounts" :value="account">{{ account.name }}</option>
                </select>
            </div>
            Didnt find the account? <a href="/accounts/create"> Create new!</a>
        </div>
    </div>
</template>