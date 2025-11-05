<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { ref } from "vue";
import SupplierForm from "@/components/SupplierForm.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Pemasok";
const simpleMode = ref(true);

const form = useForm({
  id: page.props.data.id,
  code: page.props.data.code,
  name: page.props.data.name,
  phone_1: page.props.data.phone_1,
  phone_2: page.props.data.phone_2,
  phone_3: page.props.data.phone_3,
  url_1: page.props.data.url_1,
  url_2: page.props.data.url_2,
  bank_account_name_1: page.props.data.bank_account_name_1,
  bank_account_holder_1: page.props.data.bank_account_holder_1,
  bank_account_number_1: page.props.data.bank_account_number_1,
  bank_account_name_2: page.props.data.bank_account_name_2,
  bank_account_holder_2: page.props.data.bank_account_holder_2,
  bank_account_number_2: page.props.data.bank_account_number_2,
  address: page.props.data.address,
  return_address: page.props.data.return_address,
  active: !!page.props.data.active,
});

const submit = () => handleSubmit({ form, url: route("admin.supplier.save") });
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
          @click="router.get(route('admin.supplier.index'))"
        />
      </div>
    </template>
    <template #right-button>
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
        <SupplierForm
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
