<script setup>
import ProductBrandForm from "@/components/ProductBrandForm.vue";
import { handleSubmit } from "@/helpers/client-req-handler";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

const $q = useQuasar();
const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Merk";

const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  active: !!page.props.data.active,
});

const submit = () =>
  handleSubmit({ form, url: route("admin.product-brand.save") });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="!$q.screen.lt.md">
    <template #title>{{ title }}</template>

    <template #left-button v-if="$q.screen.lt.md">
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.product-brand.index'))"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <ProductBrandForm :form="form" @submit="submit" />
      </div>
    </q-page>
  </authenticated-layout>
</template>
