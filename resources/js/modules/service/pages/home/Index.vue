<script setup>
import DigitalClock from "@/components/DigitalClock.vue";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

const $q = useQuasar();
const page = usePage();
const title = "Beranda";

const menuItems = {
  reports: [
    {
      group: "Penjualan",
      items: [
        {
          label: "Laporan Penjualan",
          route: "admin.report.sales-order.index",
        },
        {
          label: "Laporan Daftar Pelanggan",
          route: "admin.report.customer.index",
        },
      ],
    },
    {
      group: "Pembelian",
      items: [
        {
          label: "Laporan Pembelian",
          route: "admin.report.purchase-order.index",
        },
        {
          label: "Laporan Supplier",
          route: "admin.report.supplier.index",
        },
      ],
    },
    {
      group: "Inventori",
      items: [
        {
          label: "Laporan Daftar Produk",
          route: "admin.report.product.index",
        },
        {
          label: "Laporan Daftar Stok",
          route: "admin.report.product-low-stock.index",
        },
      ],
    },

    {
      group: "Keuangan",
      items: [
        {
          label: "Laporan Transaksi",
          route: "admin.report.index",
        },
        {
          label: "Laporan Akun",
          route: "admin.report.index",
        },
      ],
    },

    {
      group: "Operasional",
      items: [
        {
          label: "Laporan Transaksi Biaya Operasional",
          route: "admin.report.operational-cost.index",
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

    <template #right-button>
      <DigitalClock v-if="$q.screen.gt.sm" />
    </template>

    <q-page class="fit animated-background-container">
      <div class="q-pa-sm">
        <div class="text-h6 q-my-md text-center">
          Selamat datang, {{ page.props.auth.user.name }}.
          <q-icon name="waving_hand" class="waving-hand" />
        </div>
      </div>
    </q-page>
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

/* --- CSS BARU UNTUK BACKGROUND ANIMASI --- */

/* Keyframes untuk menggerakkan posisi gradien */
@keyframes gradient-animation {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.animated-background-container {
  /* Tinggi background yang lebih besar dari kontainer agar ada yang bergerak */
  background-size: 400% 400%;

  /* Gunakan warna pastel yang sudah kita tentukan */
  background-image: linear-gradient(
    270deg,
    rgba(255, 0, 200, 0.05),
    rgba(76, 0, 255, 0.05),
    rgba(0, 241, 241, 0.05)
  );

  /* Terapkan animasi secara perlahan dan berulang */
  animation: gradient-animation 5s ease infinite;

  /* Pastikan konten di atasnya tetap terlihat */
  /* Jika Quasar tidak mendefinisikan variabel CSS, gunakan warna hex/rgb */
  /* Contoh warna hex/rgb yang bisa Anda gunakan: */
  /* background-image: linear-gradient(
        270deg,
        #B39DDB,  // deep-purple-2
        #81D4FA,  // light-blue-2
        #81C784,  // green-3
        #FFB74D   // orange-3
    );
    */
}

/* Perlu diperhatikan: Jika Anda ingin latar belakang hanya di area konten */
/* dan bukan seluruh viewport, pastikan parent div memiliki tinggi yang cukup */

/* Catatan: q-card harus memiliki background white agar tetap menonjol */
</style>
