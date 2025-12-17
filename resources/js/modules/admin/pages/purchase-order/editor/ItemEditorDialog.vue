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

// =====================
// REFS (Untuk Navigasi)
// =====================
const qtyInput = ref(null);
const uomSelect = ref(null);
const costInput = ref(null); // Ganti priceInput jadi costInput (karena pembelian)
const notesInput = ref(null);
const saveBtn = ref(null);

const isUnitMenuOpen = ref(false);

// =====================
// DATA & LOGIC
// =====================
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

const handleSave = () => {
  emit("save");
};

const subtotal = computed(() => {
  const qty = props.item?.quantity || 0;
  const cost = props.item?.cost || 0;
  return qty * cost;
});

const getCurrentItem = () => {
  return props.item;
};

defineExpose({
  getCurrentItem,
});

// =====================
// NAVIGATION HELPERS
// =====================

// Helper: Cek apakah Ref sedang fokus
const isFocused = (componentRef) => {
  if (!componentRef) return false;
  const el = componentRef.$el || componentRef;
  return el.contains(document.activeElement) || el === document.activeElement;
};

// Helper: Pindah Fokus
const setFocus = (componentRef) => {
  if (!componentRef) return;
  if (typeof componentRef.focus === "function") {
    componentRef.focus();
  } else if (componentRef.$el) {
    const input = componentRef.$el.querySelector("input, textarea, button");
    if (input) input.focus();
    else componentRef.$el.focus();
  }
};

// =====================
// GLOBAL KEYDOWN HANDLER
// =====================
const handleGlobalKeydown = (e) => {
  // 1. Jika Dialog Tutup, abaikan
  if (!props.modelValue) return;

  // 2. Jika Menu Dropdown Satuan sedang terbuka, biarkan Enter bekerja default (pilih item)
  if (isUnitMenuOpen.value) return;

  // 3. Handle Ctrl+Enter (Save)
  if (e.ctrlKey && e.key === "Enter") {
    e.preventDefault();
    e.stopPropagation(); // Stop event agar tidak double trigger
    handleSave();
    return;
  }

  // 4. Handle Escape (Close)
  if (e.key === "Escape") {
    // Jika menu unit tertutup, baru tutup dialog
    e.preventDefault();
    emit("update:modelValue", false);
    return;
  }

  // 5. Handle Navigasi Enter
  if (e.key === "Enter") {
    // A. Quantity -> UOM
    if (isFocused(qtyInput.value)) {
      e.preventDefault();
      setFocus(uomSelect.value);
      return;
    }

    // B. UOM -> Cost (Navigasi Kunci)
    if (isFocused(uomSelect.value)) {
      e.preventDefault(); // Mencegah menu terbuka
      e.stopPropagation(); // Mencegah bubbling
      setFocus(costInput.value);
      return;
    }

    // C. Cost -> Notes
    if (isFocused(costInput.value)) {
      e.preventDefault();
      setFocus(notesInput.value);
      return;
    }

    // D. Notes -> Save Button
    if (isFocused(notesInput.value)) {
      // e.preventDefault(); // Uncomment jika tidak ingin enter membuat baris baru di notes
      setFocus(saveBtn.value);
      return;
    }

    // E. Save Button -> Submit
    if (isFocused(saveBtn.value)) {
      e.preventDefault();
      handleSave();
      return;
    }
  }
};

// Attach Event Listener dengan mode CAPTURE (true)
onMounted(() => {
  window.addEventListener("keydown", handleGlobalKeydown, true);
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleGlobalKeydown, true);
});

const onShow = () => {
  nextTick(() => {
    setFocus(qtyInput.value);
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
              ref="uomSelect"
              v-model="item.product_uom"
              :options="unitOptions"
              label="Satuan"
              hide-bottom-space
              :disable="isProcessing || isLoadingUnits"
              :loading="isLoadingUnits"
              emit-value
              map-options
              behavior="dialog"
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
          ref="costInput"
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
          ref="notesInput"
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
        <q-btn flat label="Batal" v-close-popup :disable="isProcessing" />
        <q-btn
          ref="saveBtn"
          label="Simpan"
          color="primary"
          @click="handleSave"
          :disable="isProcessing"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
