<script setup>
import { computed, ref, watch } from "vue";
import { QSelect, QItem, QItemSection, QBtn } from "quasar";
import ProductCategoryEditorDialog from "./ProductCategoryEditorDialog.vue";

const props = defineProps({
  modelValue: [String, Number, null],
  categories: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "Kategori (Opsional)",
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

const productCategoryDialogRef = ref(null);

const categoryOptions = computed(() => {
  return props.categories.map((c) => ({
    label: c.name || c.label,
    value: c.id || c.value,
    categoryData: c,
  }));
});

const filteredOptions = ref(categoryOptions.value);
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
  productCategoryDialogRef.value.show();
};

const handleNewCategoryCreated = (newCategory) => {
  props.categories.push(newCategory);
  internalValue.value = newCategory.id;
  filteredOptions.value = categoryOptions.value;
};

const filterFn = (val, update) => {
  if (val === "") {
    update(() => {
      filteredOptions.value = categoryOptions.value;
    });
    return;
  }

  update(() => {
    const needle = val.toLowerCase();
    filteredOptions.value = categoryOptions.value.filter(
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
          <q-item-section>Kategori tidak ditemukan</q-item-section>
        </q-item>
      </template>
    </q-select>

    <ProductCategoryEditorDialog
      ref="productCategoryDialogRef"
      @categoryCreated="handleNewCategoryCreated"
    />
  </div>
</template>
