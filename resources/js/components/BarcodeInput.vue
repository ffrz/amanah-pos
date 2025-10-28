<template>
  <q-input v-model="modelValueLocal" label="Barcode">
    <template v-slot:append>
      <q-icon
        v-if="isSupported"
        name="o_qr_code_scanner"
        class="cursor-pointer"
        color="primary"
        @click="toggleScan"
      >
        <q-tooltip v-if="!isScanning">Klik untuk mulai scan</q-tooltip>
      </q-icon>
    </template>
  </q-input>

  <q-dialog
    v-model="isScanning"
    persistent
    maximized
    transition-show="fade"
    transition-hide="fade"
  >
    <q-card
      class="bg-black text-white flex column items-center justify-center full-height"
    >
      <q-card-section class="text-center q-pb-none">
        <div class="text-h6">Arahkan Kamera ke Barcode</div>
      </q-card-section>

      <q-card-section class="flex flex-center full-width">
        <video
          ref="videoRef"
          autoplay
          playsinline
          class="video-scanner"
        ></video>
      </q-card-section>

      <q-card-actions align="center" class="q-pt-lg">
        <q-btn
          label="Batal Scan"
          color="negative"
          icon="close"
          @click="stopScan"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
  <!-- 
    <q-banner v-if="!isSupported" dense class="bg-warning text-dark q-mt-md">
      <template v-slot:avatar>
        <q-icon name="warning" color="dark" />
      </template>
      API Barcode Detector tidak didukung oleh browser Anda.
    </q-banner> -->
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useQuasar } from "quasar";

// --- Props & Emits Setup ---
interface Props {
  modelValue: string;
}
const props = defineProps<Props>();
const emit = defineEmits(["update:modelValue"]);

const $q = useQuasar();

// Sinkronisasi modelValue dengan input lokal
const modelValueLocal = ref(props.modelValue);
watch(modelValueLocal, (val) => emit("update:modelValue", val));
watch(
  () => props.modelValue,
  (val) => (modelValueLocal.value = val)
);

// --- State Reaktif ---
const videoRef = ref<HTMLVideoElement | null>(null);
const isSupported = ref(false);
const isScanning = ref(false);

// --- Variabel Global (Non-Reactive) ---
let stream: MediaStream | null = null;
let detector: BarcodeDetector | null = null;
let rafId: number | null = null;

// --- Lifecycle & Inisialisasi ---
onMounted(async () => {
  // 1. Cek Dukungan Barcode Detector API
  if ("BarcodeDetector" in window) {
    isSupported.value = true;
    try {
      // Dapatkan format yang didukung oleh browser (penting untuk Android/Chrome)
      const formats = await (
        window as any
      ).BarcodeDetector.getSupportedFormats();
      detector = new BarcodeDetector({ formats });
    } catch (err) {
      console.warn("BarcodeDetector init error:", err);
      isSupported.value = false;
    }
  }
});

onBeforeUnmount(() => stopScan());

// --- Fungsi Scanner ---

function toggleScan() {
  if (isScanning.value) stopScan();
  else startScan();
}

async function startScan() {
  if (!detector) return;
  try {
    isScanning.value = true;

    // Minta akses kamera
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: "environment" }, // Prioritaskan kamera belakang
    });

    // Tampilkan stream ke elemen <video>
    if (videoRef.value) videoRef.value.srcObject = stream;

    // Tunggu sebentar agar video stream siap sebelum memulai loop
    await new Promise((resolve) => setTimeout(resolve, 300));

    // Mulai loop pemindaian
    scanLoop();
  } catch (err: any) {
    console.error("Gagal akses kamera:", err);
    isScanning.value = false;

    $q.notify({
      type: "negative",
      message: `Gagal akses kamera: ${
        err.name === "NotAllowedError" ? "Izin ditolak" : "Error lainnya"
      }.`,
      actions: [{ icon: "close", color: "white" }],
    });
  }
}

function stopScan() {
  isScanning.value = false;
  if (rafId) cancelAnimationFrame(rafId);
  if (stream) {
    // Hentikan semua track (kamera)
    stream.getTracks().forEach((t) => t.stop());
    stream = null;
  }
}

async function scanLoop() {
  if (!videoRef.value || !detector || !isScanning.value) return;

  try {
    // Deteksi barcode dari frame video saat ini
    const barcodes = await detector.detect(videoRef.value);

    if (barcodes.length > 0) {
      const val = barcodes[0].rawValue;
      modelValueLocal.value = val; // Masukkan hasil ke q-input

      navigator.vibrate?.(100); // Feedback getar (jika didukung)

      $q.notify({
        type: "positive",
        message: `Barcode terdeteksi: ${val}`,
        timeout: 1500,
      });

      stopScan(); // Hentikan pemindaian setelah berhasil
      return;
    }
  } catch (err) {
    console.warn("Detection error:", err);
  }

  // Lanjutkan loop pada frame berikutnya
  rafId = requestAnimationFrame(scanLoop);
}
</script>

<style scoped>
/* Pastikan video memenuhi layar dalam dialog sambil menjaga aspek rasio */
.video-scanner {
  max-width: 100%;
  max-height: 80vh; /* Agar ada ruang untuk tombol */
  width: auto;
  height: auto;
  object-fit: contain;
}
</style>
