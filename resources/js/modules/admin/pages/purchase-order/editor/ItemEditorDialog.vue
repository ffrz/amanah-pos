<script setup>
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import axios from "axios";
import { formatNumber } from "@/helpers/formatter";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  item: {
    type: Object,
    required: false,
  },
  isProcessing: {
    type: Boolean,
    required: false,
  },
});

const emit = defineEmits(["update:modelValue", "save"]);

const qtyInput = ref(null);
const isUnitMenuOpen = ref(false);
const availableUnits = ref([]);
const isLoadingUnits = ref(false);

const fetchProductUnits = async () => {
  if (!props.item?.product_id) {
    availableUnits.value = [];
    return;
  }

  isLoadingUnits.value = true;
  try {
    const response = await axios.get(
      `/web-api/products/${props.item.product_id}/units`
    );

    if (response.data && response.data.data) {
      availableUnits.value = response.data.data;
    } else {
      availableUnits.value = [];
    }
  } catch (error) {
    console.error("Gagal mengambil satuan produk:", error);
    // Fallback: gunakan data yang ada di item sekarang (SAFE CHECK)
    if (props.item) {
      availableUnits.value = [
        {
          name: props.item.product_uom,
          cost: props.item.cost,
          is_base_unit: true,
        },
      ];
    }
  } finally {
    isLoadingUnits.value = false;
  }
};

// --- 2. WATCHER ---
watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      availableUnits.value = [];
      fetchProductUnits();
    }
  },
  { immediate: true }
);

// --- 3. OPTIONS & CHANGE HANDLER ---
const unitOptions = computed(() => {
  return availableUnits.value.map((u) => ({
    label: u.name,
    value: u.name,
    cost: parseFloat(u.cost || 0),
    description: u.is_base_unit ? "(Dasar)" : "",
  }));
});

const onUnitChange = (val) => {
  const selected = unitOptions.value.find((opt) => opt.value === val);

  if (selected) {
    props.item.product_uom = selected.value;
    if (selected.cost > 0) {
      props.item.cost = selected.cost;
    }
  }
};

// --- LOGIKA BAWAAN ---

const handleSave = () => {
  emit("save");
};

const subtotal = computed(() => {
  const qty = props.item?.quantity || 0;
  const cost = props.item?.cost || 0;
  return qty * cost;
});

const preventEvent = (e) => {
  e.stopPropagation();
  e.preventDefault();
};

const handleKeyDown = (e) => {
  if (isUnitMenuOpen.value) {
    return;
  }

  if (props.modelValue) {
    if (e.ctrlKey && e.key === "Enter") {
      handleSave();
      preventEvent(e);
    } else if (e.key === "Escape") {
      emit("update:modelValue", false);
      preventEvent(e);
    }
  }
};

onMounted(() => {
  window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeyDown);
});

const getCurrentItem = () => {
  return props.item;
};

defineExpose({
  getCurrentItem,
});

const onShow = () => {
  console.log(props.item);
  nextTick(() => {
    if (qtyInput.value) {
      qtyInput.value.focus?.() ||
        qtyInput.value.$el?.querySelector("input")?.focus();
    }
  });
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    @show="onShow"
  >
    <q-card style="width: 100%; max-width: 500px">
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-subtite text-bold text-grey-8">
            Edit Item Pembelian
          </div>
          <div class="col-auto">
            <q-btn
              flat
              size="sm"
              round
              icon="close"
              @click="$emit('update:modelValue', false)"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-py-sm">
        <q-input
          v-model="item.product_name"
          label="Produk"
          hide-bottom-space
          readonly
          :disable="isProcessing"
          class="q-mb-sm"
        />

        <div class="row q-col-gutter-sm">
          <div class="col-8">
            <LocaleNumberInput
              ref="qtyInput"
              v-model="item.quantity"
              label="Kuantitas"
              hide-bottom-space
              autofocus
              :disable="isProcessing"
            />
          </div>
          <div class="col-4">
            <q-select
              v-model="item.product_uom"
              :options="unitOptions"
              label="Satuan"
              hide-bottom-space
              :disable="isProcessing || isLoadingUnits"
              :loading="isLoadingUnits"
              emit-value
              map-options
              @update:model-value="onUnitChange"
              @popup-show="isUnitMenuOpen = true"
              @popup-hide="isUnitMenuOpen = false"
            >
              <template v-slot:option="scope">
                <q-item v-bind="scope.itemProps">
                  <q-item-section>
                    <q-item-label>{{ scope.opt.label }}</q-item-label>
                    <q-item-label caption>
                      Rp {{ formatNumber(scope.opt.cost) }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
          </div>
        </div>

        <LocaleNumberInput
          v-model="item.cost"
          label="Modal / Harga Beli"
          hide-bottom-space
          :disable="isProcessing"
        />

        <LocaleNumberInput
          :model-value="subtotal"
          label="Subtotal"
          hide-bottom-space
          readonly
        />

        <q-input
          v-model="item.notes"
          label="Catatan"
          autogrow
          type="textarea"
          counter
          maxlength="50"
          hide-bottom-space
          clearable
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          label="Batal"
          color="primary"
          v-close-popup
          :disable="isProcessing"
        />
        <q-btn
          flat
          label="Simpan"
          color="primary"
          @click="handleSave"
          v-close-popup
          :disable="isProcessing"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
