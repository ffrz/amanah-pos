<script setup>
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import axios from "axios";
import { formatNumber } from "@/helpers/formatter";

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  item: { type: Object, required: false },
  customer: {
    type: Object,
    required: false,
    default: () => ({ default_price_type: "price_1" }),
  },
  isProcessing: { type: Boolean, required: false },
});

const emit = defineEmits(["update:modelValue", "save"]);

// =====================
// REFS
// =====================
const qtyInput = ref(null);
const uomSelect = ref(null);
const priceInput = ref(null);
const notesInput = ref(null);
const saveBtn = ref(null);

const isUnitMenuOpen = ref(false);

// =====================
// COMPUTED & LOGIC
// =====================
const subtotal = computed(() => {
  const qty = props.item?.quantity || 0;
  const price = props.item?.price || 0;
  return qty * price;
});

const handleSave = () => {
  emit("save");
};

defineExpose({
  getCurrentItem: () => props.item,
});

const availableUnits = ref([]);
const isLoadingUnits = ref(false);

const getPriceKey = () => props.customer?.default_price_type || "price_1";

const fetchProductUnits = async () => {
  if (!props.item?.product_id) {
    availableUnits.value = [];
    return;
  }
  isLoadingUnits.value = true;
  try {
    const res = await axios.get(
      `/web-api/products/${props.item.product_id}/units`
    );
    availableUnits.value = res.data?.data || [];
  } catch {
    availableUnits.value = [
      {
        name: props.item.product_uom,
        price_1: props.item.price,
        is_base: true,
      },
    ];
  } finally {
    isLoadingUnits.value = false;
  }
};

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      availableUnits.value = [];
      fetchProductUnits();
    }
  },
  { immediate: true }
);

const unitOptions = computed(() => {
  const priceKey = getPriceKey();
  return availableUnits.value.map((u) => ({
    label: u.name,
    value: u.name,
    price: parseFloat(u[priceKey] || u.price_1 || 0),
    description: u.is_base ? "(Dasar)" : "",
  }));
});

const onUnitChange = (val) => {
  const selected = unitOptions.value.find((opt) => opt.value === val);
  if (selected) {
    props.item.product_uom = selected.value;
    props.item.price = selected.price;
  }
};

watch(
  () => props.customer?.default_price_type,
  () => {
    if (props.modelValue && props.item?.product_uom) {
      onUnitChange(props.item.product_uom);
    }
  }
);

// =====================
// GLOBAL NAVIGATION LOGIC
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

// Main Window Handler
const handleGlobalKeydown = (e) => {
  // 1. Jika Dialog Tutup, abaikan
  if (!props.modelValue) return;

  // 2. Jika Menu Dropdown Satuan sedang terbuka, biarkan Enter bekerja default (pilih item)
  if (isUnitMenuOpen.value) return;

  // 3. Handle Ctrl+Enter (Save)
  if (e.ctrlKey && e.key === "Enter") {
    e.preventDefault();
    handleSave();
    return;
  }

  // 4. Handle Navigasi Enter
  if (e.key === "Enter") {
    // A. Quantity -> UOM
    if (isFocused(qtyInput.value)) {
      e.preventDefault();
      setFocus(uomSelect.value);
      return;
    }

    // B. UOM -> Price
    if (isFocused(uomSelect.value)) {
      e.preventDefault();
      e.stopPropagation(); // Mencegah menu terbuka
      setFocus(priceInput.value);
      return;
    }

    // C. Price -> Notes
    if (isFocused(priceInput.value)) {
      e.preventDefault();
      setFocus(notesInput.value);
      return;
    }

    // D. Notes -> Save Button
    if (isFocused(notesInput.value)) {
      // Biarkan enter membuat baris baru di textarea jika mau,
      // atau preventDefault jika ingin langsung pindah.
      // e.preventDefault();
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

// Attach/Detach Event Listener
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
    @update:model-value="(v) => emit('update:modelValue', v)"
    @show="onShow"
  >
    <q-card style="width: 500px; max-width: 90vw">
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-subtitle1 text-bold text-grey-8">Edit Item</div>
          <div class="col-auto">
            <q-btn
              flat
              size="sm"
              round
              icon="close"
              @click="emit('update:modelValue', false)"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-py-sm">
        <q-input
          v-model="item.product_name"
          label="Produk"
          readonly
          hide-bottom-space
        />

        <div class="row q-col-gutter-sm">
          <div class="col-8">
            <LocaleNumberInput
              ref="qtyInput"
              v-model="item.quantity"
              label="Kuantitas"
              hide-bottom-space
              autofocus
            />
          </div>

          <div class="col-4">
            <q-select
              ref="uomSelect"
              v-model="item.product_uom"
              :options="unitOptions"
              label="Satuan"
              emit-value
              map-options
              hide-bottom-space
              :loading="isLoadingUnits"
              @update:model-value="onUnitChange"
              @popup-show="isUnitMenuOpen = true"
              @popup-hide="isUnitMenuOpen = false"
            >
              <template #option="{ itemProps, opt, selected, focused }">
                <q-item
                  v-bind="itemProps"
                  :class="{
                    'bg-grey-3': focused,
                    'bg-blue-1 text-primary': selected,
                  }"
                >
                  <q-item-section>
                    <q-item-label>{{ opt.label }}</q-item-label>
                    <q-item-label caption>
                      Rp {{ formatNumber(opt.price) }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
          </div>
        </div>

        <LocaleNumberInput
          ref="priceInput"
          v-model="item.price"
          label="Harga (Rp)"
          hide-bottom-space
          :readonly="
            !(
              item.product?.price_editable ||
              $can('admin.sales-order.editor:edit-price')
            )
          "
        />

        <LocaleNumberInput
          v-model="subtotal"
          label="Subtotal (Rp)"
          hide-bottom-space
          readonly
        />

        <q-input
          ref="notesInput"
          v-model="item.notes"
          label="Catatan"
          type="textarea"
          autogrow
          maxlength="50"
          hide-bottom-space
          clearable
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn label="Batal" v-close-popup />
        <q-btn
          ref="saveBtn"
          label="Simpan"
          color="primary"
          @click="handleSave"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
