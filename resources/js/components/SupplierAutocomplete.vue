<template>
  <q-select
    v-bind="$attrs"
    :class="class"
    ref="qSelectRef"
    v-model="selectedSupplier"
    use-input
    input-debounce="500"
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
    <template #prepend>
      <q-icon
        name="person_add"
        class="q-icon cursor-pointer"
        size="xs"
        @click.prevent="addSupplier"
      />
    </template>

    <template #option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section>
          <q-item-label
            >{{ scope.opt.name }} ({{ scope.opt.code }})</q-item-label
          >
          <q-item-label
            v-if="scope.opt.phone_1"
            class="text-grey-8 q-ml-sm q-mt-xs"
          >
            <q-icon name="phone" class="inline-icon" />{{ scope.opt.phone_1 }}
          </q-item-label>
          <q-item-label v-if="scope.opt.address" class="text-grey-8 q-ml-sm">
            <q-icon name="home_pin" class="inline-icon" />{{
              scope.opt.address
            }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </template>

    <template #selected-item="scope">
      <div v-if="scope.opt" class="q-select__selected-item">
        {{ scope.opt.name }} ({{ scope.opt.code }})
      </div>
    </template>
  </q-select>
  <SupplierEditorDialog
    ref="editorDialog"
    @supplier-created="onSupplierCreated"
  />
</template>

<script setup>
import { onMounted, ref, watch } from "vue";
import axios from "axios";
import SupplierEditorDialog from "./SupplierEditorDialog.vue";

const editorDialog = ref(null);
const qSelectRef = ref(null);
const props = defineProps({
  modelValue: {
    type: [Object],
    default: null,
  },
  class: { type: String, default: "" },
  label: { type: String, default: "Cari Pemasok" },
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
const emit = defineEmits(["update:modelValue", "supplierSelected"]);
const options = ref([]);
let timeoutId = null;

const selectedSupplier = ref(props.modelValue);

const addSupplier = () => {
  editorDialog.value.open();
};

onMounted(() => {
  if (props.initialData) {
    selectedSupplier.value = props.initialData;
  }
});

watch(selectedSupplier, (newValue) => {
  emit("update:modelValue", newValue);
  emit("supplierSelected", newValue);
});

watch(
  () => props.modelValue,
  (newValue) => {
    selectedSupplier.value = newValue;
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
      .get(`/web-api/suppliers?q=${val}`)
      .then((response) => {
        update(() => {
          options.value = response.data.data;
        });
      })
      .catch((error) => {
        console.error("Gagal mengambil data pemasok:", error);
        update(() => {
          options.value = [];
        });
      });
  }, 100);
};

const onSupplierCreated = (newSupplier) => {
  selectedSupplier.value = newSupplier;
  options.value = [newSupplier, ...options.value];
  focus();
};
</script>
