<script setup>
import { handleSubmit } from '@/helpers/client-req-handler';
import { scrollToFirstErrorField } from '@/helpers/utils';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.data;
const form = useForm({
  name: user.name,
  nis: user.nis,
  phone: user.phone,
  address: user.address,
});

const submit = () =>
  handleSubmit({ form, url: route('customer.profile.update') });
</script>

<template>
  <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
    <q-card square flat bordered class="col">
      <q-card-section>
        <div class="text-h6 q-my-xs text-subtitle1">Profil Saya</div>
        <p class="text-caption text-grey-9">Perbarui profil anda.</p>
        <q-input readonly v-model="form.nis" label="NIS" :disable="form.processing" />
        <q-input readonly v-model="form.name" label="Nama" :disable="form.processing" />
        <q-input v-model.trim="form.phone" type="text" label="No HP" lazy-rules :disable="form.processing"
          :error="!!form.errors.phone" :error-message="form.errors.phone" :rules="[
            (val) => (val && val.length > 0) || 'No HP harus diisi.',
          ]" />
        <q-input v-model.trim="form.address" type="textarea" autogrow counter maxlength="1000" label="Alamat" lazy-rules
          :disable="form.processing" :error="!!form.errors.address" :error-message="form.errors.address" :rules="[
            (val) => (val && val.length > 0) || 'Alamat harus diisi.',
          ]" />
      </q-card-section>
      <q-card-section>
        <q-btn type="submit" color="primary" label="Perbarui Profil Saya" :disable="form.processing" icon="save" />
      </q-card-section>
    </q-card>
  </q-form>
</template>
