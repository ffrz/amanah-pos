<script setup>
import { computed } from "vue";
import { formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";
const page = usePage();
const summaryItems1 = computed(() => [
  {
    label: "Pelanggan Aktif",
    value: page.props.data.total_active_customer,
    // Ganti Icon
    icon: "person_add",
    // Ganti Warna: Biru Muda
    color: "blue-7",
    bgColor: "#e3f2fd", // Tetap sama (Light Blue 50)
  },
  {
    label: "Saldo Wallet",
    value: formatNumber(page.props.data.total_customer_wallet_balance),
    // Ganti Icon
    icon: "savings",
    // Ganti Warna: Hijau (Simbol Saldo Positif)
    color: "green-7",
    bgColor: "#e8f5e9", // Green 50
  },
  {
    label: "Saldo Utang / Piutang",
    value: formatNumber(page.props.data.total_customer_balance),
    // Ganti Icon
    icon: "account_balance", // Lebih umum untuk saldo total
    // Ganti Warna: Merah (Simbol Kewajiban/Utang)
    color: "red-8",
    bgColor: "#ffebee", // Red 50
  },
  {
    label: "Supplier Aktif",
    value: page.props.data.total_active_supplier,
    // Ganti Icon
    icon: "local_shipping", // Ikon yang lebih spesifik untuk supplier
    // Ganti Warna: Indigo
    color: "indigo-7",
    bgColor: "#e8eaf6", // Indigo 50
  },
  {
    label: "Saldo Wallet",
    value: formatNumber(page.props.data.total_supplier_wallet_balance),
    // Ganti Icon
    icon: "account_balance_wallet", // Tetap sama untuk wallet
    // Ganti Warna: Cyan
    color: "cyan-7",
    bgColor: "#e0f7fa", // Cyan 50
  },
  {
    label: "Saldo Utang / Piutang",
    value: formatNumber(page.props.data.total_supplier_balance),
    // Ganti Icon
    icon: "credit_card", // Simbol transaksi keuangan
    // Ganti Warna: Merah (Simbol Kewajiban/Utang)
    color: "red-8",
    bgColor: "#ffebee", // Red 50
  },
]);

const summaryItems2 = computed(() => [
  {
    label: "Total Transaksi",
    value: formatNumber(page.props.data.total_sales_count),
    // Ganti Icon
    icon: "shopping_basket",
    // Ganti Warna: Ungu
    color: "purple-8",
    bgColor: "#f3e5f5", // Tetap sama (Purple 50)
  },
  {
    label: "Total Penjualan",
    value: formatNumber(page.props.data.total_sales),
    // Ganti Icon
    icon: "trending_up", // Simbol pertumbuhan penjualan
    // Ganti Warna: Hijau
    color: "green-7",
    bgColor: "#e8f5e9", // Tetap sama (Green 50)
  },
  {
    label: "Total Laba",
    value: formatNumber(page.props.data.total_sales_profit),
    // Ganti Icon
    icon: "attach_money", // Ikon yang lebih fokus ke laba
    // Ganti Warna: Teal/Cyan
    color: "teal-6",
    bgColor: "#e0f2f1", // Teal 50
  },
]);
</script>

<template>
  <div class="q-pb-sm">
    <div class="text-h6 text-bold text-grey-8 q-mb-sm">Ringkasan</div>
    <div>
      <div class="row q-col-gutter-sm">
        <div
          v-for="(item, index) in summaryItems1"
          :key="index"
          class="col-12 col-md-4"
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
    <div class="q-my-sm">
      <div class="row q-col-gutter-sm">
        <div
          v-for="(item, index) in summaryItems2"
          :key="index"
          class="col-12 col-md-4"
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
  </div>
</template>
