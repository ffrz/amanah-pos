<script setup>
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { computed, onMounted, onUnmounted, ref } from "vue";

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

const emit = defineEmits(["update:modelValue", "save"]);

// --- 1. DATA DUMMY SATUAN (Simulasi data dari backend) ---
const dummyProductUnits = [
  { name: "m", price: 4000 }, // Satuan Dasar
  { name: "ROLL", price: 683963 }, // Satuan Besar 1
  { name: "DUS BESAR", price: 1522950 }, // Satuan Besar 2
];

// --- 2. DATA OPSI UNTUK SELECT ---
const unitOptions = computed(() => {
  return dummyProductUnits.map((u) => ({
    label: u.name,
    value: u.name,
    price: u.price, // Titip harga di sini
  }));
});

// --- 3. LOGIKA GANTI SATUAN ---
const onUnitChange = (val) => {
  const selected = unitOptions.value.find((opt) => opt.value === val);
  if (selected) {
    // Update nama satuan
    props.item.product_uom = selected.value;
    // Update harga otomatis sesuai satuan
    props.item.price = selected.price;
  }
};

// --- LOGIKA BAWAAN ---

const handleSave = () => {
  emit("save");
};

const subtotal = computed(() => {
  return props.item.quantity * props.item.price;
});

const preventEvent = (e) => {
  e.stopPropagation();
  e.preventDefault();
};

const handleKeyDown = (e) => {
  if (props.modelValue) {
    if (e.key === "Enter") {
      handleSave();
      emit("update:modelValue", false);
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
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
  >
    <q-card>
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-subtite text-bold text-grey-8">Edit Item</div>
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
        />

        <div class="row q-col-gutter-sm">
          <div class="col-8">
            <LocaleNumberInput
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
              :disable="isProcessing"
              emit-value
              map-options
              @update:model-value="onUnitChange"
            />
          </div>
        </div>

        <LocaleNumberInput
          v-model="item.price"
          label="Harga (Rp)"
          :readonly="
            !(
              item.product?.price_editable ||
              $can('admin.sales-order.editor:edit-price')
            )
          "
          hide-bottom-space
          :disable="isProcessing"
        />

        <LocaleNumberInput
          v-model="subtotal"
          label="Subtotal (Rp)"
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
