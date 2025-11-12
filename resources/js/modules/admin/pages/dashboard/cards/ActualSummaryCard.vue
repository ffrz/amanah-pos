<script setup>
import { formatMoney, formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";
const page = usePage();

const summaryItems = [
  {
    title: "Penjualan",
    items: [
      {
        label: "Total Laba",
        value: formatMoney(page.props.data.total_sales_profit),
        icon: "attach_money",
        color: "teal-3",
      },
      {
        label: "Total Penjualan",
        value: formatMoney(page.props.data.total_sales),
        icon: "trending_up",
        color: "green-3",
      },
      {
        label: "Jumlah Transaksi",
        value: formatNumber(page.props.data.total_sales_count),
        icon: "shopping_cart",
        color: "purple-3",
      },
    ],
  },
  {
    title: "Pelanggan (Aktual)",
    items: [
      {
        label: "Saldo Deposit",
        value: formatMoney(page.props.data.total_customer_wallet_balance),
        icon: "wallet",
        color: "green-3",
      },
      {
        label: page.props.data.total_customer_balance < 0 ? "Piutang" : "Utang",
        value: formatMoney(Math.abs(page.props.data.total_customer_balance)),
        icon: "balance",
        color: "red-3",
        bgColor: "#ffebee",
      },
      {
        label: "Aktif",
        value: page.props.data.total_active_customer,
        icon: "person",
        color: "blue-3",
      },
    ],
  },
  {
    title: "Inventori (Aktual)",
    items: [
      {
        label: "Total Modal",
        value: formatMoney(page.props.data.total_product_cost),
        icon: "money",
        color: "orange-4",
      },
      {
        label: "Total Aset",
        value: formatMoney(page.props.data.total_product_price),
        icon: "money",
        color: "yellow-6",
      },
      {
        label: "Total Item",
        value: formatNumber(page.props.data.total_product_item),
        icon: "inventory_2",
        color: "brown-4",
      },
    ],
  },
  {
    title: "Supplier  (Aktual)",
    items: [
      {
        label: "Saldo Deposit",
        value: formatMoney(page.props.data.total_supplier_wallet_balance),
        icon: "wallet",
        color: "cyan-3",
      },
      {
        label: page.props.data.total_supplier_balance > 0 ? "Piutang" : "Utang",
        value: formatMoney(Math.abs(page.props.data.total_supplier_balance)),
        icon: "balance",
        color: "pink-3",
      },
      {
        label: "Supplier Aktif",
        value: page.props.data.total_active_supplier,
        icon: "local_shipping",
        color: "indigo-3",
      },
    ],
  },
  {
    title: "Keuangan",
    items: [
      {
        label: "Total Saldo (Aktual)",
        value: formatMoney(page.props.data.total_finance_balance),
        icon: "wallet",
        color: "orange-5",
      },
      {
        label: "Total Pemasukan",
        value: formatMoney(page.props.data.total_finance_income),
        icon: "input",
        color: "green-5",
      },
      {
        label: "Total Pengeluaran",
        value: formatMoney(page.props.data.total_finance_expense),
        icon: "output",
        color: "red-5",
      },
    ],
  },
];
</script>

<template>
  <div class="q-pb-sm" v-for="(item, i) in summaryItems" :key="i">
    <div class="text-subtitle2 text-bold q-mb-xs text-grey-7">
      {{ item.title }}
    </div>
    <div>
      <div class="row q-col-gutter-sm">
        <div v-for="(item, j) in item.items" :key="j" class="col-12 col-md-4">
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
