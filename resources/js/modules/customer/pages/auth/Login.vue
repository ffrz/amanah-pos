<script setup>
import PasswordInput from "@/components/PasswordInput.vue";
import { handleSubmit } from "@/helpers/client-req-handler";
import { useForm } from "@inertiajs/vue3";

let form = useForm({
  code: window.CONFIG.APP_DEMO ? "CST-0001" : "",
  password: window.CONFIG.APP_DEMO ? "12345" : "",
  remember: true,
});

const submit = () => handleSubmit({ form, url: route("customer.auth.login") });
</script>

<template>
  <i-head title="Login" />
  <guest-layout>
    <q-page class="row justify-center items-center">
      <div class="column">
        <div class="row">
          <q-form class="q-gutter-md" @submit.prevent="submit">
            <q-card square bordered class="q-pa-md shadow-1">
              <q-card-section>
                <h5 class="q-my-sm text-center">Masuk Pelanggan</h5>
              </q-card-section>
              <q-card-section>
                <q-input
                  v-model.trim="form.code"
                  label="Username"
                  lazy-rules
                  :error="!!form.errors.code"
                  autocomplete="code"
                  :error-message="form.errors.code"
                  :disable="form.processing"
                  :rules="[
                    (val) => (val && val.length > 0) || 'Masukkan Username',
                  ]"
                  hide-bottom-space
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
                <q-checkbox
                  class="q-mt-sm q-pl-none"
                  style="margin-left: -10px"
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
