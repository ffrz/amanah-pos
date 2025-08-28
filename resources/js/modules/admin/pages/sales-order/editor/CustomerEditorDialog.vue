<script setup>
import { ref } from "vue";

defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "save"]);

const customerName = ref("");
const customerAddress = ref("");

const handleSave = () => {
  emit("save", {
    name: customerName.value,
    address: customerAddress.value,
  });
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    persistent
  >
    <q-card style="width: 400px">
      <q-card-section class="q-py-none q-pt-lg">
        <div class="text-bold text-grey-8">Edit Info Pelanggan</div>
      </q-card-section>
      <q-card-section>
        <q-input v-model="customerName" label="Nama Pelanggan" />
        <q-input
          v-model="customerAddress"
          label="Alamat"
          type="textarea"
          autogrow
          counter
          maxlength="200"
          class="q-mt-md"
          hide-bottom-space
        />
      </q-card-section>
      <q-card-actions align="right">
        <q-btn flat label="Batal" color="primary" v-close-popup />
        <q-btn
          flat
          label="Simpan"
          color="primary"
          @click="handleSave"
          v-close-popup
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
