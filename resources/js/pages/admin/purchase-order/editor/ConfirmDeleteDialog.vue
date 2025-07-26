<script setup>
defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  item: {
    type: Object,
    default: null,
  },
});

defineEmits(["update:modelValue", "confirm"]);
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    persistent
  >
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="delete" color="negative" text-color="white" />
        <span class="q-ml-sm">Hapus item "{{ item?.name }}"?</span>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Batal" color="primary" v-close-popup />
        <q-btn
          flat
          label="Hapus"
          color="negative"
          @click="$emit('confirm')"
          v-close-popup
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
