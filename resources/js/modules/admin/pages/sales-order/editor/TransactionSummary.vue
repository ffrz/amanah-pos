<script setup>
import { formatNumber } from "@/helpers/formatter";
import { nextTick, onMounted, ref } from "vue";

const barcodeInputRef = ref(null);

const props = defineProps({
  subtotal: {
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
      <span class="text-weight-bold text-grey-8">GRAND TOTAL: Rp.</span>
      <span class="text-h4 text-weight-bold text-primary">
        {{ formatNumber(subtotal) }}
      </span>
    </div>
    <div class="q-py-xs">
      <q-input
        ref="barcodeInputRef"
        :model-value="barcode"
        @update:model-value="(val) => $emit('update:barcode', val)"
        placeholder="Qty*Kode/Barcode*Harga"
        outlined
        class="col bg-white"
        @keyup.enter.prevent="addItem()"
        :loading="isProcessing"
        clearable
        autofocus
        :disable="isProcessing"
      >
        <template v-slot:prepend>
          <q-icon name="qr_code_scanner" />
        </template>
      </q-input>
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
