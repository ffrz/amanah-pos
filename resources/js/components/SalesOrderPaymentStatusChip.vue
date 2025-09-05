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

const STATUSES = window.CONSTANTS.SALES_ORDER_PAYMENT_STATUSES;

const COLORS = {
  unpaid: "red",
  fully_paid: "green",
  partially_paid: "orange",
  refunded: "black",
};
</script>
