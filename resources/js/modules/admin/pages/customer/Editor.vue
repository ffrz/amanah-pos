<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { ref } from "vue";
import CustomerForm from "@/components/CustomerForm.vue"; // Import komponen form inti

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Pelanggan";

// Simple mode state - digunakan untuk mengontrol tampilan field di CustomerForm
const simpleMode = ref(true);

// Initialize Inertia form
const form = useForm({
  id: page.props.data.id,
  code: page.props.data.code,
  name: page.props.data.name,
  default_price_type: page.props.data.default_price_type,
  phone: page.props.data.phone,
  credit_limit: page.props.data.credit_limit,
  address: page.props.data.address,
  active: !!page.props.data.active,
  password: !page.props.data.id ? "12345" : null,
  type: page.props.data.type,
});

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.customer.save", {
      id: form.id,
    }),
  });
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
          @click="router.get(route('admin.customer.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <!-- Tombol ini memicu submit form di CustomerForm melalui @submit pada template -->
      <q-btn
        class="q-ml-xs"
        type="submit"
        icon="check"
        rounded
        dense
        color="primary"
        :disable="form.processing"
        @click="submit()"
        title="Simpan"
      />
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <CustomerForm
          bordered
          :form="form"
          :simple-mode="simpleMode"
          @update:simpleMode="simpleMode = $event"
          @submit="submit"
          @validation-error="scrollToFirstErrorField"
        />
      </div>
    </q-page>
  </authenticated-layout>
</template>
