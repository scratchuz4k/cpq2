<script setup>
import ShareLayout from '@/Layouts/ShareLayout.vue';
import { Head,  useForm } from '@inertiajs/vue3';
const props = defineProps({
    isAuthorized: Boolean,
    url: String
});
const form = useForm({
    password: '',
});

const withPassword = () => {
    form.post(`${props.url}`, {
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
                <form class="card" @submit.prevent="withPassword()">
                    <div class="card-body p-4">
                        <h2>Password Protected</h2>
                        <div class="mb-3">
                            <label class="form-label">Please enter the password to access the content:</label>
                            <input class="form-control" type="password" v-model="form.password" placeholder="Enter password"
                                autocomplete="off" autofocus />
                        </div>
                        <button type="submit" class="btn btn-primary" >Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </ShareLayout>
</template>
