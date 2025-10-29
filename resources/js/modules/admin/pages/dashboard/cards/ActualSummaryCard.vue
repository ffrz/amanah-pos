<script setup>
import { computed } from "vue";
import { formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";
const page = usePage();
const summaryItems1 = computed(() => [
  {
    label: "Pelanggan Aktif",
    value: page.props.data.total_active_customer,
    icon: "person_add",
    color: "blue-7",
    bgColor: "#e3f2fd",
  },
  {
    label: "Saldo Wallet / Deposit",
    value: formatNumber(page.props.data.total_customer_wallet_balance),
    icon: "savings",
    color: "green-7",
    bgColor: "#e8f5e9",
  },
  {
    label: "Saldo Utang / Piutang",
    value: formatNumber(page.props.data.total_customer_balance),
    icon: "account_balance",
    color: "red-8",
    bgColor: "#ffebee",
  },
  {
    label: "Supplier Aktif",
    value: page.props.data.total_active_supplier,
    icon: "local_shipping",
    color: "indigo-7",
    bgColor: "#e8eaf6",
  },
  {
    label: "Saldo Wallet / Deposit",
    value: formatNumber(page.props.data.total_supplier_wallet_balance),
    icon: "account_balance_wallet",
    color: "cyan-7",
    bgColor: "#e0f7fa",
  },
  {
    label: "Saldo Utang / Piutang",
    value: formatNumber(page.props.data.total_supplier_balance),
    icon: "credit_card",
    color: "red-8",
    bgColor: "#ffebee",
  },
]);

const summaryItems2 = computed(() => [
  {
    label: "Total Transaksi",
    value: formatNumber(page.props.data.total_sales_count),
    icon: "shopping_basket",
    color: "purple-8",
    bgColor: "#f3e5f5",
  },
  {
    label: "Total Penjualan",
    value: formatNumber(page.props.data.total_sales),
    icon: "trending_up",
    color: "green-7",
    bgColor: "#e8f5e9",
  },
  {
    label: "Total Laba",
    value: formatNumber(page.props.data.total_sales_profit),
    icon: "attach_money",
    color: "teal-6",
    bgColor: "#e0f2f1",
  },
]);
</script>

<template>
  <div class="q-pb-sm">
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
