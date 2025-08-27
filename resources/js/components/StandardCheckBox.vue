<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  disable: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
  <div style="margin-left: -10px">
    <q-checkbox
      class="full-width q-pl-none"
      :model-value="props.modelValue"
      :disable="props.disable"
      :label="props.label"
      @update:model-value="(val) => emit('update:modelValue', val)"
    />
  </div>
</template>
