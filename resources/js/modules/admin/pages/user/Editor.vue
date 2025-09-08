<script setup>
import StandardCheckBox from "@/components/StandardCheckBox.vue";
import { handleSubmit } from "@/helpers/client-req-handler";
import { createOptions } from "@/helpers/options";
import { validateUsername } from "@/helpers/validations";
import { router, useForm, usePage } from "@inertiajs/vue3";

const roles = createOptions(window.CONSTANTS.USER_ROLES);
const page = usePage();
const title = !!page.props.data.id ? "Edit Pengguna" : "Tambah Pengguna";
const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  username: page.props.data.username,
  password: "",
  role: !!page.props.data.role ? page.props.data.role : roles[0].value,
  active: !!page.props.data.active,
});

const submit = () => handleSubmit({ form, url: route("admin.user.save") });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.user.index'))"
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
                v-model.trim="form.name"
                label="Nama"
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                autofocus
                lazy-rules
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.username"
                type="text"
                label="ID Pengguna"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.username"
                :error-message="form.errors.username"
                :rules="[
                  (val) =>
                    (val && val.length > 0) || 'ID Pengguna harus diisi.',
                  (val) => validateUsername(val) || 'ID Pengguna tidak valid.',
                ]"
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
                v-model="form.role"
                label="Hak Akses"
                :options="roles"
                map-options
                emit-value
                lazy-rules
                :disable="form.processing"
                transition-show="jump-up"
                transition-hide="jump-up"
                :error="!!form.errors.role"
                :error-message="form.errors.role"
                hide-bottom-space
              >
              </q-select>
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
                @click="router.get(route('admin.user.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>
