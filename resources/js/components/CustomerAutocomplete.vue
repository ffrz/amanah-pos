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
    option-value="id"
    option-label="name"
  >
    <template #option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section>
          <q-item-label>{{ scope.opt.name }}</q-item-label>
          <q-item-label caption>
            <q-icon name="person" size="14px" />
            {{ scope.opt.username }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </template>

    <template #selected-item="scope">
      <div v-if="scope.opt" class="q-select__selected-item">
        {{ scope.opt.name }} ({{ scope.opt.username }})
      </div>
    </template>
  </q-select>
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
  class: { type: String, default: "" },
  label: { type: String, default: "Cari Pelanggan" },
  error: { type: Boolean, default: false },
  errorMessage: { type: String, default: "" },
  disable: { type: Boolean, default: false },
  outlined: { type: Boolean, default: false },
  minLength: { type: Number, default: 0 },
  initialData: {
    type: Object,
    default: null,
    required: false,
  },
});
const emit = defineEmits(["update:modelValue", "customerSelected"]);
const options = ref([]);
let timeoutId = null;

// State lokal untuk v-model
const selectedCustomer = ref(props.modelValue);

onMounted(() => {
  // Inisialisasi dengan data awal dari prop
  if (props.initialData) {
    selectedCustomer.value = props.initialData;
  }
});

// Watcher untuk menyinkronkan state internal dan eksternal
watch(selectedCustomer, (newValue) => {
  emit("update:modelValue", newValue);
  emit("customerSelected", newValue);
});

// Watcher untuk mengupdate state internal jika props.modelValue berubah
watch(
  () => props.modelValue,
  (newValue) => {
    selectedCustomer.value = newValue;
  }
);

const focus = () => {
  if (qSelectRef.value) {
    qSelectRef.value.focus();
  }
};
defineExpose({ focus });

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
        update(() => {
          options.value = response.data.data;
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
