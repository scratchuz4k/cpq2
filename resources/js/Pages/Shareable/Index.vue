<script setup>
import ShareLayout from '@/Layouts/ShareLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
const props = defineProps({
    isAuthorized: Boolean,
    url: String
});
const password = ref('')

const withPassword = async () => {
    await router.post(`${props.url}`, { password: password.value }, {
        onError: (errors) => {
            let string = ''
            Object.keys(errors).forEach((key) => {
                string += errors[key] + '<br>'
            })
            notyf.error(string)
        },
    })
}
</script>
<template>

    <Head title="Share" />
    <ShareLayout>
        <h1 v-if="isAuthorized"> Nothing to Show</h1>
        <div v-else class="panel-login row justify-content-center " style="min-height: 50vh;">
            <div class="col-12 col-md-8 col-lg-6 col-xl-4 d-flex align-items-center">
                <div class="card">
                    <div class="card-body p-4">
                        <h2>Password Protected</h2>
                        <div class="mb-3">
                            <label class="form-label">Please enter the password to access the content:</label>
                            <input class="form-control" type="password" v-model="password" placeholder="Enter password"
                                @keyup.enter="checkPassword" />
                        </div>
                        <button class="btn btn-primary" @click="withPassword">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </ShareLayout>
</template>
