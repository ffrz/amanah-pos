<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { useQuasar } from "quasar";

const $q = useQuasar();
const title = "Laporan";
const activeTab = ref("sales");
const isSmallScreen = computed(() => true);

const reportCategories = {
  sales: {
    label: "Penjualan",
    icon: "o_payments",
    reports: [
      {
        title: "Laporan Penjualan",
        subtitle: "Laporan penjualan rekap / rincian",
        icon: "o_receipt_long",
        route: "admin.report.sales-order.index",
      },
    ],
  },
  purchasing: {
    label: "Pembelian",
    icon: "o_shopping_cart",
    reports: [
      {
        title: "Rincian Pembelian",
        subtitle: "Daftar rincian pembelian",
        icon: "o_receipt_long",
        route: "admin.report.purchase-order.detail",
      },
    ],
  },
  inventory: {
    label: "Inventori",
    icon: "inventory",
    reports: [
      {
        title: "Produk",
        subtitle: "Daftar produk",
        icon: "inventory",
        route: "admin.report.product.index",
      },
    ],
  },
  customer: {
    label: "Pelanggan",
    icon: "o_groups",
    reports: [
      {
        title: "Daftar Pelanggan",
        subtitle: "Laporan daftar pelanggan",
        icon: "o_groups",
        route: "admin.report.customer.index",
      },
    ],
  },
  supplier: {
    label: "Supplier",
    icon: "o_groups",
    reports: [
      {
        title: "Daftar Supplier",
        subtitle: "Laporan daftar supplier",
        icon: "o_groups",
        route: "admin.report.supplier.index",
      },
    ],
  },
  finance: {
    label: "Keuangan",
    icon: "paid",
    reports: [
      {
        title: "Laporan Transaksi",
        subtitle: "Laporan Transaksi Keuangan",
        icon: "receipt",
        route: "admin.report.finance-transaction.index",
      },
      {
        title: "Laporan Akun",
        subtitle: "Laporan Akun Keuangan",
        icon: "wallet",
        route: "admin.report.finance-account.index",
      },
    ],
  },
  cost: {
    label: "Operasional",
    icon: "paid",
    reports: [
      {
        title: "Laporan Rincian Biaya Operasional",
        subtitle: "Laporan Rincian Transaksi Biaya Operasional",
        icon: "receipt",
        route: "admin.report.operational-cost-detail.index",
      },
      {
        title: "Laporan Rekapitulasi Biaya Operasional",
        subtitle: "Laporan Rekapitulasi Transaksi Biaya Operasional",
        icon: "receipt",
        route: "admin.report.operational-cost-recap.index",
      },
    ],
  },
};

const sortedReportCategories = [
  { id: "sales", ...reportCategories.sales },
  { id: "purchasing", ...reportCategories.purchasing },
  { id: "inventory", ...reportCategories.inventory },
  { id: "customer", ...reportCategories.customer },
  { id: "supplier", ...reportCategories.supplier },
  { id: "finance", ...reportCategories.finance },
  { id: "cost", ...reportCategories.cost },
];
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="q-pa-xs">
      <q-card flat bordered class="report-container full-width">
        <q-tabs
          v-model="activeTab"
          dense
          class="text-grey-7"
          active-color="primary"
          indicator-color="primary"
          inline-label
          mobile-arrows
          align="justify"
          switch-indicator
          :breakpoint="0"
        >
          <q-tab
            v-for="category in sortedReportCategories"
            :key="category.id"
            :name="category.id"
          >
            <div class="row items-center full-width">
              <q-icon
                v-if="!isSmallScreen"
                :name="category.icon"
                class="q-mr-md"
              />
              <div class="col text-left text-bold text-grey-9">
                {{ category.label }}
              </div>
              <q-badge
                v-if="category.count"
                color="grey-5"
                text-color="dark"
                rounded
                :label="category.count"
                class="q-ml-sm"
              />
            </div>
          </q-tab>
        </q-tabs>

        <q-tab-panels v-model="activeTab" animated class="q-pa-none">
          <q-tab-panel
            v-for="category in sortedReportCategories"
            :key="category.id"
            :name="category.id"
            class="q-pa-none"
          >
            <q-list separator>
              <template v-if="category.reports && category.reports.length > 0">
                <q-item
                  v-for="(report, index) in category.reports"
                  :key="index"
                  clickable
                  v-ripple
                  class="q-py-md"
                  @click.stop="router.get(route(report.route))"
                >
                  <q-item-section avatar>
                    <q-icon :name="report.icon" size="28px" color="grey" />
                  </q-item-section>

                  <q-item-section>
                    <q-item-label class="text-weight-bold text-dark">{{
                      report.title
                    }}</q-item-label>
                    <q-item-label caption>{{ report.subtitle }}</q-item-label>
                  </q-item-section>

                  <q-item-section side>
                    <q-icon name="chevron_right" color="grey" />
                  </q-item-section>
                </q-item>
              </template>

              <div v-else class="q-pa-md">
                <div class="text-h6 text-primary q-mb-md">
                  Laporan {{ category.label }}
                </div>
                <p>
                  Saat ini tidak ada daftar laporan spesifik yang ditampilkan di
                  kategori ini.
                </p>
              </div>
            </q-list>
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
    </div>
  </authenticated-layout>
</template>

<style scoped>
/* Kontainer utama yang menampung tabs dan panels */
.report-container {
  min-height: 90vh;
}
</style>
