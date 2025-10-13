<script setup>
defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
  >
    <q-card style="min-width: 300px">
      <q-card-section class="q-py-none q-pt-md">
        <div class="text-bold text-grey-8">Bantuan</div>
      </q-card-section>
      <q-card-section>
        <table style="width: 100%" class="info-table">
          <thead>
            <tr>
              <th>Shortcut</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>F1</td>
              <td>Dialog Bantuan</td>
            </tr>
            <tr>
              <td>F2</td>
              <td>Pilih Pelanggan</td>
            </tr>
            <tr>
              <td>F3</td>
              <td>Aktifkan Input Barcode<</td>
            </tr>
            <tr>
              <td>F4</td>
              <td>Gabung / pisahkan item saat input</td>
            </tr>
            <tr>
              <td>F10 / Ctrl+Enter</td>
              <td>Bayar / Selesai</td>
            </tr>
          </tbody>
        </table>
      </q-card-section>
      <q-card-actions align="center">
        <q-btn dense label="Tutup" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<style scoped>
.info-table {
  border-collapse: collapse;
}
.info-table td,
.info-table th {
  border: 1px solid #aaa;
  padding: 0 8px;
}
</style>
