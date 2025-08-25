<template>
  <q-chip
    size="sm"
    dense
    square
    :color="chipColor"
    :label="chipLabel"
    text-color="white"
  />
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
  statuses: {
    type: Object,
    required: true, // mapping status → label
  },
  colors: {
    type: Object,
    default: () => ({}), // mapping status → warna
  },
});

// ✅ Warna chip
const chipColor = computed(() => {
  return props.colors[props.status] ?? "grey";
});

// ✅ Label chip
const chipLabel = computed(() => {
  return props.statuses[props.status] ?? props.status;
});
</script>
