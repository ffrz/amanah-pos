<template>
  <div class="text-grey-8">{{ formatDateTime(currentDateTime) }}</div>
</template>

<script setup>
import { useClock } from "@/composables/useClock";
import { formatDateTime } from "@/helpers/formatter";

const { currentDateTime } = useClock();
</script>
