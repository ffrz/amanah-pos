<script setup>
import { router, usePage } from "@inertiajs/vue3";
const page = usePage();
const title = "Beranda";

const menuItems = {
  reports: [
    {
      group: "Pembelian",
      items: [
        {
          label: "Laporan Pembelian 1",
          route: "admin.report.index",
        },
        {
          label: "Laporan Pembelian 2",
          route: "admin.report.index",
        },
      ],
    },
    {
      group: "Penjualan",
      items: [
        {
          label: "Laporan Penjualan 1",
          route: "admin.report.index",
        },
        {
          label: "Laporan Penjualan 2",
          route: "admin.report.index",
        },
      ],
    },
    {
      group: "Keuangan",
      items: [
        {
          label: "Laporan Keuangan 1",
          route: "admin.report.index",
        },
        {
          label: "Laporan Keuangan 2",
          route: "admin.report.index",
        },
      ],
    },
  ],
};
</script>
<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <template #right-button> </template>

    <div class="q-pa-sm">
      <div class="text-h6 q-mb-lg">
        Selamat datang, {{ page.props.auth.user.name }}.
        <q-icon name="waving_hand" class="waving-hand" />
      </div>

      <div class="row q-col-gutter-md q-mb-md">
        <div class="col-xs-12 col-md-4">
          <q-card class="full-height full-width" flat>
            <q-toolbar class="bg-deep-purple-2 text-grey-10">
              <q-toolbar-title
                ><q-icon
                  name="local_shipping"
                  class="q-mr-md"
                />Pembelian</q-toolbar-title
              >
            </q-toolbar>
            <q-list>
              <q-item
                clickable
                v-ripple
                @click="router.get(route('admin.purchase-order.index'))"
              >
                <q-item-section>Pembelian</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
              <q-separator />
              <q-item
                clickable
                v-ripple
                @click="router.get(route('admin.supplier.index'))"
              >
                <q-item-section>Supplier</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>

        <div class="col-xs-12 col-md-4">
          <q-card class="full-height full-width" flat>
            <q-toolbar class="bg-light-blue-2 text-grey-10">
              <q-toolbar-title
                ><q-icon
                  name="inventory_2"
                  class="q-mr-md"
                />Inventori</q-toolbar-title
              >
            </q-toolbar>
            <q-list>
              <q-item
                clickable
                v-ripple
                @click="router.get(route('admin.product.index'))"
              >
                <q-item-section>Produk</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
              <q-separator />
              <q-item
                clickable
                v-ripple
                @click="router.get(route('admin.stock-adjustment.index'))"
              >
                <q-item-section>Penyesuaian Stok</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
              <q-item
                clickable
                v-ripple
                @click="router.get(route('admin.stock-movement.index'))"
              >
                <q-item-section>Riwayat Stok</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>

        <div class="col-xs-12 col-md-4">
          <q-card class="full-height full-width" flat>
            <q-toolbar class="bg-green-3 text-grey-10">
              <q-toolbar-title>
                <q-icon
                  name="storefront"
                  class="q-mr-md"
                />Penjualan</q-toolbar-title
              >
            </q-toolbar>
            <q-list>
              <q-item
                clickable
                v-ripple
                @click="router.get(route('admin.sales-order.index'))"
              >
                <q-item-section>Penjualan</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
              <q-separator />
              <q-item
                clickable
                v-ripple
                @click="
                  router.get(
                    route(
                      'admin.customer-wallet-transaction-confirmation.index'
                    )
                  )
                "
              >
                <q-item-section>Konfirmasi Topup Wallet</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
              <q-item
                clickable
                v-ripple
                @click="
                  router.get(route('admin.customer-wallet-transaction.index'))
                "
              >
                <q-item-section>Transaksi Wallet</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
              <q-separator />
              <q-item
                clickable
                v-ripple
                @click="router.get(route('admin.customer.index'))"
              >
                <q-item-section>Pelanggan</q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>
      </div>

      <q-card class="bg-orange-3 text-grey-10 full-width" flat>
        <q-toolbar>
          <q-toolbar-title>
            <q-icon name="analytics" class="q-mr-sm" />
            Laporan</q-toolbar-title
          >
        </q-toolbar>
      </q-card>
      <q-card flat class="q-pa-md bg-grey-1 full-width">
        <div class="row">
          <div
            v-for="group in menuItems.reports"
            :key="group.group"
            class="col-xs-6 col-sm-4 q-mb-md"
          >
            <div class="text-subtitle1 text-bold text-grey-9 q-mb-xs q-ml-md">
              {{ group.group }}
            </div>
            <q-list>
              <q-item
                v-for="item in group.items"
                :key="item"
                clickable
                v-ripple
                @click="router.get(route(item.route))"
              >
                <q-item-section>{{ item.label }} </q-item-section>
                <q-item-section side
                  ><q-icon name="chevron_right"
                /></q-item-section>
              </q-item>
            </q-list>
          </div>
        </div>
      </q-card>
    </div>
  </authenticated-layout>
</template>
<style scoped>
@keyframes wave {
  0% {
    transform: rotate(0deg);
  }
  10% {
    transform: rotate(14deg);
  } /* Melambai ke kanan */
  20% {
    transform: rotate(-8deg);
  } /* Kembali ke kiri */
  30% {
    transform: rotate(14deg);
  }
  40% {
    transform: rotate(-4deg);
  }
  50% {
    transform: rotate(10deg);
  }
  60% {
    transform: rotate(0deg);
  } /* Selesai melambai */
  100% {
    transform: rotate(0deg);
  }
}

.waving-hand {
  display: inline-block;
  transform-origin: 70% 70%;
  animation-name: wave;
  animation-duration: 5s;
  animation-iteration-count: infinite;
  animation-timing-function: ease-in-out;
}
</style>
