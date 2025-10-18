<script setup>
import UpdatePasswordForm from "./partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./partials/UpdateProfileInformationForm.vue";

const title = "Pengaturan Pribadi";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="q-pa-xs">
      <div class="row justify-center">
        <div class="col col-md-6">
          <UpdateProfileInformationForm class="q-mb-md" />
          <UpdatePasswordForm />
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
