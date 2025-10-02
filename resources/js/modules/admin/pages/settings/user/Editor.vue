<script setup>
import StandardCheckBox from "@/components/StandardCheckBox.vue";
import { handleSubmit } from "@/helpers/client-req-handler";
import { createOptions } from "@/helpers/options";
import { validateUsername } from "@/helpers/validations";
import { useForm, usePage } from "@inertiajs/vue3";

const types = createOptions(window.CONSTANTS.USER_TYPES);
const page = usePage();
const roles = page.props.roles;
const title = !!page.props.data.id ? "Edit Pengguna" : "Tambah Pengguna";

// Terapkan mapping di sini untuk memastikan form.roles selalu array of ID integer
const initialRoles = page.props.data.roles;
const roleIds = Array.isArray(initialRoles)
  ? initialRoles.map((role) => role.id)
  : [];

const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  username: page.props.data.username,
  password: "",
  type: !!page.props.data.type ? page.props.data.type : types[0].value,
  active: !!page.props.data.active,
  // Gunakan array ID yang sudah di-map di sini
  roles: roleIds,
});

const submit = () => handleSubmit({ form, url: route("admin.user.save") });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$goBack()"
        />
      </div>
    </template>
    <div class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <q-form class="row" @submit.prevent="submit">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input
                autofocus
                v-model.trim="form.username"
                type="text"
                label="Username"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.username"
                :error-message="form.errors.username"
                :rules="[
                  (val) => (val && val.length > 0) || 'Username harus diisi.',
                  (val) => validateUsername(val) || 'Username tidak valid.',
                ]"
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.name"
                label="Nama"
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                lazy-rules
                hide-bottom-space
              />
              <q-input
                v-model="form.password"
                type="password"
                label="Kata Sandi"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.password"
                :error-message="form.errors.password"
                hide-bottom-space
              />
              <q-select
                v-model="form.type"
                label="Jenis Akun"
                :options="types"
                map-options
                emit-value
                lazy-rules
                :disable="form.processing"
                transition-show="jump-up"
                transition-hide="jump-up"
                :error="!!form.errors.type"
                :error-message="form.errors.type"
                hide-bottom-space
              />
              <q-select
                v-if="form.type == 'standard_user'"
                v-model="form.roles"
                label="Peran"
                :options="roles"
                option-value="id"
                option-label="name"
                map-options
                emit-value
                multiple
                lazy-rules
                use-chips
                :disable="form.processing"
                transition-show="jump-up"
                transition-hide="jump-up"
                :error="!!form.errors['roles'] || !!form.errors['roles.0']"
                :error-message="form.errors['roles'] || form.errors['roles.0']"
                hide-bottom-space
              />
              <StandardCheckBox
                v-model="form.active"
                :disable="form.processing"
                label="Aktif"
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :disable="form.processing"
                @click="submit"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                class="text-black"
                :disable="form.processing"
                @click="$goBack()"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>
