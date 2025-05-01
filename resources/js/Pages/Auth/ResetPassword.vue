<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
  email: {
    type: String,
    required: true,
  },
  token: {
    type: String,
    required: true,
  },
});

const form = useForm({
  token: props.token,
  email: props.email,
  password: "",
  password_confirmation: "",
});

const submit = () => {
  form.post(route("password.store"), {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Reset Password" />

    <div class="panel-login row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6 col-xl-4">
        <div class="card">
          <div class="card-header">Reset Password</div>
          <div class="card-body p-4">
            <form @submit.prevent="submit">
              <div>
                <div
                  v-for="key in Object.keys(form.errors)"
                  class="info-box-primary d-flex p-2 mb-2"
                >
                  <font-awesome-icon :class="['p-1']" :icon="['fas', 'circle-info']" />
                  <p class="my-0 ps-2 my-auto">
                    {{ form.errors[key] }}
                  </p>
                </div>

                <InputLabel for="email" value="Email" />

                <TextInput
                  id="email"
                  type="email"
                  class="mt-1 block w-full"
                  v-model="form.email"
                  required
                  autofocus
                  autocomplete="username"
                />
              </div>

              <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                  id="password"
                  type="password"
                  class="mt-1 block w-full"
                  v-model="form.password"
                  required
                  autocomplete="new-password"
                />
              </div>

              <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput
                  id="password_confirmation"
                  type="password"
                  class="mt-1 block w-full"
                  v-model="form.password_confirmation"
                  required
                  autocomplete="new-password"
                />
              </div>

              <div class="flex items-center justify-end mt-4">
                <PrimaryButton
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  Reset Password
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>
