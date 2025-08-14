<template>
  <div class="text-grey-8">{{ currentDate }} - {{ currentTime }}</div>
</template>

<script setup>
import { useClock } from "@/composables/useClock";

const { currentDate, currentTime } = useClock();
</script>
