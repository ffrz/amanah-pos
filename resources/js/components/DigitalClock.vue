<template>
  <div class="text-grey-8 row items-center">
    <q-icon name="calendar_clock" class="q-mr-sm" />
    {{ formatDateTime(currentDateTime) }}
  </div>
</template>

<script setup>
import { useClock } from "@/composables/useClock";
import { formatDateTime } from "@/helpers/formatter";

const { currentDateTime } = useClock();
</script>
