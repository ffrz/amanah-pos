<script setup>
import PasswordInput from "@/components/PasswordInput.vue";
import StandardCheckBox from "@/components/StandardCheckBox.vue";
import { useApiForm } from "@/composables/useApiForm";
import { handleSubmit } from "@/helpers/client-req-handler";
import { usePage } from "@inertiajs/vue3";

let form = useApiForm({
  username: window.CONFIG.APP_DEMO ? "admin" : "",
  password: window.CONFIG.APP_DEMO ? "12345" : "",
  remember: true,
});

const page = usePage();

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.auth.login"),
    onSuccess: () => {
      form.processing = true;
      window.location.href = route("admin.home");
    },
  });
</script>

<template>
  <i-head title="Login" />
  <guest-layout>
    <q-page class="row justify-center items-center">
      <div class="column">
        <div class="row">
          <q-form class="q-gutter-md" @submit.prevent="submit">
            <q-card square bordered flat class="q-pa-md">
              <q-card-section class="text-center">
                <div class="q-my-sm text-grey-7 text-subtitle1 text-bold">
                  Login ke {{ page.props.company.name }}
                  <div class="flex justify-center">
                    <q-avatar size="100px" class="q-my-none">
                      <q-icon name="badge" color="grey" />
                    </q-avatar>
                  </div>
                </div>
                <q-input
                  v-model.trim="form.username"
                  label="Username"
                  lazy-rules
                  :error="!!form.errors.username"
                  autocomplete="username"
                  :error-message="form.errors.username"
                  :disable="form.processing"
                  :rules="[
                    (val) => (val && val.length > 0) || 'Masukkan Username',
                  ]"
                  hide-bottom-space
                  data-test="username"
                >
                  <template v-slot:append>
                    <q-icon name="person" />
                  </template>
                </q-input>
                <PasswordInput
                  v-model="form.password"
                  label="Kata Sandi"
                  :error-message="form.errors.password"
                  :disable="form.processing"
                  :rules="[
                    (val) => (val && val.length > 0) || 'Masukkan kata sandi',
                  ]"
                  autocomplete="current-password"
                  hide-bottom-space
                  data-test="password"
                />
                <StandardCheckBox
                  class="q-mt-sm"
                  v-model="form.remember"
                  :disable="form.processing"
                  label="Ingat saya di perangkat ini"
                />
              </q-card-section>
              <q-card-actions>
                <q-btn
                  icon="login"
                  type="submit"
                  color="primary"
                  class="full-width"
                  label="Login"
                  :disable="form.processing"
                  :loading="form.loading"
                  data-test="submit"
                />
              </q-card-actions>
            </q-card>
          </q-form>
        </div>
      </div>
    </q-page>
  </guest-layout>
</template>

<style>
.q-card {
  width: 360px;
}
</style>
