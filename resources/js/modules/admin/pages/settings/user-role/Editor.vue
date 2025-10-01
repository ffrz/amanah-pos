<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { computed } from "vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Peran Pengguna";
const form = useForm({
  id: page.props.data.id ?? null,
  name: page.props.data.name,
  description: page.props.data.description,
  // Perbaikan: Pastikan permissions adalah array ID, bukan array objek
  permissions: page.props.data.permissions
    ? page.props.data.permissions.map((p) => p.id)
    : [],
});

const submit = () => handleSubmit({ form, url: route("admin.user-role.save") });

const groupedPermissions = computed(() => {
  const groups = {};
  if (page.props.permissions) {
    page.props.permissions.forEach((permission) => {
      const category = permission.category;

      if (!groups[category]) {
        groups[category] = [];
      }
      groups[category].push(permission);
    });
  }
  return groups;
});
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
    <template #right-button>
      <q-btn
        icon="save"
        label="Simpan"
        color="primary"
        :disable="form.processing"
        size="sm"
        dense
        class="custom-dense"
        @click.prevent="submit"
      />
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input
                autofocus
                v-model.trim="form.name"
                label="Nama Role"
                lazy-rules
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.description"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Deskripsi"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.description"
                :error-message="form.errors.description"
                hide-bottom-space
              />
              <div
                v-for="(permissions, category) in groupedPermissions"
                :key="category"
              >
                <div
                  class="text-subtitle2 text-bold text-grey-8 q-mt-md q-mb-sm"
                >
                  {{ category }}
                </div>
                <div v-for="permission in permissions" :key="permission.id">
                  <q-checkbox
                    class="q-ma-none q-pa-none list-checkbox"
                    v-model="form.permissions"
                    :val="permission.id"
                    :label="permission.label"
                    color="primary"
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<style scoped>
.list-checkbox {
  margin-left: -10px !;
  margin-top: -12px;
}
</style>
