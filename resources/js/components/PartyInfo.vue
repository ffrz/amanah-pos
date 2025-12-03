<template>
  <div>
    <div class="row text-grey-9 q-pt-sm">
      <div v-if="party.phone" class="q-ml-sm">
        <q-icon name="phone" class="inline-icon" />
        {{ party.phone }}
      </div>
      <div v-if="party.phone_1" class="q-ml-sm">
        <q-icon name="phone" class="inline-icon" />
        {{ party.phone_1 }}
      </div>
      <div v-if="party.address" class="q-ml-sm text-grey-9">
        <q-icon name="home_pin" class="inline-icon" />
        {{ party.address }}
      </div>
    </div>

    <div class="row text-grey-9">
      <div
        v-if="party.wallet_balance"
        class="q-ml-sm"
        :class="balanceClass(party.wallet_balance)"
      >
        <q-icon name="wallet" class="inline-icon" />
        {{ formatCurrency(party.wallet_balance) }}
      </div>

      <div v-if="party.balance && party.balance != 0" class="q-ml-sm">
        <span v-if="party.wallet_balance">&#9679;</span>
        &nbsp;
        <span :class="balanceClass(party.balance)">
          <q-icon name="balance" class="inline-icon" />
          {{ formatCurrency(party.balance) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { formatNumber } from "@/helpers/formatter";
import { computed } from "vue";

const props = defineProps({
  party: {
    type: Object,
    required: true,
  },
  isValidWalletBalance: {
    type: Boolean,
    default: true,
  },
});

const balanceClass = (balance) => {
  if (balance === undefined || balance === null) {
    return "text-grey-9";
  }

  return balance > 0 ? "text-green" : "text-red";
};

const formatCurrency = (value) => {
  if (value === null || value === undefined) return "Rp. 0";

  const formatted = formatNumber(value);
  return `Rp. ${formatted}`;
};
</script>
