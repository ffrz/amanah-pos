<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import PasswordInput from "@/components/PasswordInput.vue";

const $q = useQuasar();
const title = "Pengaturan Database & Utilitas";
const restoreFileInput = ref(null);

const dialogState = ref({
  show: false,
  title: "",
  message: "",
  isTotalReset: false,
  isRestore: false,
  password: "",
  confirmText: "",
  file: null,
  routeName: "",
});

/**
 * Fungsi Helper untuk Request AJAX menggunakan Axios
 * Menangani respons JsonResponseHelper secara konsisten.
 */
const submitDatabaseAction = async (routeName, data, successMessage) => {
  let url = route(routeName);

  // Identifikasi apakah aksi ini adalah Total Reset
  const isTotalResetAction = routeName === "admin.database-settings.reset-all";

  try {
    $q.loading.show({
      message: "Memproses database, mohon tunggu...",
      boxClass: "bg-grey-2 text-grey-9",
      spinnerColor: "primary",
    });

    // Menggunakan window.axios untuk memastikan ketersediaan global
    const response = await window.axios.post(url, data, {
      // Axios secara otomatis menangani header CSRF
      headers: {
        Accept: "application/json",
        // Content-Type otomatis diatur (multipart/form-data untuk FormData, application/json untuk objek)
      },
    });

    // Axios hanya masuk ke sini jika status respons 2xx
    const result = response.data;

    // Cek status dari JsonResponseHelper
    if (result.status === "success") {
      // --- LOGIKA BARU: UI LOCK DAN LOGOUT PAKSA SETELAH RESET TOTAL ---
      if (isTotalResetAction) {
        // Sembunyikan loading
        $q.loading.hide();

        $q.dialog({
          title: "Sistem Reset Selesai",
          message: `<span class="text-positive text-bold">Reset total berhasil.</span><br/>Database telah dikembalikan ke pengaturan pabrik. Anda akan segera di-logout untuk login ulang.`,
          html: true,
          persistent: true,
          ok: false,
          cancel: false,
        });

        // Paksa logout dengan full page reload
        // Beri waktu 3 detik agar notifikasi terbaca
        setTimeout(() => {
          window.location.href = route("admin.auth.logout");
        }, 3000);
      } else {
        // Untuk Restore dan Reset Transaksional, cukup soft reload
        router.reload({
          preserveScroll: true,
          onSuccess: () => {
            // Tampilkan pesan dari backend atau pesan default
            $q.notify({
              type: "positive",
              message: result.message || successMessage,
              position: "top",
            });
          },
        });
      }
    } else {
      // Jika status 2xx tapi helper menandakan error (kasus sangat jarang)
      $q.notify({
        type: "negative",
        message: result.message || "Operasi gagal dengan status tak terduga.",
        position: "top",
      });
    }
  } catch (error) {
    // Axios menangkap semua status non-2xx di sini (422, 500, dll.)
    const responseData = error.response?.data;
    let errorMessage = "Gagal berkomunikasi dengan server.";

    if (responseData) {
      // Ambil pesan utama dari JsonResponseHelper
      errorMessage = responseData.message || errorMessage;

      // Periksa error validasi (status 422)
      if (error.response?.status === 422 && responseData.errors) {
        // Ambil error password jika ada, atau error validasi pertama lainnya
        errorMessage = responseData.errors.password
          ? responseData.errors.password[0]
          : Object.values(responseData.errors)[0][0];
      }
    }

    $q.notify({
      type: "negative",
      message: errorMessage,
      position: "top",
    });

    $q.loading.hide();
  } finally {
    // Loading hanya disembunyikan di sini jika bukan Total Reset,
    // karena untuk Total Reset, dialog yang akan menahan layar.
    if (!isTotalResetAction) {
      $q.loading.hide();
    }

    // Reset input file agar bisa pilih file yang sama lagi
    if (restoreFileInput.value) restoreFileInput.value.value = "";
  }
};

// --- LOGIKA UTAMA (Satu Dialog) ---

// Fungsi untuk membuka dialog konfirmasi kustom
const openConfirmationDialog = (actionType, file = null) => {
  // Reset state dialog
  dialogState.value = {
    show: true,
    password: "",
    confirmText: "",
    file: file,
    isTotalReset: actionType === "total",
    isRestore: actionType === "restore",
    routeName: "",
    successMsg: "",
    title: "",
    message: "",
  };

  // Set properti berdasarkan tipe aksi
  if (actionType === "restore") {
    dialogState.value.title = "⚠️ KONFIRMASI PEMULIHAN DATABASE";
    dialogState.value.message = `Anda akan memulihkan database menggunakan file: <b>${file.name}</b>. Tindakan ini akan <b>MENGHAPUS SEMUA DATA SAAT INI</b> secara permanen. Masukkan password Anda untuk konfirmasi.`;
    dialogState.value.routeName = "admin.database-settings.restore";
    dialogState.value.successMsg = "Database berhasil dipulihkan.";
  } else if (actionType === "transactional") {
    dialogState.value.title = "⚠️ RESET TRANSAKSI";
    dialogState.value.message =
      "Anda akan <b>MENGHAPUS SEMUA RIWAYAT TRANSAKSI</b>. Data master aman. Masukkan password Anda untuk konfirmasi.";
    dialogState.value.routeName = "admin.database-settings.reset-transaction";
    dialogState.value.successMsg = "Data transaksi berhasil dikosongkan.";
  } else if (actionType === "total") {
    dialogState.value.title = "⛔ RESET TOTAL (FACTORY RESET)";
    dialogState.value.message =
      "Anda akan <b>MENGHAPUS SELURUH DATA</b> termasuk data master. Ketik <b>CONFIRM</b> dan masukkan password Anda untuk melanjutkan.";
    dialogState.value.routeName = "admin.database-settings.reset-all";
    dialogState.value.successMsg = "Sistem berhasil di-reset total.";
  }
};

// Fungsi handler saat tombol di dialog diklik
const handleDialogSubmit = () => {
  // Validasi dasar form dilakukan oleh QForm, kita hanya perlu validasi logika

  let dataToSend;

  if (dialogState.value.isRestore) {
    // Untuk Restore: menggunakan FormData
    dataToSend = new FormData();
    dataToSend.append("file", dialogState.value.file);
    dataToSend.append("password", dialogState.value.password);
  } else {
    // Untuk Reset: menggunakan objek biasa (JSON)
    dataToSend = {
      password: dialogState.value.password,
    };
    // Tambahkan confirm_text jika Total Reset
    if (dialogState.value.isTotalReset) {
      dataToSend.confirm_text = dialogState.value.confirmText;
    }
  }

  // Tutup dialog sebelum kirim
  dialogState.value.show = false;

  submitDatabaseAction(
    dialogState.value.routeName,
    dataToSend,
    dialogState.value.successMsg
  );
};

// Handler untuk Restore (Memicu klik input file)
const triggerRestore = () => {
  restoreFileInput.value.click();
};

// Handler saat file dipilih (Memanggil dialog baru)
const onRestoreFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) {
    // Reset input agar bisa pilih file yang sama
    if (restoreFileInput.value) restoreFileInput.value.value = "";
    return;
  }
  openConfirmationDialog("restore", file);
};

// Handler untuk Reset (Memanggil dialog baru)
const confirmReset = (type) => {
  openConfirmationDialog(type);
};

// --- Konfigurasi Tindakan ---

const dbActions = [
  {
    name: "Buat Salinan Cadangan (Backup)",
    icon: "cloud_download",
    color: "positive",
    description:
      "Membuat salinan cadangan (*backup*) lengkap dari seluruh database saat ini dan mengunduh data ke perangkat lokal.",
    warning:
      "Pastikan proses backup berjalan sepenuhnya sebelum menutup halaman. Waktu pemrosesan tergantung ukuran database Anda.",
    action: () => {
      // Backup tidak membutuhkan password karena tidak mengubah database, dan langsung memicu download.
      // Kita tetap menggunakan window.location.href karena ini adalah file download.
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
      "⚠️ Tindakan ini akan MENGHAPUS SEMUA DATA SAAT INI dan digantikan oleh data dari file backup. Lakukan dengan sangat hati-hati. **Membutuhkan konfirmasi password.**",
    action: triggerRestore,
  },
  {
    name: "Reset Database Transaksional",
    icon: "data_thresholding",
    color: "negative",
    description:
      "Mengosongkan semua tabel data transaksional (penjualan, stok, pembelian, transaksi keuangan, operasional, dll.). Data master aman.",
    warning:
      "⛔ Semua riwayat transaksi dan operasional akan HILANG PERMANEN. **Membutuhkan konfirmasi password.**",
    action: () => confirmReset("transactional"),
  },
  {
    name: "Reset Database Total",
    icon: "delete_forever",
    color: "negative",
    description:
      "Mengosongkan semua tabel data termasuk data master, dan mengembalikan database ke kondisi awal instalasi (Factory Reset).",
    warning:
      "❌ Semua data (transaksi dan master) akan HILANG PERMANEN. Gunakan hanya untuk tujuan testing atau reset menyeluruh. **Membutuhkan konfirmasi 'CONFIRM' dan password.**",
    action: () => confirmReset("total"),
  },
];
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <!-- Input file tersembunyi untuk Restore -->
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

    <!-- Single Custom Confirmation Dialog (BARU, Lebih Sederhana) -->
    <q-dialog v-model="dialogState.show" persistent>
      <q-card style="width: 400px">
        <q-card-section class="bg-negative text-white">
          <div class="text-h6">{{ dialogState.title }}</div>
        </q-card-section>

        <q-form @submit.prevent="handleDialogSubmit">
          <q-card-section class="q-pt-md q-pb-none">
            <div class="text-caption" v-html="dialogState.message"></div>
          </q-card-section>

          <q-card-section>
            <!-- Input KONFIRMASI (Hanya untuk Total Reset) -->
            <q-input
              v-if="dialogState.isTotalReset"
              v-model="dialogState.confirmText"
              label="Ketik KONFIRMASI di sini"
              :rules="[
                (val) => val === 'KONFIRMASI' || 'Harus mengetik KONFIRMASI',
              ]"
              class="q-mb-md"
              autocomplete="off"
              autofocus
            />

            <!-- Input Password (Wajib untuk semua aksi sensitif) -->
            <PasswordInput
              v-model="dialogState.password"
              label="Masukkan Password"
              :rules="[
                (val) => (val && val.length > 0) || 'Password wajib diisi',
              ]"
              autofocus
            />
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Batal" color="dark" v-close-popup />
            <q-btn
              type="submit"
              :label="dialogState.isRestore ? 'Pulihkan' : 'Konfirmasi'"
              color="negative"
            />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </authenticated-layout>
</template>
