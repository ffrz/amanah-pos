<script setup lang="ts">
import { ref, useSlots, watch } from "vue";
import { QInput } from "quasar";

const qInputRef = ref<InstanceType<typeof QInput> | null>(null);
const slots = useSlots();

// Expose focus & select methods
defineExpose({
  focus: () => {
    if (qInputRef.value) {
      qInputRef.value.focus();
    }
  },
  select: () => {
    if (qInputRef.value?.$el) {
      // const input = qInputRef.value.$el.querySelector(
      //   "input"
      // ) as HTMLInputElement;
      // input?.select();
    }
  },
});

// Props
const props = defineProps({
  modelValue: {
    type: [Number, null], // <-- UBAH INI
    required: false, // Jika bisa null, sebaiknya jangan required
    default: 0, // Opsional: Tetapkan 0 sebagai nilai default yang aman
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

// Format number
const formatNumber = (value: number) => {
  if (value === null || value === undefined || isNaN(value)) {
    value = 0;
  }
  return new Intl.NumberFormat(props.locale, {
    minimumFractionDigits: props.maxDecimals,
    maximumFractionDigits: props.maxDecimals,
  }).format(value);
};

// Sync displayValue with modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    displayValue.value = formatNumber(newValue);
  },
  { immediate: true }
);

// Sanitize input
const sanitizeInput = (value: string): number | null => {
  const regex = props.allowNegative
    ? /^-?[0-9]+([.,][0-9]*)?$/
    : /^[0-9]+([.,][0-9]*)?$/;

  const sanitized = value
    .replace(/[^0-9.,-]+/g, "")
    .replace(new RegExp(`\\${thousandSeparator}`, "g"), "")
    .replace(new RegExp(`\\${decimalSeparator}`, "g"), ".");

  // This is the bug fix. If the input is empty or just a minus sign, return null.
  if (sanitized === "" || sanitized === "-") {
    return null;
  }

  const parsed = parseFloat(sanitized);
  return isNaN(parsed) ? null : parseFloat(parsed.toFixed(props.maxDecimals));
};

const emitUpdate = () => {
  const sanitizedValue = sanitizeInput(displayValue.value);
  emit("update:modelValue", sanitizedValue);
};

// Input handlers
const onInput = (value: string) => {
  displayValue.value = value;
  emitUpdate();
};

const onBlur = () => {
  displayValue.value = formatNumber(sanitizeInput(displayValue.value));
  emitUpdate();
};

// Keyboard filter
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
    @keydown="filterInput"
    @focus="qInputRef.select()"
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
