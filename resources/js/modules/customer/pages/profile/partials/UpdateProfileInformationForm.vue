<script setup>
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useForm, usePage } from "@inertiajs/vue3";
import { formatNumber } from "@/helpers/formatter";

const page = usePage();
const user = page.props.data;
const form = useForm({
  name: user.name,
  username: user.username,
  phone: user.phone,
  address: user.address,
  balance: user.balance,
});

const submit = () =>
  handleSubmit({ form, url: route("customer.profile.update") });
</script>

<template>
  <q-form class="row q-mb-xs">
    <q-card square flat bordered class="col">
      <q-card-section>
        <div class="text-h6 q-my-xs text-subtitle1">Info Santri</div>
        <table class="detail">
          <tbody>
            <tr>
              <td style="width: 100px">NIS</td>
              <td style="width: 1px">:</td>
              <td>{{ user.username }}</td>
            </tr>
            <tr>
              <td>Nama</td>
              <td>:</td>
              <td>{{ user.name }}</td>
            </tr>
            <tr>
              <td>Saldo</td>
              <td>:</td>
              <td>Rp. {{ formatNumber(user.balance) }}</td>
            </tr>
          </tbody>
        </table>
      </q-card-section>
    </q-card>
  </q-form>
  <q-form
    class="row q-my-xs"
    @submit.prevent="submit"
    @validation-error="scrollToFirstErrorField"
  >
    <q-card square flat bordered class="col">
      <q-card-section>
        <div class="text-h6 text-subtitle1">Kontak</div>
        <!-- <q-input
          v-model.trim="form.parent_name"
          type="text"
          label="Nama Wali Santri"
          lazy-rules
          :disable="form.processing"
          hide-bottom-space
          :error="!!form.errors.parent_name"
          :error-message="form.errors.parent_name"
          :rules="[(val) => (val && val.length > 0) || 'Nama harus diisi.']"
        /> -->
        <q-input
          v-model.trim="form.phone"
          type="text"
          label="No HP"
          lazy-rules
          :disable="form.processing"
          hide-bottom-space
          :error="!!form.errors.phone"
          :error-message="form.errors.phone"
          :rules="[(val) => (val && val.length > 0) || 'No HP harus diisi.']"
        />
        <q-input
          v-model.trim="form.address"
          type="textarea"
          autogrow
          counter
          maxlength="500"
          label="Alamat"
          lazy-rules
          :disable="form.processing"
          hide-bottom-space
          :error="!!form.errors.address"
          :error-message="form.errors.address"
          :rules="[(val) => (val && val.length > 0) || 'Alamat harus diisi.']"
        />
      </q-card-section>
      <q-card-section>
        <q-btn
          type="submit"
          color="primary"
          label="Perbarui Profil Saya"
          :disable="form.processing"
          icon="save"
        />
      </q-card-section>
    </q-card>
  </q-form>
</template>
