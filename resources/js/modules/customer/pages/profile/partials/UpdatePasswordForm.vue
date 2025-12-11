<script setup>
import PasswordInput from "@/components/PasswordInput.vue";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useForm } from "@inertiajs/vue3";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const form = useForm({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const updatePassword = () => {
  form.clearErrors();
  form.post(route("customer.profile.update-password"), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset("password", "password_confirmation");
        passwordInput.value?.focus();
      }
      if (form.errors.current_password) {
        form.reset("current_password");
        currentPasswordInput.value?.focus();
      }
    },
  });
};
</script>

<template>
  <q-form
    class="row"
    @submit.prevent="updatePassword"
    @validation-error="scrollToFirstErrorField"
  >
    <q-card square flat bordered class="col">
      <q-card-section>
        <div class="text-h6 q-my-xs text-subtitle1">Perbarui Kata Sandi</div>
        <p class="text-caption text-grey-9">
          Pastikan akun anda menggunakan kata sandi acak yang panjang agar akun
          tetap aman.
        </p>
        <PasswordInput
          :ref="currentPasswordInput"
          v-model="form.current_password"
          label="Kata Sandi Sekarang"
          autocomplete="off"
          :rules="[
            (val) => (val && val.length > 0) || 'Kata sandi harus diisi.',
          ]"
          autofocus
        />
        <PasswordInput
          :ref="passwordInput"
          v-model="form.password"
          label="Kata Sandi Baru"
          autocomplete="off"
          :rules="[
            (val) => (val && val.length > 0) || 'Kata sandi harus diisi.',
          ]"
        />
        <PasswordInput
          v-model="form.password_confirmation"
          label="Konfirmasi Kata Sandi Baru"
          autocomplete="off"
          :rules="[
            (val) => (val && val.length > 0) || 'Kata sandi harus diisi.',
          ]"
        />
      </q-card-section>
      <q-card-section>
        <q-btn
          type="submit"
          color="primary"
          label="Perbarui Kata Sandi"
          :disable="form.processing"
          icon="save"
        />
      </q-card-section>
    </q-card>
  </q-form>
</template>
