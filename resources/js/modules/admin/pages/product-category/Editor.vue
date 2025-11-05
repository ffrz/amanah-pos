<script setup>
import ProductCategoryForm from "@/components/ProductCategoryForm.vue";
import { handleSubmit } from "@/helpers/client-req-handler";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

const $q = useQuasar();
const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Kategori Produk";

const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  description: page.props.data.description,
});

const submit = () =>
  handleSubmit({ form, url: route("admin.product-category.save") });
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
          @click="router.get(route('admin.product-category.index'))"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <ProductCategoryForm :form="form" @submit="submit" />
      </div>
    </q-page>
  </authenticated-layout>
</template>
