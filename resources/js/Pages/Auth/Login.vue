<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    canGoogleLogin: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Login" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <div class="panel-login row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-4">
                <div class="card">
                    <div class="card-header">Login</div>
                    <div class="card-body p-4">
                        <form @submit.prevent="submit">
                            <div class="row overflow-hidden">
                                <div class="mb-3">
                                    <span><InputLabel for="email" value="Email" /></span>
                                    <TextInput id="email" type="email" :class="{'is-invalid': form.errors.email}" v-model="form.email" required autofocus
                                        autocomplete="username" />
                                    <InputError class="invalid-feedback" :message="form.errors.email" />
                                </div>
                                <div>
                                    <span><InputLabel for="password" value="Password" /></span>
                                    <TextInput id="password" type="password" v-model="form.password" required
                                        autocomplete="current-password" />

                                    <InputError class="invalid-feedback" :message="form.errors.password" />
                                </div>

                                <div class="mb-3 d-flex align-items-center justify-content-between">
                                    <div class="form-check">
                                        <Checkbox id="remember" name="remember" class="form-check-input" v-model:checked="form.remember" />

                                        <label class="form-check-label" for="remember">
                                            <small>Remember me</small>
                                        </label>
                                    </div>

                                    <div class="text-end">
                                        <Link v-if="canResetPassword" :href="route('password.request')" class="btn btn-link">
                                            <small>Forgot password?</small>
                                        </Link>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <PrimaryButton id="btn-login" :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing">
                                        Login
                                    </PrimaryButton>
                                </div>
                                <div class="text-center my-3 d-block position-relative">
                                    <span class="login-seperator">or</span>
                                </div>
                                <div class="mb-2">
                                    <div class="d-grid">
                                        <a :href="route('login.sso')" class="btn btn-outline-secondary d-flex align-items-center">
                                            <i class="fa-solid fa-key"></i>
                                            <span class="ms-2 fs-6 flex-grow-1">Continue with SSO</span>
                                        </a>
                                    </div>
                                </div>
                                <div v-if="canGoogleLogin">
                                    <div class="d-grid">
                                        <a :href="route('login.google')" class="btn btn-outline-secondary d-flex align-items-center">
                                            <i class="fa-brands fa-google"></i>
                                            <span class="ms-2 fs-6 flex-grow-1">Continue with Google</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
    .panel-login {
        .login-seperator:before {
            content: '';
            width: 40%;
            height:1px;
            background-color:var(--light-grey);
            position: absolute;
            left: 1rem;
            top: 50%;
        }
        .login-seperator:after {
            content: '';
            width: 40%;
            height:1px;
            background-color:var(--light-grey);
            position: absolute;
            right: 1rem;
            top: 50%;
        }
    }
</style>
