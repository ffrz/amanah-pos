<script setup>
import { ref, watch } from "vue";
import { QInput } from "quasar";

const qInputRef = ref(null);

defineExpose({
  // Ekspor metode fokus dan select yang berfungsi
  focus: () => {
    // Memanggil metode fokus pada instance QInput
    if (qInputRef.value) {
      qInputRef.value.focus();
    }
  },
  select: () => {
    // PENTING: Panggil metode select pada elemen input asli
    if (qInputRef.value) {
      qInputRef.value.select();
    }
  },
});

// Props
const props = defineProps({
  modelValue: { type: Number, required: true, default: 0 },
  label: { type: String, default: "" },
  locale: { type: String, default: "id-ID" },
  outlined: { type: Boolean, default: false },
  allowNegative: { type: Boolean, default: false },
  maxDecimals: { type: Number, default: 0 },
  lazyRules: { type: String },
  disable: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
  errorMessage: { type: String, default: "" },
  rules: { type: Array, default: () => [] },
});

// Emit events
const emit = defineEmits(["update:modelValue"]);

// Internal state for display value
const displayValue = ref("");

// Detect locale's decimal and thousand separators
const getLocaleSeparators = (locale) => {
  const sampleNumber = 1234567.89;
  const formatted = new Intl.NumberFormat(locale).format(sampleNumber);
  const decimalSeparator = formatted.includes(".")
    ? "."
    : formatted.includes(",")
    ? ","
    : ".";
  const thousandSeparator = formatted
    .replace(/\d/g, "")
    .replace(decimalSeparator, "")
    .slice(0, 1);
  return { decimalSeparator, thousandSeparator };
};

const { decimalSeparator, thousandSeparator } = getLocaleSeparators(
  props.locale
);

// Format a number according to the locale
const formatNumber = (value) => {
  if (value === null || value === undefined || isNaN(value)) {
    value = 0;
  }
  return new Intl.NumberFormat(props.locale, {
    minimumFractionDigits: props.maxDecimals,
    maximumFractionDigits: props.maxDecimals,
  }).format(value);
};

// Watch for changes in modelValue and sync displayValue
watch(
  () => props.modelValue,
  (newValue) => {
    displayValue.value = formatNumber(newValue);
  },
  { immediate: true }
);

// Sanitize input and convert to a valid number
const sanitizeInput = (value) => {
  if (value === null || value === undefined) return 0;

  const sanitized = value
    .toString()
    .replace(new RegExp(`\\${thousandSeparator}`, "g"), "")
    .replace(new RegExp(`\\${decimalSeparator}`, "g"), ".");

  const parsed = parseFloat(sanitized);
  return isNaN(parsed) ? 0 : parsed;
};

// Update display value on input
const onInput = (value) => {
  displayValue.value = value;
};

// Emit sanitized value on blur
const onBlur = () => {
  const sanitizedValue = sanitizeInput(displayValue.value);
  displayValue.value = formatNumber(sanitizedValue);
  emit("update:modelValue", sanitizedValue);
};

// Filter keyboard input
const filterInput = (event) => {
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
  const isNumber = event.key >= "0" && event.key <= "9";
  const isSeparator =
    event.key === decimalSeparator || event.key === thousandSeparator;
  const isAllowed =
    allowedKeys.includes(event.key) ||
    (event.key === "-" && props.allowNegative);

  if (
    !isNumber &&
    !isSeparator &&
    !isAllowed &&
    !event.ctrlKey &&
    !event.metaKey
  ) {
    event.preventDefault();
  }
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
    @keydown="filterInput"
    :lazy-rules="lazyRules"
    :disable="disable"
    :error="error"
    :rules="rules"
    :error-message="errorMessage"
  >
    <template v-slot:prepend>
      <slot name="prepend"></slot>
    </template>
    <template v-slot:append>
      <slot name="append"></slot>
    </template>
  </q-input>
</template>
