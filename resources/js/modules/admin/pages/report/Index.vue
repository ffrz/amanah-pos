<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { useQuasar } from "quasar"; // Import useQuasar untuk deteksi breakpoint

const $q = useQuasar();
const title = "Laporan";
const activeTab = ref("sales");
const isSmallScreen = computed(() => $q.screen.lt.md);

const reportCategories = {
  sales: {
    label: "Penjualan",
    //count: 1,
    icon: "o_payments",
    reports: [
      {
        title: "Keuntungan Penjualan",
        subtitle: "Daftar penjualan berikut laba kotor",
        icon: "analytics",
        route: "admin.report.sales-order.gross-profit",
      },
      {
        title: "Pelanggan",
        subtitle: "Daftar pelanggan",
        icon: "o_groups",
        route: "admin.report.customer.index",
      },
    ],
  },
  purchasing: {
    label: "Pembelian",
    //count: 2,
    icon: "o_shopping_cart",
    reports: [
      {
        title: "Rincian Pembelian",
        subtitle: "Daftar rincian pembelian",
        icon: "o_receipt_long",
        route: "admin.report.purchase-order.detail",
      },
      {
        title: "Supplier",
        subtitle: "Daftar supplier",
        icon: "o_groups",
        route: "admin.report.supplier.index",
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
};

const sortedReportCategories = [
  { id: "sales", ...reportCategories.sales },
  { id: "purchasing", ...reportCategories.purchasing },
  { id: "inventory", ...reportCategories.inventory },
];
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <div class="q-pa-xs">
      <q-card flat bordered class="report-container full-width">
        <q-tabs
          v-model="activeTab"
          dense
          class="text-grey-7"
          align="left"
          :vertical="!isSmallScreen"
          :switch-indicator="isSmallScreen"
          :inline-label="isSmallScreen"
          :mobile-arrows="isSmallScreen"
          :breakpoint="0"
        >
          <q-tab
            v-for="category in sortedReportCategories"
            :key="category.id"
            :name="category.id"
            :class="{
              'bg-blue-2': activeTab === category.id && !isSmallScreen,
              'q-py-md': !isSmallScreen,
              'q-py-sm': isSmallScreen,
            }"
            content-class="full-width"
            style="min-height: 50px"
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
                v-if="category.count && !isSmallScreen"
                color="grey-5"
                text-color="dark"
                rounded
                :label="category.count"
                class="q-ml-sm"
              />
            </div>
          </q-tab>
        </q-tabs>

        <q-separator :vertical="!isSmallScreen" />

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
  display: flex;
  /* Tumpuk secara vertikal di layar kecil */
  flex-direction: column;
  min-height: 80vh;
}

/* Override default Quasar: Di layar besar, buat tabs dan panels berdampingan */
@media (min-width: 1024px) {
  /* breakpoint md atau lebih */
  .report-container {
    flex-direction: row; /* Berdampingan */
  }
  .report-container > .q-tabs {
    width: 300px; /* Lebar kolom kiri */
    border-right: 1px solid #e0e0e0;
  }
  .report-container > .q-tab-panels {
    flex-grow: 1; /* Ambil sisa lebar */
    width: auto;
  }
}
</style>
