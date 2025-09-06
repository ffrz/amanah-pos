<template>
  <q-select
    dense
    v-model="selectedId"
    use-input
    input-debounce="0"
    :label="label"
    :options="options"
    @filter="filterFn"
    hint="Ketik minimal 2 karakter untuk mencari"
    clearable
    behavior="menu"
    :error="error"
    :error-message="errorMessage"
    :disable="disable"
    hide-bottom-space
    emit-value
    map-options
  />
</template>

<script setup>
import { ref, watch } from "vue";
import axios from "axios";

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: null,
  },
  label: {
    type: String,
    default: "Cari Pelanggan",
  },
  error: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: "",
  },
  disable: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue", "customerSelected"]);

const selectedId = ref(props.modelValue);
const options = ref([]);
let timeoutId = null;

watch(
  () => props.modelValue,
  (newValue) => {
    selectedId.value = newValue;
  }
);

watch(selectedId, (newValue) => {
  emit("update:modelValue", newValue);

  const selectedOption = options.value.find(
    (option) => option.value === newValue
  );

  if (selectedOption) {
    emit("customerSelected", selectedOption.data);
  } else {
    emit("customerSelected", null);
  }
});

const filterFn = (val, update) => {
  if (val.length < 2) {
    update(() => {
      options.value = [];
    });
    return;
  }

  if (timeoutId) {
    clearTimeout(timeoutId);
  }

  timeoutId = setTimeout(() => {
    axios
      .get(`/web-api/customers?q=${val}`)
      .then((response) => {
        const transformedData = response.data.data.map((customer) => {
          return {
            label: `${customer.username} - ${customer.name}`,
            value: customer.id,
            data: customer,
          };
        });
        update(() => {
          options.value = transformedData;
        });
      })
      .catch((error) => {
        console.error("Gagal mengambil data pelanggan:", error);
        update(() => {
          options.value = [];
        });
      });
  }, 500);
};
</script>
