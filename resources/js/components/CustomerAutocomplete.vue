<template>
  <q-select
    :class="class"
    ref="qSelectRef"
    v-model="selectedCustomer"
    use-input
    input-debounce="0"
    :label="label"
    :options="options"
    @filter="filterFn"
    clearable
    :error="error"
    behavior="menu"
    :error-message="errorMessage"
    :disable="disable"
    hide-bottom-space
    :outlined="outlined"
    style="min-width: 150px !important"
  />
</template>

<script setup>
import { onMounted, ref, watch } from "vue";
import axios from "axios";

const qSelectRef = ref(null);

const props = defineProps({
  modelValue: {
    type: [Object],
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
  initialData: {
    type: Object,
    default: () => ({
      value: null,
      data: {},
    }),
  },
});

const emit = defineEmits(["update:modelValue", "customerSelected"]);

// Gunakan ref untuk objek pelanggan lengkap
const selectedCustomer = ref(null);
const options = ref([]);
let timeoutId = null;

onMounted(() => {
  if (props.initialData?.id) {
    // Inisialisasi selectedCustomer dengan objek penuh
    const customerObj = {
      label: `${props.initialData.username} - ${props.initialData.name}`,
      value: props.initialData.id,
      data: props.initialData,
    };
    options.value.push(customerObj);
    selectedCustomer.value = customerObj;
  }
});

const focus = () => {
  if (qSelectRef.value) {
    qSelectRef.value.focus();
  }
};

defineExpose({ focus });

// Watcher untuk sinkronisasi dari luar (props.modelValue)
watch(
  () => props.modelValue,
  (newValue) => {
    // Jika newValue null, reset selectedCustomer menjadi null
    if (!newValue) {
      selectedCustomer.value = null;
    }
  }
);

// Watcher utama untuk memancarkan event ke parent
watch(selectedCustomer, (newValue) => {
  if (newValue) {
    emit("update:modelValue", newValue.value);
    emit("customerSelected", newValue.data);
  } else {
    // Jika input dikosongkan (clearable), pancarkan null
    emit("update:modelValue", null);
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
