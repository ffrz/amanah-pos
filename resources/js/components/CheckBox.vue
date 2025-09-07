<template>
  <div style="margin-left: -10px">
    <q-checkbox
      class="full-width q-pl-none"
      :model-value="modelValue"
      @update:model-value="(value) => $emit('update:modelValue', value)"
      :disable="disable"
      :label="label"
    />
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  modelValue: {
    type: [Boolean, Array],
    default: false,
  },
  label: {
    type: String,
    default: "",
  },
  disable: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["update:modelValue"]);
</script>
