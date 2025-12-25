<script setup>
import { computed, ref, watch } from "vue";
import { QSelect, QItem, QItemSection, QBtn } from "quasar";
import ProductBrandEditorDialog from "./ProductBrandEditorDialog.vue";

const props = defineProps({
  modelValue: [String, Number, null],
  brands: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "Merk",
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

const dialogRef = ref(null);

const brandOptions = computed(() => {
  return props.brands.map((c) => ({
    label: c.name || c.label,
    value: c.id || c.value,
  }));
});

const filteredOptions = ref(brandOptions.value);
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

const showNewBrandDialog = () => {
  dialogRef.value.show();
};

const handleNewBrandCreated = (newBrand) => {
  props.brands.push(newBrand);
  internalValue.value = newBrand.id;
  filteredOptions.value = brandOptions.value;
};

const filterFn = (val, update) => {
  if (val === "") {
    update(() => {
      filteredOptions.value = brandOptions.value;
    });
    return;
  }

  update(() => {
    const needle = val.toLowerCase();
    filteredOptions.value = brandOptions.value.filter(
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
          @click.stop="showNewBrandDialog"
          :disable="disable"
        >
          <q-tooltip>Tambah Merk Baru</q-tooltip>
        </q-btn>
      </template>

      <template v-slot:no-option>
        <q-item>
          <q-item-section>Merk tidak ditemukan</q-item-section>
        </q-item>
      </template>
    </q-select>

    <ProductBrandEditorDialog
      ref="dialogRef"
      @itemCreated="handleNewBrandCreated"
    />
  </div>
</template>
