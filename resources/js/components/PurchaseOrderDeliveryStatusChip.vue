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

const STATUSES = window.CONSTANTS.PURCHASE_ORDER_DELIVERY_STATUSES;

const COLORS = {
  not_sent: "grey",
  sent: "orange",
  received: "green",
  failed: "red",
};
</script>
