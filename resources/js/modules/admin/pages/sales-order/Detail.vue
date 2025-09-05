<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const data = page.props.data;
const title = `Rincian ${data.formatted_id}`;
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
          @click="$inertia.get(route('admin.sales-order.index'))"
        />
      </div>
    </template>
  </authenticated-layout>
</template>
