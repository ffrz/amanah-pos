<template>
  <StatusChip
    :status="status"
    :statuses="CUSTOMER_WALLET_STATUSES"
    :colors="CUSTOMER_WALLET_COLORS"
  />
</template>

<script setup>
import StatusChip from "./StatusChip.vue";

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
});

const CUSTOMER_WALLET_STATUSES =
  window.CONSTANTS.CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES;

const CUSTOMER_WALLET_COLORS = {
  approved: "green",
  rejected: "red",
  pending: "orange",
  canceled: "black",
};
</script>
