<script setup>
import UpdatePasswordForm from "./partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./partials/UpdateProfileInformationForm.vue";
</script>

<template>
  <i-head title="Edit Profil" />
  <authenticated-layout>
    <template #title>Profil</template>
    <div class="q-pa-xs">
      <div class="row justify-center">
        <div class="col col-lg-6">
          <UpdateProfileInformationForm />
          <UpdatePasswordForm />
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
