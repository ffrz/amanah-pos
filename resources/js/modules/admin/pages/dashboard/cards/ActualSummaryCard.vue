<script setup>
import { defineProps, computed } from "vue";
import { formatNumber } from "@/helpers/formatter";

const props = defineProps({
  dashboardData: {
    type: Object,
    required: true,
  },
});

const summaryItems = computed(() => [
  {
    label: "Total Pelanggan Aktif",
    value: props.dashboardData.summary.total_active_customer,
    icon: "groups",
    color: "primary",
    bgColor: "#e3f2fd",
  },
  {
    label: "Total Saldo Wallet",
    value: formatNumber(props.dashboardData.summary.total_wallet_balance),
    icon: "account_balance_wallet",
    color: "orange-9",
    bgColor: "#fff3e0",
  },
  {
    label: "Total Penjualan",
    value: formatNumber(props.dashboardData.summary.total_sales),
    icon: "shopping_cart",
    color: "green-7",
    bgColor: "#e8f5e9",
  },
  {
    label: "Total Transaksi",
    value: formatNumber(props.dashboardData.summary.total_transactions),
    icon: "swap_horiz",
    color: "purple-8",
    bgColor: "#f3e5f5",
  },
]);
</script>

<template>
  <div class="q-pb-sm">
    <div class="text-h6 text-bold text-grey-8 q-mb-sm">Ringkasan Statistik</div>
    <div class="row q-col-gutter-sm">
      <div
        v-for="(item, index) in summaryItems"
        :key="index"
        class="col-12 col-md-6"
      >
        <q-card square bordered flat class="q-pa-md" style="width: 100%">
          <div class="row items-center no-wrap">
            <q-avatar
              :icon="item.icon"
              :color="item.color"
              :text-color="item.bgColor"
            />
            <div class="q-ml-md">
              <div class="text-subtitle2 text-grey-8">
                {{ item.label }}
              </div>
              <div class="text-h6 text-weight-bold">{{ item.value }}</div>
            </div>
          </div>
        </q-card>
      </div>
    </div>
  </div>
</template>
