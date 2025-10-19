<script setup lang="ts">
import { nextTick, ref, useSlots, watch } from "vue";
import { QInput } from "quasar";

const qInputRef = ref<InstanceType<typeof QInput> | null>(null);
const slots = useSlots();

// 1. STATE BARU: Untuk melacak fokus
const isFocused = ref(false);

// Expose focus & select methods
defineExpose({
  focus: () => {
    if (qInputRef.value) {
      qInputRef.value.focus();
    }
  },
  select: () => {
    if (qInputRef.value?.$el) {
      const input = qInputRef.value.$el.querySelector(
        "input"
      ) as HTMLInputElement;
      input?.select();
    }
  },
});

// Props
const props = defineProps({
  modelValue: {
    type: [Number, null],
    required: false,
    default: 0,
  },
  label: {
    type: [String, undefined],
    required: false,
    default: undefined,
  },
  locale: { type: String, default: "id-ID" },
  outlined: { type: Boolean, default: false },
  allowNegative: { type: Boolean, default: false },
  maxDecimals: { type: Number, default: 0 },
  lazyRules: { type: [Boolean, String], default: false },
  disable: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
  errorMessage: { type: String, default: "" },
  rules: { type: Array, default: () => [] },
});

// Emit
const emit = defineEmits(["update:modelValue"]);

// Internal display value
const displayValue = ref("");

// Detect locale separators
const getLocaleSeparators = (locale: string) => {
  const sampleNumber = 1234567.89;
  const formatted = new Intl.NumberFormat(locale).format(sampleNumber);
  const decimalSeparator = formatted.includes(",") ? "," : ".";
  const thousandSeparator = formatted
    .replace(/\d/g, "")
    .replace(decimalSeparator, "")
    .charAt(0);
  return { decimalSeparator, thousandSeparator };
};

const { decimalSeparator, thousandSeparator } = getLocaleSeparators(
  props.locale
);

// Format number (Hanya digunakan saat BLUR atau initial load)
const formatNumber = (value: number | null): string => {
  if (value === null || value === undefined || isNaN(value)) {
    // Jika null/NaN, kembalikan string kosong agar mudah diketik, kecuali maxDecimals=0
    if (props.maxDecimals > 0) return "";
    value = 0;
  }
  return new Intl.NumberFormat(props.locale, {
    // Memaksa minimumFractionDigits yang memicu bug jika digunakan saat onInput
    minimumFractionDigits: props.maxDecimals,
    maximumFractionDigits: props.maxDecimals,
  }).format(value);
};

// 2. Sync displayValue dengan modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    // HANYA format jika input TIDAK difokuskan. Ini MENCEGAH overriding saat mengetik.
    if (!isFocused.value) {
      displayValue.value = formatNumber(newValue);
    }
  },
  { immediate: true }
);

// Sanitize input (logika tetap)
const sanitizeInput = (value: string): number | null => {
  // Regex dan pembersihan string mentah
  const sanitized = value
    .replace(/[^0-9.,-]+/g, "")
    .replace(new RegExp(`\\${thousandSeparator}`, "g"), "")
    // Ganti separator locale ke dot agar parseFloat bekerja
    .replace(new RegExp(`\\${decimalSeparator}`, "g"), ".");

  if (sanitized === "" || sanitized === "-") {
    return null;
  }

  const parsed = parseFloat(sanitized);

  // Tetap gunakan toFixed untuk membatasi presisi nilai modelValue yang di-emit
  return isNaN(parsed) ? null : parseFloat(parsed.toFixed(props.maxDecimals));
};

// Input handlers
// 3. ON FOCUS: Bersihkan tampilan untuk pengeditan yang mudah
const onFocus = () => {
  isFocused.value = true;
  // Konversi nilai display yang mungkin sudah terformat ribuan
  const sanitizedNumber = sanitizeInput(displayValue.value);

  // if (sanitizedNumber === null) {
  //   displayValue.value = "";
  // } else {
  //   // Tampilkan string tanpa pemisah ribuan, tetapi dengan pemisah desimal locale
  //   displayValue.value = sanitizedNumber
  //     .toString()
  //     .replace(".", decimalSeparator);
  // }

  nextTick(() => {
    qInputRef.value?.select();
  });
};

// 4. ON INPUT: Hanya perbarui tampilan mentah, JANGAN EMIT
const onInput = (value: string) => {
  displayValue.value = value;
  // Hapus emitUpdate() dari sini!
};

// 5. ON BLUR: Sanitasi, Emit, dan Format Ulang Tampilan
const onBlur = () => {
  isFocused.value = false;

  const sanitizedValue = sanitizeInput(displayValue.value);

  // Emit nilai yang sudah disanitasi dan dibatasi presisinya
  emit("update:modelValue", sanitizedValue);

  // Terapkan format akhir ke displayValue untuk presentasi (dengan minimumFractionDigits)
  displayValue.value = formatNumber(sanitizedValue);
};

// Keyboard filter (tetap sama)
const filterInput = (event: KeyboardEvent) => {
  const allowedKeys = [
    "Backspace",
    "Delete",
    "Tab",
    "ArrowLeft",
    "ArrowRight",
    "Home",
    "End",
    "Shift",
  ];
  if (event.ctrlKey || event.metaKey) return;

  if (event.key >= "0" && event.key <= "9") return;
  if (
    (event.key === decimalSeparator || event.key === thousandSeparator) &&
    !displayValue.value.includes(decimalSeparator)
  )
    return;
  if (
    props.allowNegative &&
    event.key === "-" &&
    (event.target as HTMLInputElement).selectionStart === 0
  )
    return;
  if (allowedKeys.includes(event.key)) return;

  event.preventDefault();
};
</script>

<template>
  <q-input
    ref="qInputRef"
    :model-value="displayValue"
    :label="props.label"
    :outlined="props.outlined"
    @update:model-value="onInput"
    @blur="onBlur"
    @focus="onFocus"
    @keydown="filterInput"
    :lazy-rules="props.lazyRules"
    :disable="props.disable"
    :error="props.error"
    :rules="props.rules"
    :error-message="props.errorMessage"
  >
    <template v-if="slots.prepend" v-slot:prepend>
      <slot name="prepend"></slot>
    </template>
    <template v-if="slots.append" v-slot:append>
      <slot name="append"></slot>
    </template>
  </q-input>
</template>
