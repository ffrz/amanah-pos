<script setup>
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import axios from "axios"; // Pastikan axios di-import

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  item: {
    type: Object,
    required: false,
  },
  isProcessing: {
    type: Boolean,
    required: false,
  },
});

const qtyInput = ref(null);
const emit = defineEmits(["update:modelValue", "save"]);

// --- 1. STATE & FETCH LOGIC ---
const availableUnits = ref([]);
const isLoadingUnits = ref(false);

const fetchProductUnits = async () => {
  if (!props.item?.product_id) {
    availableUnits.value = [];
    return;
  }

  isLoadingUnits.value = true;
  try {
    // Kita gunakan endpoint yang sama karena kita butuh daftar satuannya.
    // Harga yang dikembalikan server kita abaikan saja.
    const response = await axios.get(
      `/web-api/products/${props.item.product_id}/units`
    );

    if (response.data && response.data.data) {
      availableUnits.value = response.data.data;
    } else {
      availableUnits.value = [];
    }
  } catch (error) {
    console.error("Gagal mengambil satuan produk:", error);
    // Fallback agar select tidak kosong
    availableUnits.value = [{ name: props.item.product_uom, is_base: true }];
  } finally {
    isLoadingUnits.value = false;
  }
};

// --- 2. WATCHER (Reset & Fetch saat Dialog Buka) ---
watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      // Reset data lama
      availableUnits.value = [];
      // Fetch baru
      fetchProductUnits();
    }
  },
  { immediate: true }
);

// --- 3. OPTIONS & CHANGE HANDLER ---
const unitOptions = computed(() => {
  return availableUnits.value.map((u) => ({
    label: u.name,
    value: u.name,
    // Kita tidak mapping price di sini karena pembelian tidak otomatis ganti harga
    description: u.is_base ? "(Dasar)" : "",
  }));
});

const onUnitChange = (val) => {
  // Hanya update nama satuan, JANGAN update harga beli (cost)
  // karena harga beli biasanya manual atau mengikuti deal terakhir
  if (val) {
    props.item.product_uom = val;
  }
};

// --- LOGIKA BAWAAN ---

const handleSave = () => {
  emit("save");
};

const subtotal = computed(() => {
  return (props.item.quantity || 0) * (props.item.cost || 0);
});

const preventEvent = (e) => {
  e.stopPropagation();
  e.preventDefault();
};

const handleKeyDown = (e) => {
  if (props.modelValue) {
    if (e.key === "Enter") {
      handleSave();
      preventEvent(e);
    } else if (e.key === "Escape") {
      emit("update:modelValue", false);
      preventEvent(e);
    }
  }
};

onMounted(() => {
  window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeyDown);
});

const getCurrentItem = () => {
  return props.item;
};

defineExpose({
  getCurrentItem,
});

const onShow = () => {
  // alert("show");
  nextTick(() => {
    qtyInput.value.focus();
  });
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    @show="onShow"
  >
    <q-card style="width: 100%; max-width: 500px">
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-subtite text-bold text-grey-8">
            Edit Item Pembelian
          </div>
          <div class="col-auto">
            <q-btn
              flat
              size="sm"
              round
              icon="close"
              @click="$emit('update:modelValue', false)"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-py-sm">
        <q-input
          v-model="item.product_name"
          label="Produk"
          hide-bottom-space
          readonly
          :disable="isProcessing"
          class="q-mb-sm"
          autofocus
        />

        <div class="row q-col-gutter-sm">
          <div class="col-8">
            <LocaleNumberInput
              ref="qtyInput"
              v-model="item.quantity"
              label="Kuantitas"
              hide-bottom-space
              autofocus
              :disable="isProcessing"
            />
          </div>
          <div class="col-4">
            <q-select
              v-model="item.product_uom"
              :options="unitOptions"
              label="Satuan"
              hide-bottom-space
              :disable="isProcessing || isLoadingUnits"
              :loading="isLoadingUnits"
              emit-value
              map-options
              @update:model-value="onUnitChange"
            >
              <template v-slot:option="scope">
                <q-item v-bind="scope.itemProps">
                  <q-item-section>
                    <q-item-label>{{ scope.opt.label }}</q-item-label>
                    <q-item-label caption v-if="scope.opt.description">
                      {{ scope.opt.description }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
          </div>
        </div>

        <LocaleNumberInput
          v-model="item.cost"
          label="Modal / Harga Beli"
          hide-bottom-space
          :disable="isProcessing"
        />

        <LocaleNumberInput
          v-model="subtotal"
          label="Subtotal"
          hide-bottom-space
          readonly
        />

        <q-input
          v-model="item.notes"
          label="Catatan"
          autogrow
          type="textarea"
          counter
          maxlength="50"
          hide-bottom-space
          clearable
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          label="Batal"
          color="primary"
          v-close-popup
          :disable="isProcessing"
        />
        <q-btn
          flat
          label="Simpan"
          color="primary"
          @click="handleSave"
          v-close-popup
          :disable="isProcessing"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
