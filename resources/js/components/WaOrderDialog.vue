<script setup>
import { ref, watch } from "vue";
import { useQuasar, copyToClipboard } from "quasar";
import { formatNumber, formatDateTime } from "@/helpers/formatter";

const props = defineProps({
  modelValue: Boolean, // Untuk kontrol buka/tutup dialog
  data: Object, // Data order pembelian
});

const emit = defineEmits(["update:modelValue"]);

const $q = useQuasar();
const waMessage = ref("");

// Fungsi generate pesan (sama seperti sebelumnya)
const generateWaMessage = () => {
  const d = props.data;
  if (!d || !d.details) return;

  const itemsReport = d.details
    .map((item) => {
      return `- ${item.quantity} ${item.product_uom} ${item.product_name}`;
    })
    .join("\n");

  let msg = `*PESANAN PEMBELIAN:*\n`;
  msg += `--------------------------\n`;
  msg += `*No:* #${d.code || "Draft"}\n`;
  msg += `*Kepada:* ${d.supplier_name || "Umum"}\n\n`;
  msg += `Daftar Pesanan:\n${itemsReport}\n\n`;

  if (d.notes) msg += `*Catatan:* ${d.notes}\n`;
  msg += `_Mohon diinformasikan ketersediaan stok dan total tagihannya. Terima kasih._`;

  waMessage.value = msg;
};

// Generate pesan setiap kali dialog dibuka
watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal) generateWaMessage();
  }
);

const copyMessage = () => {
  copyToClipboard(waMessage.value).then(() => {
    $q.notify({
      message: "Pesan disalin!",
      color: "positive",
      icon: "content_copy",
      timeout: 1000,
    });
  });
};

const sendToWa = () => {
  let phone = props.data.supplier_phone;

  if (!phone) {
    $q.notify({
      message: "Nomor WA supplier tidak ditemukan",
      color: "negative",
    });
    return;
  }

  // 1. Hapus semua karakter yang bukan angka (spasi, -, titik, +, dll)
  let cleanPhone = phone.replace(/\D/g, "");

  // 2. Standarisasi awalan angka
  // Jika diawali '08...', ubah menjadi '628...'
  if (cleanPhone.startsWith("0")) {
    cleanPhone = "62" + cleanPhone.substring(1);
  }

  // Jika user menulis '8...' tanpa 0 atau 62 di depan, asumsikan 62
  if (cleanPhone.startsWith("8")) {
    cleanPhone = "62" + cleanPhone;
  }

  // 3. Cek apakah setelah dibersihkan stringnya kosong atau terlalu pendek
  if (cleanPhone.length < 9) {
    $q.notify({ message: "Format nomor WA tidak valid", color: "warning" });
    return;
  }

  const encodedMsg = encodeURIComponent(waMessage.value);
  const waUrl = `https://wa.me/${cleanPhone}?text=${encodedMsg}`;

  window.open(waUrl, "_blank");
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    persistent
  >
    <q-card style="min-width: 350px; width: 500px">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Kirim Order via WA</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-input
          v-model="waMessage"
          type="textarea"
          outlined
          autogrow
          input-style="font-family: monospace; font-size: 12px; min-height: 200px"
        />
      </q-card-section>

      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          flat
          label="Salin"
          color="grey-8"
          icon="content_copy"
          @click="copyMessage"
        />
        <q-btn
          label="Kirim WA"
          color="positive"
          icon="send"
          :disable="!props.data.supplier_phone"
          @click="sendToWa"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
