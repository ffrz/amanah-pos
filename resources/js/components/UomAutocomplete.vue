<script setup>
import { computed, ref, watch } from "vue";
import { QSelect, QItem, QItemSection, QBtn } from "quasar";
import UomEditorDialog from "./UomEditorDialog.vue";

const props = defineProps({
  modelValue: [String, Number, null],
  items: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "Satuan",
  },
  disable: Boolean,
  error: Boolean,
  errorMessage: String,
  showAddButton: {
    type: Boolean,
    default: true,
  },
});

// ⭐ Perubahan 1: Tambahkan 'update:items' untuk memperbarui array items di parent
const emit = defineEmits(["update:modelValue", "update:items"]);

const editorRef = ref(null);

// Hapus: const localItems = ref([...props.items]);
// Hapus: watch(() => props.items, ...)

// ⭐ Perubahan 2: Gunakan props.items secara langsung
const uomOptions = computed(() => {
  return props.items.map((c) => ({
    label: c.name,
    value: c.name, // Diasumsikan nilai yang disimpan adalah nama/label UOM
  }));
});

// ⭐ Perubahan 3: Inisialisasi filteredOptions dengan menggunakan uomOptions
const filteredOptions = ref(uomOptions.value);

const internalValue = ref(props.modelValue);

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue !== internalValue.value) {
      internalValue.value = newValue;
    }
  },
  { immediate: true }
);

watch(internalValue, (newValue) => {
  emit("update:modelValue", newValue);
});

const showNewCategoryDialog = () => {
  // Diasumsikan UomEditorDialog memiliki fungsi `show()`
  editorRef.value?.show();
};

// ⭐ Perubahan 4: Perbarui props.items di parent melalui event 'update:items'
const handleUomCreated = (item) => {
  // 1. Buat array items baru dengan item baru di dalamnya
  const newItems = [...props.items, item];

  // 2. Kirim event untuk memberitahu parent agar mengupdate prop 'items'
  emit("update:items", newItems);

  // 3. Atur nilai terpilih ke item yang baru dibuat
  internalValue.value = item.name;
  // 4. Update filteredOptions agar list dropdown menampilkan UOM baru
  // Note: Karena uomOptions adalah computed dari props.items,
  // filteredOptions perlu diupdate setelah parent menerima update:items (walaupun ini akan terjadi di next tick)
  // Untuk kepastian, kita bisa update list-nya secara lokal sebelum ditutup
  filteredOptions.value = newItems.map((c) => ({
    label: c.name,
    value: c.name,
  }));

  // 5. Tutup dialog
  editorRef.value?.hide();
};

const filterFn = (val, update) => {
  if (val === "") {
    update(() => {
      filteredOptions.value = uomOptions.value;
    });
    return;
  }

  update(() => {
    const needle = val.toLowerCase(); // ⭐ Perubahan 5: Gunakan uomOptions.value untuk filtering
    filteredOptions.value = uomOptions.value.filter(
      (v) => v.label.toLowerCase().indexOf(needle) > -1
    );
  });
};
</script>

<template>
  <div>
    <q-select
      v-bind="$attrs"
      v-model="internalValue"
      :label="label"
      :options="filteredOptions"
      use-input
      input-debounce="300"
      clearable
      map-options
      emit-value
      @filter="filterFn"
      option-label="label"
      option-value="value"
      :error="error"
      :disable="disable"
      :error-message="errorMessage"
      hide-bottom-space
      class="custom-select"
    >
      <template v-slot:prepend v-if="showAddButton">
        <q-btn
          icon="add_circle"
          flat
          round
          dense
          size="sm"
          @click.stop="showNewCategoryDialog"
          :disable="disable"
        >
          <q-tooltip>Tambah Kategori Baru</q-tooltip>
        </q-btn>
      </template>

      <template v-slot:no-option>
        <q-item>
          <q-item-section>Satuan tidak ditemukan</q-item-section>
        </q-item>
      </template>
    </q-select>

    <UomEditorDialog ref="editorRef" @uomCreated="handleUomCreated" />
  </div>
</template>
