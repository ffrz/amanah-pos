<script setup>
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useForm, usePage } from "@inertiajs/vue3";

const page = usePage();
const user = page.props.auth.user;
const form = useForm({
  name: user.name,
  username: user.username,
  cashier_account: page.props.cashier_account,
  role: window.CONSTANTS.USER_ROLES[user.role],
});

const submit = () => handleSubmit({ form, url: route("admin.profile.update") });
</script>

<template>
  <q-form
    class="row"
    @submit.prevent="submit"
    @validation-error="scrollToFirstErrorField"
  >
    <q-card square flat bordered class="col">
      <q-card-section>
        <div class="text-h6 q-my-xs text-subtitle1">Profil Saya</div>
        <p class="text-caption text-grey-9">Perbarui profil anda.</p>
        <q-input
          v-model="form.username"
          label="ID Pengguna"
          :disable="form.processing"
          readonly
          hide-bottom-space
        />
        <q-input
          v-model.trim="form.name"
          label="Nama"
          :disable="form.processing"
          :error="!!form.errors.name"
          :error-message="form.errors.name"
          :rules="[(val) => (val && val.length > 0) || 'Name harus diisi.']"
          lazy-rules
          hide-bottom-space
        />
        <q-input
          v-model="form.role"
          label="Hak Akses"
          :disable="form.processing"
          readonly
          hide-bottom-space
        />
        <q-input
          v-if="form.cashier_account"
          v-model="form.cashier_account.name"
          label="Akun Kas"
          :disable="form.processing"
          readonly
          hide-bottom-space
        />
      </q-card-section>
      <q-card-section>
        <q-btn
          type="submit"
          label="Perbarui Profil Saya"
          icon="save"
          color="primary"
          :disable="form.processing"
        />
      </q-card-section>
    </q-card>
  </q-form>
</template>
