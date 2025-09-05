<template>
  <StatusChip :status="status" :statuses="STATUSES" :colors="COLORS" />
</template>

<script setup>
import StatusChip from "./StatusChip.vue";

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
});

const STATUSES = window.CONSTANTS.SALES_ORDER_STATUSES;

const COLORS = {
  draft: "orange",
  closed: "green",
  canceled: "red",
};
</script>
