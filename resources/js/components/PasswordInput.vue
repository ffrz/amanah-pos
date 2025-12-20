<template>
  <q-input
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    :type="showPassword ? 'text' : 'password'"
    :label="label"
    :error="!!errorMessage"
    :error-message="errorMessage"
    :disable="loading || disable"
    lazy-rules
    hide-bottom-space
    autocomplete="current-password"
    data-test="password"
    v-bind="$attrs"
  >
    <template v-slot:append>
      <q-btn
        dense
        flat
        round
        :icon="showPassword ? 'visibility_off' : 'visibility'"
        @click="showPassword = !showPassword"
        tabindex="-1"
      />
    </template>
  </q-input>
</template>

<script setup>
import { ref } from "vue";

// Definisi Props agar dinamis
const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  label: {
    type: String,
    default: "Kata Sandi",
  },
  errorMessage: {
    type: String,
    default: "",
  },
  disable: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

// Definisi Emits untuk v-model
defineEmits(["update:modelValue"]);

// State lokal untuk toggle show/hide (tidak perlu diurus parent lagi)
const showPassword = ref(false);
</script>
