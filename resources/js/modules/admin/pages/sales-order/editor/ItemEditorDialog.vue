<script setup>
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import axios from "axios";

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  item: { type: Object, required: false },
  // Tambahkan Customer untuk mendeteksi default_price_type
  customer: {
    type: Object,
    required: false,
    default: () => ({ default_price_type: "price_1" }),
  },
  isProcessing: { type: Boolean, required: false },
});

const emit = defineEmits(["update:modelValue", "save"]);

// --- STATE ---
const availableUnits = ref([]);
const isLoadingUnits = ref(false);

// --- HELPER: Tentukan kolom harga mana yang dipakai ---
const getPriceKey = () => {
  // Asumsi: customer.default_price_type bernilai 'price_1', 'price_2', atau 'price_3'
  // Jika customer null atau tidak punya setting, default ke 'price_1'
  return props.customer?.default_price_type || "price_1";
};

// --- FETCH DATA ---
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
    if (response.data?.data) {
      availableUnits.value = response.data.data;
    } else {
      availableUnits.value = [];
    }
  } catch (error) {
    console.error("Error fetching units:", error);
    // Fallback darurat
    availableUnits.value = [
      {
        name: props.item.product_uom,
        price_1: props.item.price, // Asumsi harga saat ini masuk price_1
        is_base: true,
      },
    ];
  } finally {
    isLoadingUnits.value = false;
  }
};

// --- WATCHER ---
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

// --- LOGIC UTAMA: Mapping Harga Berdasarkan Customer ---
const unitOptions = computed(() => {
  const priceKey = getPriceKey(); // Misal: 'price_2'

  return availableUnits.value.map((u) => {
    // Ambil harga dinamis sesuai tipe customer
    // Jika harga tipe tersebut 0 atau null, fallback ke price_1
    const finalPrice = parseFloat(u[priceKey] || u["price_1"] || 0);

    return {
      label: u.name,
      value: u.name,
      price: finalPrice,
      description: u.is_base ? "(Dasar)" : "",
    };
  });
});

// --- LOGIKA GANTI SATUAN ---
const onUnitChange = (val) => {
  const selected = unitOptions.value.find((opt) => opt.value === val);
  if (selected) {
    props.item.product_uom = selected.value;

    // PENTING: Jika user manual ganti satuan, harga otomatis ikut berubah
    // sesuai kategori customer yang sedang aktif
    props.item.price = selected.price;
  }
};

// ... sisa logic save, subtotal, onMounted sama seperti sebelumnya ...

// --- Tambahan Logic Watch Customer (Opsional) ---
// Jika tiba-tiba object customer berubah saat dialog sedang terbuka
// maka harga di "unitOptions" akan otomatis berubah (reactive),
// TAPI harga di "props.item.price" (yang sudah terpilih) tidak otomatis berubah
// kecuali kita paksa update seperti ini:

watch(
  () => props.customer?.default_price_type,
  () => {
    // Refresh harga item yang sedang diedit jika tipe harga customer berubah
    if (props.modelValue && props.item.product_uom) {
      onUnitChange(props.item.product_uom);
    }
  }
);
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
  >
    <q-card>
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-subtite text-bold text-grey-8">Edit Item</div>
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
        />

        <div class="row q-col-gutter-sm">
          <div class="col-8">
            <LocaleNumberInput
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
            >
              <template v-slot:option="scope">
                <q-item v-bind="scope.itemProps">
                  <q-item-section>
                    <q-item-label>{{ scope.opt.label }}</q-item-label>
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
          </div>
        </div>

        <LocaleNumberInput
          v-model="item.price"
          label="Harga (Rp)"
          :readonly="
            !(
              item.product?.price_editable ||
              $can('admin.sales-order.editor:edit-price')
            )
          "
          hide-bottom-space
          :disable="isProcessing"
        />

        <LocaleNumberInput
          v-model="subtotal"
          label="Subtotal (Rp)"
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
