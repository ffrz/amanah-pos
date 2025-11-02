<script setup>
import { usePage } from "@inertiajs/vue3";
import DigitalClock from "./DigitalClock.vue";

const page = usePage();
</script>
<template>
  <div class="row items-center text-grey-8" style="line-height: 1em">
    <div class="row items-center q-pr-sm">
      <q-icon name="account_box" class="q-mr-xs" />
      {{ page.props.auth.user.username }}
    </div>
    <div
      v-if="page.props.data.cashier_session"
      class="q-px-sm row items-center left-border"
    >
      <q-icon name="point_of_sale" class="q-mr-sm" />
      {{ page.props.data.cashier_session?.cashier_terminal?.name }}
    </div>
    <div class="q-pl-sm left-border">
      <DigitalClock />
    </div>
  </div>
</template>
<style scoped>
.left-border {
  border-left: 1px solid #ccc;
}
</style>
