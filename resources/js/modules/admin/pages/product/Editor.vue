<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useProductCategoryFilter } from "@/composables/useProductCategoryFilter";
import { useSupplierFilter } from "@/composables/useSupplierFilter";
import { createOptions } from "@/helpers/options";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import CheckBox from "@/components/CheckBox.vue";
import PercentInput from "@/components/PercentInput.vue";
import { onMounted, ref, watch } from "vue"; // <-- Import ref dan watch

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Produk";
const types = createOptions(window.CONSTANTS.PRODUCT_TYPES);

// Flag untuk mencegah loop tak terbatas (infinite loop) saat sinkronisasi
const isSyncing = ref(false);

const form = useForm({
  id: page.props.data.id,
  category_id: page.props.data.category_id,
  supplier_id: page.props.data.supplier_id,
  // tax_scheme_id: page.props.data.tax_scheme_id,
  type: page.props.data.type || "",
  name: page.props.data.name || "",
  description: page.props.data.description || "",
  stock: parseFloat(page.props.data.stock) || 0,
  min_stock: parseFloat(page.props.data.min_stock) || 0,
  max_stock: parseFloat(page.props.data.max_stock) || 0,

  uom: page.props.data.uom || "",
  barcode: page.props.data.barcode || "",

  cost: parseFloat(page.props.data.cost) || 0,
  price_1: parseFloat(page.props.data.price_1) || 0,
  price_2: parseFloat(page.props.data.price_2) || 0,
  price_3: parseFloat(page.props.data.price_3) || 0,

  // Set nilai awal margin berdasarkan perhitungan di atas
  price_1_markup: parseFloat(page.props.data.price_1_markup),
  price_2_markup: parseFloat(page.props.data.price_2_markup),
  price_3_markup: parseFloat(page.props.data.price_3_markup),

  price_1_option: page.props.data.price_1_option ?? "price",
  price_2_option: page.props.data.price_2_option ?? "price",
  price_3_option: page.props.data.price_3_option ?? "price",

  active: !!page.props.data.active,
  price_editable: !!page.props.data.price_editable,
  // tax_enabled: !!page.props.data.tax_enabled,
  notes: page.props.data.notes || "",
});

const submit = () => handleSubmit({ form, url: route("admin.product.save") });

const { filteredCategories, filterCategories } = useProductCategoryFilter(
  page.props.categories
);
const { filteredSuppliers, filterSupplierFn } = useSupplierFilter(
  page.props.suppliers
);

// --- LOGIKA SINKRONISASI HARGA JUAL DAN MARGIN ---

// Hitung Margin Persen (Harga Jual -> Margin)
const calculateMargin = (price, cost) => {
  price = parseFloat(price) || 0;
  cost = parseFloat(cost) || 0;

  if (cost <= 0) {
    // Jika modal 0 atau negatif, dan harga jual positif, anggap 100% (atau tinggi sekali)
    return price > 0 ? 100 : 0;
  }

  // M = ((P - C) / C) * 100
  return ((price - cost) / cost) * 100;
};

// Hitung Harga Jual (Margin -> Harga Jual)
const calculatePrice = (cost, margin) => {
  cost = parseFloat(cost) || 0;
  margin = parseFloat(margin) || 0;

  // P = C * (1 + M/100)
  const newPrice = cost * (1 + margin / 100);
  // Harga jual di-round ke bilangan bulat (integer)
  return Math.round(newPrice);
};

// 1. Master Watcher: Ketika COST berubah, hitung ulang semua HARGA JUAL berdasarkan margin % yang sudah ada
watch(
  () => form.cost,
  (newCost) => {
    if (isSyncing.value) return;

    // Set flag
    isSyncing.value = true;

    // Recalculate all prices based on current margin markup
    if (form.price_1_option == "markup") {
      form.price_1 = calculatePrice(newCost, form.price_1_markup);
    } else {
      form.price_1_markup = calculateMargin(form.price_1, newCost);
    }

    if (form.price_2_option == "markup") {
      form.price_2 = calculatePrice(newCost, form.price_2_markup);
    } else {
      form.price_2_markup = calculateMargin(form.price_2, newCost);
    }

    if (form.price_3_option == "markup") {
      form.price_3 = calculatePrice(newCost, form.price_3_markup);
    } else {
      form.price_3_markup = calculateMargin(form.price_3, newCost);
    }

    // Reset flag setelah selesai
    isSyncing.value = false;
  },
  { immediate: false }
);

// 2. Setup Sync untuk setiap level harga
const setupPriceMarginSync = (priceIndex) => {
  const priceKey = `price_${priceIndex}`;
  const marginKey = `price_${priceIndex}_markup`;
  const optionKey = `price_${priceIndex}_option`;

  // Watcher A: Harga Jual berubah -> Margin Persen diperbarui
  watch(
    () => form[priceKey],
    (newPrice) => {
      if (form[optionKey] == "markup") return;
      if (isSyncing.value) return; // Hentikan jika sedang dalam proses sinkronisasi

      isSyncing.value = true;

      // Hitung margin baru dan update form
      const newMargin = calculateMargin(newPrice, form.cost);
      form[marginKey] = parseFloat(newMargin.toFixed(2));

      isSyncing.value = false;
    },
    { deep: false }
  );

  watch(
    () => form[marginKey],
    (newMargin) => {
      if (form[optionKey] == "price") return;
      if (isSyncing.value) return;
      isSyncing.value = true;
      form[priceKey] = calculatePrice(form.cost, newMargin);
      isSyncing.value = false;
    },
    { deep: false }
  );
};

// // Aplikasikan sinkronisasi untuk Harga 1, 2, dan 3
for (let i = 1; i <= 3; i++) {
  setupPriceMarginSync(i);
}

onMounted(() => {
  for (let i = 1; i <= 3; i++) {
    form[`price_${i}_markup`] = calculateMargin(form[`price_${i}`], form.cost);
  }
});

// --- END LOGIKA SINKRONISASI ---
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$goBack()"
        />
      </div>
    </template>
    <template #right-button>
      <q-btn
        class="q-ml-xs"
        type="submit"
        icon="check"
        rounded
        dense
        color="primary"
        :disable="form.processing"
        @click="submit()"
        title="Simpan"
      />
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <q-form
          class="row"
          @keydown.enter.prevent
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <div class="text-subtitle1 q-pt-lg">Info Utama</div>
              <q-select
                :disabled="!!form.id"
                autofocus
                v-model="form.type"
                class="custom-select"
                :options="types"
                label="Jenis"
                map-options
                emit-value
                hide-bottom-space
              />
              <div class="text-warning text-italic">
                Mengganti tipe setelah produk digunakan dapat mempengaruhi stok
              </div>
              <q-input
                v-model.trim="form.name"
                label="Nama Produk"
                lazy-rules
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
                hide-bottom-space
              />
              <div class="text-warning text-italic">
                Mengganti kode/nama setelah produk digunakan dapat mempengaruhi
                konsistensi data.
              </div>
              <q-select
                v-model="form.category_id"
                label="Kategori (Opsional)"
                use-input
                input-debounce="300"
                clearable
                :options="filteredCategories"
                map-options
                emit-value
                @filter="filterCategories"
                option-label="label"
                option-value="value"
                :error="!!form.errors.category_id"
                :disable="form.processing"
                hide-bottom-space
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Kategori tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>

              <q-select
                v-if="$can('admin.product:view-supplier')"
                v-model="form.supplier_id"
                label="Supplier Default (Opsional)"
                use-input
                input-debounce="300"
                clearable
                :options="filteredSuppliers"
                map-options
                emit-value
                @filter="filterSupplierFn"
                option-label="label"
                option-value="value"
                :error="!!form.errors.supplier_id"
                :disable="form.processing"
                hide-bottom-space
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Supplier tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>

              <q-input
                v-model.trim="form.description"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Deskripsi (Opsional)"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.description"
                :error-message="form.errors.description"
                hide-bottom-space
              />

              <CheckBox
                class="q-mt-md"
                v-model="form.active"
                :disable="form.processing"
                label="Produk Aktif (Ditampilkan)"
              />
              <div class="text-subtitle1 q-pt-lg">Info Inventori</div>
              <div class="row q-gutter-md">
                <q-input
                  v-model.trim="form.uom"
                  label="Satuan"
                  lazy-rules
                  :error="!!form.errors.uom"
                  :disable="form.processing"
                  :error-message="form.errors.uom"
                  hide-bottom-space
                  class="col"
                />
                <q-input
                  v-model.trim="form.barcode"
                  label="Barcode"
                  lazy-rules
                  :error="!!form.errors.barcode"
                  :disable="form.processing"
                  :error-message="form.errors.barcode"
                  hide-bottom-space
                  class="col"
                />
              </div>

              <div class="row q-gutter-md">
                <LocaleNumberInput
                  v-model:modelValue="form.stock"
                  label="Stok Aktual"
                  lazyRules
                  :disable="form.processing"
                  :error="!!form.errors.stock"
                  :errorMessage="form.errors.stock"
                  hide-bottom-space
                  class="col"
                />
                <LocaleNumberInput
                  v-model:modelValue="form.min_stock"
                  label="Stok Min"
                  lazyRules
                  :disable="form.processing"
                  :error="!!form.errors.min_stock"
                  :errorMessage="form.errors.min_stock"
                  hide-bottom-space
                  class="col"
                />
                <LocaleNumberInput
                  v-model:modelValue="form.max_stock"
                  label="Stok Maks"
                  lazyRules
                  :disable="form.processing"
                  :error="!!form.errors.max_stock"
                  :errorMessage="form.errors.max_stock"
                  hide-bottom-space
                  class="col"
                />
              </div>
              <div class="text-subtitle1 q-pt-lg">Harga & Modal</div>
              <LocaleNumberInput
                v-if="$can('admin.product:view-cost')"
                v-model:modelValue="form.cost"
                label="Modal / Harga Beli (Rp)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.cost"
                :errorMessage="form.errors.cost"
                hide-bottom-space
              />
              <table class="q-mt-md full-width">
                <thead class="text-grey-6">
                  <tr>
                    <th style="width: 25%">Harga</th>
                    <th style="width: 25%">Markup (%)</th>
                    <th></th>
                    <th style="width: 25%">Harga Jual (Rp)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="no-wrap">Harga Eceran</td>
                    <td>
                      <PercentInput
                        class="col-3"
                        filled
                        dense
                        v-model:modelValue="form.price_1_markup"
                        lazyRules
                        :readonly="form.price_1_option != 'markup'"
                        :disable="form.processing"
                        :error="!!form.errors.price_1_markup"
                        :errorMessage="form.errors.price_1_markup"
                        hide-bottom-space
                        :max-decimals="2"
                      />
                    </td>
                    <td>
                      <q-btn-toggle
                        v-model="form.price_1_option"
                        dense
                        spread
                        class="col-3"
                        no-caps
                        rounded
                        toggle-color="primary"
                        color="white"
                        text-color="primary"
                        :options="[
                          { label: '%', value: 'markup' },
                          { label: 'Rp', value: 'price' },
                        ]"
                      />
                    </td>
                    <td>
                      <LocaleNumberInput
                        filled
                        class="col-3"
                        dense
                        v-model:modelValue="form.price_1"
                        lazyRules
                        :disable="form.processing"
                        :readonly="form.price_1_option == 'markup'"
                        :error="!!form.errors.price_1"
                        :errorMessage="form.errors.price_1"
                        hide-bottom-space
                      />
                    </td>
                  </tr>
                  <tr>
                    <td class="no-wrap">Harga Partai</td>
                    <td>
                      <PercentInput
                        class="col-3"
                        filled
                        dense
                        v-model:modelValue="form.price_2_markup"
                        lazyRules
                        :readonly="form.price_2_option != 'markup'"
                        :disable="form.processing"
                        :error="!!form.errors.price_2_markup"
                        :errorMessage="form.errors.price_2_markup"
                        hide-bottom-space
                        :max-decimals="2"
                      />
                    </td>
                    <td>
                      <q-btn-toggle
                        v-model="form.price_2_option"
                        dense
                        spread
                        class="col-3"
                        no-caps
                        rounded
                        toggle-color="primary"
                        color="white"
                        text-color="primary"
                        :options="[
                          { label: '%', value: 'markup' },
                          { label: 'Rp', value: 'price' },
                        ]"
                      />
                    </td>
                    <td>
                      <LocaleNumberInput
                        filled
                        class="col-3"
                        dense
                        v-model:modelValue="form.price_2"
                        lazyRules
                        :readonly="form.price_2_option == 'markup'"
                        :disable="form.processing"
                        :error="!!form.errors.price_2"
                        :errorMessage="form.errors.price_2"
                        hide-bottom-space
                      />
                    </td>
                  </tr>
                  <tr>
                    <td class="no-wrap">Harga Grosir</td>
                    <td>
                      <PercentInput
                        class="col-3"
                        filled
                        dense
                        v-model:modelValue="form.price_3_markup"
                        lazyRules
                        :disable="form.processing"
                        :readonly="form.price_3_option != 'markup'"
                        :error="!!form.errors.price_3_markup"
                        :errorMessage="form.errors.price_3_markup"
                        hide-bottom-space
                        :max-decimals="2"
                      />
                    </td>
                    <td>
                      <q-btn-toggle
                        v-model="form.price_3_option"
                        dense
                        spread
                        class="col-3"
                        no-caps
                        rounded
                        toggle-color="primary"
                        color="white"
                        text-color="primary"
                        :options="[
                          { label: '%', value: 'markup' },
                          { label: 'Rp', value: 'price' },
                        ]"
                      />
                    </td>
                    <td>
                      <LocaleNumberInput
                        filled
                        class="col-3"
                        dense
                        v-model:modelValue="form.price_3"
                        lazyRules
                        :disable="form.processing"
                        :readonly="form.price_3_option == 'markup'"
                        :error="!!form.errors.price_3"
                        :errorMessage="form.errors.price_3"
                        hide-bottom-space
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- bisa diaktifkan nanti -->
              <!-- <CheckBox
                class="q-mt-sm"
                v-model="form.autoupdate_price"
                :disable="form.processing"
                label="Update harga jual jika modal berubah"
              /> -->

              <CheckBox
                class="q-mt-sm"
                v-model="form.price_editable"
                :disable="form.processing"
                label="Harga dapat diubah saat penjualan"
              />

              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                hide-bottom-space
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
