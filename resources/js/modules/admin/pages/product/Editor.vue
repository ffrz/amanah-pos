<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { createOptions } from "@/helpers/options";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import CheckBox from "@/components/CheckBox.vue";
import PercentInput from "@/components/PercentInput.vue";
import { onMounted, ref, watch } from "vue";
import BarcodeInput from "@/components/BarcodeInput.vue";
import SupplierAutocomplete from "@/components/SupplierAutocomplete.vue";
import ProductCategoryAutocomplete from "@/components/ProductCategoryAutocomplete.vue";
import UomAutocomplete from "@/components/UomAutocomplete.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Produk";
const types = createOptions(window.CONSTANTS.PRODUCT_TYPES);

// State untuk Tab
const tab = ref("general");

// Flag untuk mencegah loop tak terbatas (infinite loop) saat sinkronisasi
const isSyncing = ref(false);
const selectedSupplier = ref(page.props.data.supplier);
const form = useForm({
  id: page.props.data.id,
  category_id: page.props.data.category_id,
  supplier_id: page.props.data.supplier_id,
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
  notes: page.props.data.notes || "",
});

const submit = () => {
  handleSubmit({ form, url: route("admin.product.save") });
};

// Hitung Margin Persen (Harga Jual -> Margin)
const calculateMargin = (price, cost) => {
  price = parseFloat(price) || 0;
  cost = parseFloat(cost) || 0;

  if (cost <= 0) {
    return price > 0 ? 100 : 0;
  }
  return ((price - cost) / cost) * 100;
};

// Hitung Harga Jual (Margin -> Harga Jual)
const calculatePrice = (cost, margin) => {
  cost = parseFloat(cost) || 0;
  margin = parseFloat(margin) || 0;
  const newPrice = cost * (1 + margin / 100);
  return Math.round(newPrice);
};

watch(selectedSupplier, (newSupplierValue) => {
  form.supplier_id = newSupplierValue ? newSupplierValue.id : null;
});

watch(
  () => form.cost,
  (newCost) => {
    if (isSyncing.value) return;
    isSyncing.value = true;

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

    isSyncing.value = false;
  },
  { immediate: false }
);

// Setup Sync untuk setiap level harga
const setupPriceMarginSync = (priceIndex) => {
  const priceKey = `price_${priceIndex}`;
  const marginKey = `price_${priceIndex}_markup`;
  const optionKey = `price_${priceIndex}_option`;

  watch(
    () => form[priceKey],
    (newPrice) => {
      if (form[optionKey] == "markup") return;
      if (isSyncing.value) return;

      isSyncing.value = true;
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

for (let i = 1; i <= 3; i++) {
  setupPriceMarginSync(i);
}

onMounted(() => {
  for (let i = 1; i <= 3; i++) {
    form[`price_${i}_markup`] = calculateMargin(form[`price_${i}`], form.cost);
  }
});
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
          @click="router.get(route('admin.product.index'))"
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
      <div class="col col-md-8 q-pa-xs">
        <q-form
          class="row"
          @keydown.enter.prevent
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <input type="hidden" name="id" v-model="form.id" />

            <q-tabs
              v-model="tab"
              dense
              class="text-grey-7"
              active-color="primary"
              indicator-color="primary"
              align="justify"
              switch-indicator
            >
              <q-tab name="general" label="Umum" />
              <q-tab name="inventory" label="Inventori" />
              <q-tab name="pricing" label="Harga & Modal" />
            </q-tabs>

            <q-tab-panels v-model="tab" animated keep-alive>
              <q-tab-panel name="general">
                <div class="column q-gutter-y-md">
                  <q-select
                    autofocus
                    v-model="form.type"
                    class="custom-select"
                    :options="types"
                    label="Jenis Produk"
                    map-options
                    emit-value
                    hide-bottom-space
                  />

                  <q-input
                    v-model.trim="form.name"
                    label="Kode / Nama Produk"
                    lazy-rules
                    :error="!!form.errors.name"
                    :disable="form.processing"
                    :error-message="form.errors.name"
                    :rules="[
                      (val) => (val && val.length > 0) || 'Nama harus diisi.',
                    ]"
                    hide-bottom-space
                  />

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

                  <ProductCategoryAutocomplete
                    v-model:modelValue="form.category_id"
                    :categories="page.props.categories"
                    label="Kategori (Opsional)"
                    :error="!!form.errors.category_id"
                    :disable="form.processing"
                    :error-message="form.errors.category_id"
                  />

                  <SupplierAutocomplete
                    v-model="selectedSupplier"
                    label="Supplier Default (Opsional)"
                    :error="!!form.errors.supplier_id"
                    :error-message="form.errors.supplier_id"
                    :disable="form.processing"
                    option-label="label"
                    option-value="value"
                  />

                  <q-input
                    v-model.trim="form.notes"
                    type="textarea"
                    autogrow
                    counter
                    maxlength="200"
                    label="Catatan Internal"
                    lazy-rules
                    :disable="form.processing"
                    :error="!!form.errors.notes"
                    :error-message="form.errors.notes"
                    hide-bottom-space
                  />

                  <CheckBox
                    v-model="form.active"
                    :disable="form.processing"
                    label="Produk Aktif (Ditampilkan)"
                  />
                </div>
              </q-tab-panel>

              <q-tab-panel name="inventory">
                <div class="column q-gutter-y-md">
                  <div class="row q-gutter-md">
                    <div class="col">
                      <UomAutocomplete
                        v-model:modelValue="form.uom"
                        :items="page.props.uoms"
                        label="Satuan"
                        :error="!!form.errors.uom"
                        :disable="form.processing"
                        :error-message="form.errors.uom"
                      />
                    </div>
                    <div class="col">
                      <BarcodeInput
                        v-model.trim="form.barcode"
                        label="Barcode / SKU"
                      />
                    </div>
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
                      label="Stok Minimum (Alert)"
                      lazyRules
                      :disable="form.processing"
                      :error="!!form.errors.min_stock"
                      :errorMessage="form.errors.min_stock"
                      hide-bottom-space
                      class="col"
                    />
                  </div>
                </div>
              </q-tab-panel>

              <q-tab-panel name="pricing">
                <div class="column q-gutter-y-md">
                  <LocaleNumberInput
                    v-if="$can('admin.product:view-cost')"
                    v-model:modelValue="form.cost"
                    label="Modal / Harga Beli (Rp)"
                    lazyRules
                    :disable="form.processing"
                    :error="!!form.errors.cost"
                    :errorMessage="form.errors.cost"
                    hide-bottom-space
                    bg-color="yellow-1"
                  />

                  <q-banner dense class="bg-blue-1 text-blue-9 rounded-borders">
                    Atur skema harga jual berdasarkan Markup (%) atau Nominal
                    Rupiah.
                  </q-banner>

                  <table class="q-mt-sm full-width">
                    <thead class="text-grey-7 text-left">
                      <tr>
                        <th style="width: 25%">Level Harga</th>
                        <th style="width: 25%">Markup (%)</th>
                        <th>Opsi</th>
                        <th style="width: 25%">Harga Jual (Rp)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="text-weight-bold">Harga Eceran</td>
                        <td>
                          <PercentInput
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
                        <td class="q-px-sm">
                          <q-btn-toggle
                            v-model="form.price_1_option"
                            dense
                            spread
                            no-caps
                            rounded
                            unelevated
                            toggle-color="primary"
                            color="grey-3"
                            text-color="grey-9"
                            :options="[
                              { label: '%', value: 'markup' },
                              { label: 'Rp', value: 'price' },
                            ]"
                          />
                        </td>
                        <td>
                          <LocaleNumberInput
                            filled
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
                        <td class="text-weight-bold">Harga Partai</td>
                        <td>
                          <PercentInput
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
                        <td class="q-px-sm">
                          <q-btn-toggle
                            v-model="form.price_2_option"
                            dense
                            spread
                            no-caps
                            rounded
                            unelevated
                            toggle-color="primary"
                            color="grey-3"
                            text-color="grey-9"
                            :options="[
                              { label: '%', value: 'markup' },
                              { label: 'Rp', value: 'price' },
                            ]"
                          />
                        </td>
                        <td>
                          <LocaleNumberInput
                            filled
                            dense
                            v-model:modelValue="form.price_2"
                            lazyRules
                            :disable="form.processing"
                            :readonly="form.price_2_option == 'markup'"
                            :error="!!form.errors.price_2"
                            :errorMessage="form.errors.price_2"
                            hide-bottom-space
                          />
                        </td>
                      </tr>

                      <tr>
                        <td class="text-weight-bold">Harga Grosir</td>
                        <td>
                          <PercentInput
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
                        <td class="q-px-sm">
                          <q-btn-toggle
                            v-model="form.price_3_option"
                            dense
                            spread
                            no-caps
                            rounded
                            unelevated
                            toggle-color="primary"
                            color="grey-3"
                            text-color="grey-9"
                            :options="[
                              { label: '%', value: 'markup' },
                              { label: 'Rp', value: 'price' },
                            ]"
                          />
                        </td>
                        <td>
                          <LocaleNumberInput
                            filled
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

                  <CheckBox
                    class="q-mt-sm"
                    v-model="form.price_editable"
                    :disable="form.processing"
                    label="Izinkan kasir mengubah harga manual saat penjualan"
                  />
                </div>
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
