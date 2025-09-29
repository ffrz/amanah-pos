<script setup>
const title = "Pengaturan Database & Utilitas";

const dbActions = [
  {
    name: "Akses Log Operasi Database",
    icon: "insights",
    color: "primary",
    description:
      "Melihat riwayat operasi database untuk mendiagnosis masalah, melacak aktifitas pengguna, dan memantau *error* aplikasi. Log ini penting untuk *troubleshooting*.",
    action: () => console.log("Aksi: Lihat Log Sistem"),
  },
  {
    name: "Buat Salinan Cadangan (Backup)",
    icon: "cloud_download",
    color: "positive",
    description:
      "Membuat salinan cadangan (*backup*) lengkap dari seluruh database saat ini dan mengunduh data ke perangkat lokal.",
    warning:
      "Pastikan proses backup berjalan sepenuhnya sebelum menutup halaman. Waktu pemrosesan tergantung ukuran database Anda.",
    action: () => console.log("Aksi: Mulai Backup"),
  },
  {
    name: "Pulihkan Database (Restore)",
    icon: "cloud_upload",
    color: "negative",
    description:
      "Memulihkan database ke kondisi terakhir dari file cadangan (*backup*). Semua data setelah waktu backup akan hilang permanen.",
    warning:
      "⚠️ **Peringatan Kritis:** Tindakan ini akan **menghapus semua data saat ini** dan digantikan oleh data dari file backup. Lakukan dengan sangat hati-hati.",
    action: () => console.log("Aksi: Mulai Restore"),
  },
  {
    name: "Reset Database Transaksional",
    icon: "data_thresholding",
    color: "deep-orange-10",
    description:
      "Mengosongkan semua tabel data transaksional (penjualan, stok, pembelian, transaksi keuangan, operasional, dll.). Data master seperti pelanggan, pemasok, produk, dan akun pengguna **tidak akan dihapus**.",
    warning:
      "⛔ **Hati-hati:** Semua riwayat transaksi dan operasional akan **hilang permanen**. Data master aman, tetapi relasi data akan terputus.",
    action: () => console.log("Aksi: Mulai Reset Transaksional"),
  },
  {
    name: "Reset Database Total",
    icon: "delete_forever",
    color: "red-10",
    description:
      "Mengosongkan semua tabel data termasuk data master (pelanggan, produk, pemasok, dll.), dan mengembalikan database ke kondisi awal instalasi. Hanya menyisakan konfigurasi sistem.",
    warning:
      "❌ **Tindakan Penghapusan Data Total:** Semua data (transaksi dan master) akan **hilang permanen**. Gunakan hanya untuk tujuan testing atau reset menyeluruh.",
    action: () => console.log("Aksi: Mulai Reset Total"),
  },
];
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center q-pa-xs">
      <div class="col-xs-12 col-md-8">
        <q-card square flat bordered class="full-width">
          <q-card-section>
            <div class="text-subtitle1 text-bold text-grey-8">
              Pengaturan Database & Utilitas
            </div>
            <q-banner dense rounded class="bg-yellow-4 text-dark q-mt-sm">
              <template v-slot:avatar>
                <q-icon name="warning" color="red" />
              </template>
              <span class="text-bold">PERINGATAN!</span>
              Bagian ini berisi alat-alat sensitif yang dapat memengaruhi
              ketersediaan dan integritas data Anda. Gunakan dengan penuh
              tanggung jawab.
            </q-banner>
          </q-card-section>
          <q-separator />
          <q-card-section class="q-gutter-y-lg">
            <div
              v-for="action in dbActions"
              :key="action.name"
              class="row items-center q-col-gutter-md"
            >
              <div class="col-xs-12 col-sm-9">
                <q-item class="q-pa-none">
                  <q-item-section avatar class="q-pr-md">
                    <q-icon
                      :name="action.icon"
                      :color="action.color"
                      size="lg"
                    />
                  </q-item-section>
                  <q-item-section style="text-wrap: initial">
                    <q-item-label class="text-subtitle1 text-bold">{{
                      action.name
                    }}</q-item-label>
                    <q-item-label caption class="q-pb-sm">
                      {{ action.description }}
                    </q-item-label>
                    <q-item-label class="text-caption text-negative text-bold">
                      {{ action.warning }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </div>
              <div class="col-xs-12 col-sm-3 text-right">
                <q-btn
                  :icon="action.icon"
                  :label="action.name.split(' ')[0]"
                  :color="action.color"
                  @click="action.action"
                  class="full-width"
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </q-page>
  </authenticated-layout>
</template>
