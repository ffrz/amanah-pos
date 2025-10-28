<template>
  <div>
    <q-input
      ref="qInputInternalRef"
      v-bind="$attrs"
      :model-value="localValue"
      @update:model-value="updateValue"
      :disable="isScanning || $attrs.disable"
      :loading="isScanning || $attrs.loading"
      :clearable="localValue && !localValue.endsWith('*')"
    >
      <template #prepend>
        <q-icon name="search" @click="emitSearch" class="cursor-pointer" />
      </template>

      <template #append>
        <q-icon
          v-if="showScanButton"
          name="o_qr_code_scanner"
          class="cursor-pointer q-mr-sm"
          color="primary"
          @click="toggleScan"
        >
          <q-tooltip>Scan Barcode</q-tooltip>
        </q-icon>

        <q-icon
          v-if="!showScanButton"
          name="send"
          @click="emitSend"
          class="cursor-pointer q-ml-md"
        />

        <q-spinner
          v-else-if="isScanning"
          color="primary"
          size="1.5em"
          class="q-mr-sm"
        />
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
import { ref, watch, onMounted, onBeforeUnmount, computed } from "vue";
import { useQuasar } from "quasar";
const qInputInternalRef = ref(null);

const focus = () => {
  if (qInputInternalRef.value) {
    qInputInternalRef.value.focus();
  }
};

defineExpose({
  focus,
  qInput: qInputInternalRef,
});

// --- Props & Emits ---
interface Props {
  modelValue: string | number | null | undefined;
}
const props = defineProps<Props>();
const emit = defineEmits([
  "update:modelValue",
  "scan-success",
  "send",
  "search",
]);

const $q = useQuasar();

// VARIABEL LOKAL UNTUK SINKRONISASI
const localValue = ref(props.modelValue);

// 1. Update nilai lokal ketika props.modelValue berubah dari luar
watch(
  () => props.modelValue,
  (val) => {
    if (val !== localValue.value) {
      localValue.value = val;
    }
  }
);

// 2. Emit perubahan ketika nilai lokal (diinput manual) berubah
const updateValue = (val: string | number | null | undefined) => {
  localValue.value = val;
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

async function emitSearch() {
  emit("search");
}

async function emitSend() {
  emit("send");
}

async function scanLoop() {
  if (!videoRef.value || !detector || !isScanning.value) return;

  try {
    const barcodes = await detector.detect(videoRef.value);

    if (barcodes.length > 0) {
      const val = barcodes[0].rawValue;

      // Update nilai lokal dan emit ke komponen induk
      localValue.value = val;
      emit("update:modelValue", val);
      emit("scan-success", val);

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

const showScanButton = computed(() => {
  if (isSupported.value && !isScanning.value && !localValue.value) return true;
  if (localValue.value.endsWith("*")) return true;
  return false;
});
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
