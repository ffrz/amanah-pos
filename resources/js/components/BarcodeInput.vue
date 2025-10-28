<template>
  <div>
    <q-input
      v-bind="$attrs"
      :model-value="modelValue"
      @update:model-value="updateValue"
      :disable="isScanning || $attrs.disable"
      :loading="isScanning || $attrs.loading"
      clearable
    >
      <template v-for="(_, name) in $slots" #[name]="slotData">
        <slot v-if="name !== 'append'" :name="name" v-bind="slotData" />
      </template>

      <template #append>
        <q-icon
          v-if="isSupported && !isScanning"
          name="o_qr_code_scanner"
          class="cursor-pointer q-mr-sm"
          color="primary"
          @click="toggleScan"
        >
          <q-tooltip>Scan Barcode</q-tooltip>
        </q-icon>

        <q-spinner
          v-else-if="isScanning"
          color="primary"
          size="1.5em"
          class="q-mr-sm"
        />

        <slot name="append" />
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

        <q-card-section class="flex flex-center full-width q-pa-none">
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
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useQuasar } from "quasar";

// --- Props & Emits ---
// $attrs digunakan untuk menerima semua props Q-Input lainnya
interface Props {
  modelValue: string | number | null | undefined;
}
const props = defineProps<Props>();
// Tambahkan event 'scan-success' agar komponen induk tahu kapan scan selesai
const emit = defineEmits(["update:modelValue", "scan-success"]);

const $q = useQuasar();

// Sinkronisasi modelValue dan emit update
const updateValue = (val: string | number | null | undefined) => {
  emit("update:modelValue", val);
};

// --- Logika Scanner ---
const videoRef = ref<HTMLVideoElement | null>(null);
const isSupported = ref(false);
const isScanning = ref(false);
let stream: MediaStream | null = null;
let detector: BarcodeDetector | null = null;
let rafId: number | null = null;

onMounted(async () => {
  // Cek Dukungan Barcode Detector API
  if ("BarcodeDetector" in window) {
    isSupported.value = true;
    try {
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

function toggleScan() {
  if (!isSupported.value) {
    $q.notify({
      type: "warning",
      message: "Barcode Detector API tidak didukung browser ini.",
    });
    return;
  }
  if (isScanning.value) stopScan();
  else startScan();
}

async function startScan() {
  if (!detector) return;
  try {
    isScanning.value = true;
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: "environment" },
    });
    if (videoRef.value) videoRef.value.srcObject = stream;
    await new Promise((resolve) => setTimeout(resolve, 300));
    scanLoop();
  } catch (err: any) {
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
    stream.getTracks().forEach((t) => t.stop());
    stream = null;
  }
}

async function scanLoop() {
  if (!videoRef.value || !detector || !isScanning.value) return;

  try {
    const barcodes = await detector.detect(videoRef.value);

    if (barcodes.length > 0) {
      const val = barcodes[0].rawValue;
      updateValue(val); // Masukkan hasil ke q-input (v-model)
      emit("scan-success", val); // Kirim event success

      navigator.vibrate?.(100);
      $q.notify({
        type: "positive",
        message: `Barcode terdeteksi: ${val}`,
        timeout: 1000,
      });

      stopScan();
      return;
    }
  } catch (err) {
    console.warn("Detection error:", err);
  }

  rafId = requestAnimationFrame(scanLoop);
}
</script>

<style scoped>
.video-scanner {
  max-width: 100%;
  max-height: 80vh;
  width: auto;
  height: auto;
  object-fit: contain;
}
</style>
