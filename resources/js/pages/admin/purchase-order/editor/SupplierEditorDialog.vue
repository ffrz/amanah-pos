<script setup>
import { ref } from "vue";

defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "save"]);

const supplierName = ref("");
const supplierAddress = ref("");

const handleSave = () => {
  emit("save", {
    name: supplierName.value,
    address: supplierAddress.value,
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
        <div class="text-bold text-grey-8">Edit Info Supplier</div>
      </q-card-section>
      <q-card-section>
        <q-input v-model="supplierName" label="Nama Supplier" />
        <q-input
          v-model="supplierAddress"
          label="Alamat"
          type="textarea"
          autogrow
          counter
          maxlength="200"
          class="q-mt-md"
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
