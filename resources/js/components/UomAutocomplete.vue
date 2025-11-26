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

const emit = defineEmits(["update:modelValue"]);

const editorRef = ref(null);
const localItems = ref([...props.items]);

watch(
  () => props.items,
  (newItems) => {
    if (JSON.stringify(newItems) !== JSON.stringify(localItems.value)) {
      localItems.value = [...newItems];
      filteredOptions.value = uomOptions.value;
    }
  },
  { immediate: true }
);

const uomOptions = computed(() => {
  return localItems.value.map((c) => ({
    label: c.name,
    value: c.name,
  }));
});

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

// ⭐ Perbaikan 2: Pastikan kita memanggil fungsi `show` yang ada di UomEditorDialog
const showNewCategoryDialog = () => {
  // Diasumsikan UomEditorDialog memiliki fungsi `show()`
  editorRef.value?.show();
};

// ⭐ Perbaikan 3: Modifikasi localItems, bukan props.items DAN menutup editor
const handleUomCreated = (item) => {
  localItems.value.push(item);
  internalValue.value = item.name;
  filteredOptions.value = uomOptions.value;
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
    const needle = val.toLowerCase();
    filteredOptions.value = uomOptions.value.filter(
      (v) => v.label.toLowerCase().indexOf(needle) > -1
    );
  });
};
</script>

<template>
  <div>
    <q-select
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
