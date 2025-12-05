<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

const $q = useQuasar();
const title = "Pengaturan Database & Utilitas";

// Referensi untuk input file tersembunyi
const restoreFileInput = ref(null);

// Fungsi Helper untuk Request Inertia
const submitDatabaseAction = (
  routeName,
  data = {},
  successMessage = "Operasi berhasil."
) => {
  router.post(route(routeName), data, {
    preserveScroll: true,
    onStart: () => {
      $q.loading.show({
        message: "Memproses database, mohon tunggu...",
        boxClass: "bg-grey-2 text-grey-9",
        spinnerColor: "primary",
      });
    },
    onFinish: () => {
      $q.loading.hide();
      // Reset input file jika ada
      if (restoreFileInput.value) restoreFileInput.value.value = "";
    },
    onSuccess: () => {
      $q.notify({
        type: "positive",
        message: successMessage,
        position: "top",
      });
    },
    onError: (errors) => {
      // Ambil pesan error pertama jika ada
      const msg = Object.values(errors)[0] || "Terjadi kesalahan pada server.";
      $q.notify({
        type: "negative",
        message: msg,
        position: "top",
      });
    },
  });
};

// Handler untuk Restore (Memicu klik input file)
const triggerRestore = () => {
  restoreFileInput.value.click();
};

// Handler saat file dipilih
const onRestoreFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  // Konfirmasi Ekstrem
  $q.dialog({
    title: "⚠️ KONFIRMASI PEMULIHAN DATABASE",
    message: `Anda akan memulihkan database menggunakan file: <b>${file.name}</b>.<br/><br/>Tindakan ini akan <b>MENGHAPUS SEMUA DATA SAAT INI</b> secara permanen. Apakah Anda benar-benar yakin?`,
    html: true,
    ok: {
      label: "Ya, Pulihkan Sekarang",
      color: "negative",
      push: true,
    },
    cancel: {
      label: "Batal",
      color: "dark",
      flat: true,
    },
    persistent: true,
  })
    .onOk(() => {
      const formData = new FormData();
      formData.append("file", file);
      // Sesuaikan nama route dengan backend Anda
      submitDatabaseAction(
        "admin.database-settings.restore",
        formData,
        "Database berhasil dipulihkan."
      );
    })
    .onCancel(() => {
      // Reset input agar bisa pilih file yang sama jika dibatalkan lalu dipilih lagi
      restoreFileInput.value.value = "";
    });
};

// Handler untuk Reset (Transactional & Total)
const confirmReset = (type) => {
  const isTotal = type === "total";
  const title = isTotal
    ? "⛔ RESET TOTAL (FACTORY RESET)"
    : "⚠️ RESET TRANSAKSI";
  const message = isTotal
    ? "Anda akan <b>MENGHAPUS SELURUH DATA</b> termasuk data master (Produk, User, Pelanggan). Sistem akan kembali seperti instalasi baru.<br/><br/>Ketik <b>CONFIRM</b> untuk melanjutkan."
    : "Anda akan <b>MENGHAPUS SEMUA RIWAYAT TRANSAKSI</b>. Data master tidak akan hilang, tapi stok dan laporan keuangan akan di-reset.<br/><br/>Apakah Anda yakin?";

  const dialogConfig = {
    title: title,
    message: message,
    html: true,
    ok: {
      label: "Ya, Hapus Data",
      color: "negative",
      push: true,
    },
    cancel: {
      label: "Batal",
      color: "dark",
      flat: true,
    },
    persistent: true,
  };

  // Jika Total Reset, butuh prompt input tambahan agar lebih aman
  if (isTotal) {
    dialogConfig.prompt = {
      model: "",
      isValid: (val) => val === "CONFIRM",
      type: "text",
      label: 'Ketik "CONFIRM"',
    };
  }

  $q.dialog(dialogConfig).onOk(() => {
    const routeName = isTotal
      ? "admin.database-settings.reset-all"
      : "admin.database-settings.reset-transaction";

    submitDatabaseAction(
      routeName,
      {},
      isTotal
        ? "Sistem berhasil di-reset total."
        : "Data transaksi berhasil dikosongkan."
    );
  });
};

const dbActions = [
  {
    name: "Buat Salinan Cadangan (Backup)",
    icon: "cloud_download",
    color: "positive", // Ubah jadi positive/hijau krn ini tindakan aman
    description:
      "Membuat salinan cadangan (*backup*) lengkap dari seluruh database saat ini dan mengunduh data ke perangkat lokal.",
    warning:
      "Pastikan proses backup berjalan sepenuhnya sebelum menutup halaman. Waktu pemrosesan tergantung ukuran database Anda.",
    action: () => {
      // Backup biasanya GET request untuk download, jadi window.open aman
      // Pastikan route ini mengarah ke controller yang mereturn download file
      window.location.href = route("admin.database-settings.backup");
    },
  },
  {
    name: "Pulihkan Database (Restore)",
    icon: "cloud_upload",
    color: "negative",
    description:
      "Memulihkan database ke kondisi terakhir dari file cadangan (*backup*). Semua data setelah waktu backup akan hilang permanen.",
    warning:
      "⚠️ Tindakan ini akan MENGHAPUS SEMUA DATA SAAT INI dan digantikan oleh data dari file backup. Lakukan dengan sangat hati-hati.",
    action: triggerRestore, // Panggil trigger input file
  },
  {
    name: "Reset Database Transaksional",
    icon: "data_thresholding",
    color: "negative",
    description:
      "Mengosongkan semua tabel data transaksional (penjualan, stok, pembelian, transaksi keuangan, operasional, dll.). Data master seperti pelanggan, pemasok, produk, dan akun pengguna TIDAK DIHAPUS.",
    warning:
      "⛔ Semua riwayat transaksi dan operasional akan HILANG PERMANEN. Data master aman, tetapi relasi data akan terputus.",
    action: () => confirmReset("transactional"),
  },
  {
    name: "Reset Database Total",
    icon: "delete_forever",
    color: "negative",
    description:
      "Mengosongkan semua tabel data termasuk data master (pelanggan, produk, pemasok, dll.), dan mengembalikan database ke kondisi awal instalasi. Hanya menyisakan konfigurasi sistem.",
    warning:
      "❌ Semua data (transaksi dan master) akan HILANG PERMANEN. Gunakan hanya untuk tujuan testing atau reset menyeluruh.",
    action: () => confirmReset("total"),
  },
];
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <input
      type="file"
      ref="restoreFileInput"
      accept=".posdb,.zip,.sql"
      class="hidden"
      @change="onRestoreFileChange"
    />

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
                    <q-icon :name="action.icon" size="lg" />
                  </q-item-section>
                  <q-item-section style="text-wrap: initial">
                    <q-item-label class="text-subtitle1 text-bold">{{
                      action.name
                    }}</q-item-label>
                    <q-item-label caption class="q-pb-sm">
                      <span v-html="action.description"></span>
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
                  no-caps
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </q-page>
  </authenticated-layout>
</template>
