<script setup>
import { formatNumber } from "@/helpers/formatter";
import { nextTick, onMounted, ref } from "vue";

const barcodeInputRef = ref(null);

const props = defineProps({
  total: {
    type: Number,
    required: true,
  },
  itemCount: {
    type: Number,
    required: true,
  },
  barcode: {
    type: String,
    required: true,
  },
  isProcessing: {
    type: Boolean,
    default: false,
  },
  formProcessing: {
    type: Boolean,
    default: false,
  },
  isProductBrowserOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:barcode", "add-item", "process-payment"]);

onMounted(() => {
  focusOnBarcodeInput();
});

const focusOnBarcodeInput = () => {
  nextTick(() => {
    if (barcodeInputRef.value) {
      barcodeInputRef.value.focus();
    }
  });
};

const addItem = () => {
  if (!props.isProductBrowserOpen) {
    emit("add-item");
  }
};

defineExpose({
  focusOnBarcodeInput,
});
</script>

<template>
  <div class="column">
    <div class="row justify-end items-center q-gutter-sm">
      <span class="text-grey-8 text-subtitle-2">Total: Rp.</span>
      <span class="text-h5 text-weight-bold text-primary">
        {{ formatNumber(total) }}
      </span>
    </div>
    <div class="q-py-xs">
      <q-input
        ref="barcodeInputRef"
        :model-value="barcode"
        @update:model-value="(val) => $emit('update:barcode', val)"
        @keyup.enter.prevent="addItem()"
        :loading="isProcessing"
        :disable="isProcessing"
        placeholder="Qty * Kode / Barcode * Harga(opsional)"
        class="col bg-white"
        outlined
        clearable
        autofocus
        dense
      />
    </div>

    <div class="q-py-xs">
      <q-btn
        class="full-width"
        label="Bayar"
        color="primary"
        icon="payment"
        @click="$emit('process-payment')"
        :disable="isProcessing || itemCount === 0"
        :loading="formProcessing"
      />
    </div>
  </div>
</template>
