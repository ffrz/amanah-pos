<script setup>
import UpdatePasswordForm from "./partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./partials/UpdateProfileInformationForm.vue";
</script>

<template>
  <i-head title="Edit Profil" />
  <customer-layout>
    <template #title>Profil Wali Santri</template>
    <div class="q-pa-sm">
      <div class="row justify-center">
        <div class="col col-lg-6">
          <UpdateProfileInformationForm />
          <UpdatePasswordForm />
        </div>
      </div>
    </div>
  </customer-layout>
</template>
