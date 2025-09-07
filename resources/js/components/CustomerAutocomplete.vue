<template>
  <q-select
    :class="class"
    ref="qSelectRef"
    v-model="selectedId"
    use-input
    input-debounce="0"
    :label="label"
    :options="options"
    @filter="filterFn"
    clearable
    :error="error"
    :error-message="errorMessage"
    :disable="disable"
    hide-bottom-space
    emit-value
    map-options
    :outlined="outlined"
    style="min-width: 150px !important"
  />
</template>

<script setup>
import { ref, watch } from "vue";
import axios from "axios";
const qSelectRef = ref(null);

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: null,
  },
  class: {
    type: String,
    default: "",
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
  outlined: {
    type: Boolean,
    default: false,
  },
  minLength: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(["update:modelValue", "customerSelected"]);

const selectedId = ref(props.modelValue);
const options = ref([]);
let timeoutId = null;

const focus = () => {
  if (qSelectRef.value) {
    qSelectRef.value.focus();
  }
};

defineExpose({ focus });

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
  if (val.length < props.minLength) {
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
