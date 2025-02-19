<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";

defineProps({
  status: {
    type: String,
  },
});

const form = useForm({
  email: "",
});

const submit = () => {
  form.post(route("password.email"), {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Forgot Password" />

    <div class="panel-login row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6 col-xl-4">
        <div class="card">
          <div class="card-header">Forgot Password?</div>
          <div class="card-body p-4">
            <div v-if="status" class="info-box-primary d-flex p-2 mb-2">
              <font-awesome-icon :class="['p-1']" :icon="['fas', 'circle-info']" />
              <p class="my-0 ps-2 my-auto">
                {{ status }}
              </p>
            </div>
            <div class="mb-4 text-sm text-gray-600">
              Forgot your password? No problem. Just let us know your email address and we
              will email you a password reset link that will allow you to choose a new
              one.
            </div>
            <form @submit.prevent="submit">
              <div class="row overflow-hidden">
                <div class="mb-3">
                  <span><InputLabel for="email" value="Email" /></span>
                  <TextInput
                    id="email"
                    type="email"
                    :class="{ 'is-invalid': form.errors.email }"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                  />
                  <InputError class="invalid-feedback" :message="form.errors.email" />
                </div>
                <div class="d-grid">
                  <PrimaryButton
                    class="btn-login"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                  >
                    Email Password Reset Link
                  </PrimaryButton>
                </div>
                <div class="text-center my-3 d-block position-relative">
                  <span class="login-seperator">or</span>
                </div>
                <div class="mb-2">
                  <div class="d-grid">
                    <a
                      :href="route('login')"
                      class="btn btn-outline-secondary d-flex align-items-center"
                    >
                      <font-awesome-icon :icon="['fa-solid', 'fa-key']" />
                      <span class="ms-2 fs-6 flex-grow-1">Continue with Login</span>
                    </a>
                  </div>
                </div>
                <div v-if="canGoogleLogin">
                  <div class="d-grid">
                    <a
                      :href="route('login.google')"
                      class="btn btn-outline-secondary d-flex align-items-center"
                    >
                      <font-awesome-icon :icon="['fa-brands', 'fa-google']" />
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
    content: "";
    width: 40%;
    height: 1px;
    background-color: var(--light-grey);
    position: absolute;
    left: 1rem;
    top: 50%;
  }
  .login-seperator:after {
    content: "";
    width: 40%;
    height: 1px;
    background-color: var(--light-grey);
    position: absolute;
    right: 1rem;
    top: 50%;
  }
}
</style>
