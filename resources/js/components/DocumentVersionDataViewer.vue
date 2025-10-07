<script setup>
import { computed } from "vue";
import { useQuasar } from "quasar";
import SupplierDetail from "./document/SupplierDetail.vue";

const props = defineProps({
  // Objek versi yang dipilih (harus mengandung field 'data', 'version', dan 'document_type')
  selectedVersion: {
    type: Object,
    default: null,
  },
  // Status kontrol dialog
  showDialog: {
    type: Boolean,
    required: true,
  },
});

const $q = useQuasar();
const emit = defineEmits(["update:showDialog"]);

const dialogVisible = computed({
  get: () => props.showDialog,
  set: (val) => emit("update:showDialog", val),
});

// Computed property untuk data yang sudah di-parse
const parsedData = computed(() => {
  if (props.selectedVersion && props.selectedVersion.data) {
    try {
      // Pastikan data diparse jika masih berupa JSON string
      return typeof props.selectedVersion.data === "string"
        ? JSON.parse(props.selectedVersion.data)
        : props.selectedVersion.data;
    } catch (e) {
      console.error("Error parsing JSON data:", e);
      return { error: "Data JSON tidak valid." };
    }
  }
  return {};
});

// Computed property untuk menentukan jenis dokumen
const docType = computed(() => props.selectedVersion?.document_type);

// Raw JSON fallback untuk tampilan yang tidak dikenali
const rawJson = computed(() => JSON.stringify(parsedData.value, null, 2));
</script>

<template>
  <q-dialog
    v-model="dialogVisible"
    transition-show="scale"
    transition-hide="scale"
    :maximized="$q.screen.lt.md"
  >
    <q-card style="width: 600px; max-width: 95vw">
      <q-card-section class="row items-center q-pb-md bg-grey-4">
        <div class="text-subtitle1">
          <q-icon name="history" class="q-mr-sm" />
          Detail Versi V.{{ selectedVersion?.version || "N/A" }}
        </div>
        <q-space />
        <q-btn
          icon="close"
          flat
          round
          dense
          @click="dialogVisible = false"
          color="grey"
        />
      </q-card-section>

      <q-card-section class="q-pt-md scroll" style="max-height: 75vh">
        <div class="text-subtitle2 text-grey-9 q-mb-md">
          <q-icon name="description" class="q-mr-xs" />
          Catatan Perubahan:
          <span class="text-weight-medium">{{
            selectedVersion?.changelog || "Tidak ada catatan perubahan."
          }}</span>
        </div>

        <div v-if="docType === 'App\\Models\\Supplier'">
          <div class="text-subtitle2 text-grey-8 q-mb-sm">Info Pemasok</div>
          <SupplierDetail :data="parsedData" />
        </div>
        <div v-else-if="docType === 'App\\Models\\Customer'">
          <div class="text-subtitle2 text-grey-8 q-mb-sm">Info Pelanggan</div>
          <SupplierDetail :data="parsedData" />
        </div>

        <div v-else>
          <div class="text-subtitle1 text-grey-6 q-mb-sm">
            Tipe Dokumen ({{ docType || "TIDAK DIKETAHUI" }}) Tidak Memiliki
            Tampilan Kustom.
          </div>
          <div class="text-caption text-grey-7 q-mb-xs">Tampilan Raw JSON:</div>
          <pre
            class="bg-white q-pa-sm rounded-borders overflow-auto text-sm border-2 border-dashed border-grey-4"
            style="white-space: pre-wrap; word-wrap: break-word"
            >{{ rawJson }}</pre
          >
        </div>
      </q-card-section>

      <q-card-actions align="right" class="bg-grey-2">
        <q-btn
          flat
          label="Tutup"
          color="primary"
          @click="dialogVisible = false"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
