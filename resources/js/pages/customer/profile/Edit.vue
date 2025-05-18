<script setup>
import UpdatePasswordForm from "./partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./partials/UpdateProfileInformationForm.vue";
</script>

<template>
  <i-head :title="title" />
  <customer-layout>
    <template #title>Profil</template>
    <div class="q-pa-sm">
      <div class="row justify-center">
        <div class="col col-lg-6">
          <UpdateProfileInformationForm class="q-mb-md" />
          <UpdatePasswordForm />
        </div>
      </div>
    </div>
  </customer-layout>
</template>
