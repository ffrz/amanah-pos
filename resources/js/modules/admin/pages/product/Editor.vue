<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { computed, ref } from "vue";
import { useProductCategoryFilter } from "@/composables/useProductCategoryFilter";
import { useSupplierFilter } from "@/composables/useSupplierFilter";
import { createOptions } from "@/helpers/options";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import CheckBox from "@/components/CheckBox.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Produk";
const types = createOptions(window.CONSTANTS.PRODUCT_TYPES);

const form = useForm({
  id: page.props.data.id,
  category_id: page.props.data.category_id,
  supplier_id: page.props.data.supplier_id,
  type: page.props.data.type || "",
  name: page.props.data.name || "",
  barcode: page.props.data.barcode || "",
  description: page.props.data.description || "",
  stock: parseFloat(page.props.data.stock) || 0,
  min_stock: parseFloat(page.props.data.min_stock) || 0,
  max_stock: parseFloat(page.props.data.max_stock) || 0,
  uom: page.props.data.uom || "",
  cost: parseFloat(page.props.data.cost) || 0,
  price_1: parseFloat(page.props.data.price_1) || 0,
  price_2: parseFloat(page.props.data.price_2) || 0,
  price_3: parseFloat(page.props.data.price_3) || 0,
  active: !!page.props.data.active,
  price_editable: !!page.props.data.price_editable,
  notes: page.props.data.notes || "",
});

const submit = () => handleSubmit({ form, url: route("admin.product.save") });

const { filteredCategories, filterCategories } = useProductCategoryFilter(
  page.props.categories
);
const { filteredSuppliers, filterSuppliers } = useSupplierFilter(
  page.props.suppliers
);

const margin = computed(() => {
  return form.price_1 > 0
    ? ((form.price_1 - form.cost) / form.price_1) * 100
    : 0;
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
          @click="$goBack()"
        />
      </div>
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
                autofocus
                v-model="form.type"
                class="custom-select"
                :options="types"
                label="Jenis"
                map-options
                emit-value
                hide-bottom-space
              />
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
              <q-input
                v-model.trim="form.description"
                type="textarea"
                autogrow
                counter
                maxlength="200"
                label="Deskripsi"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.description"
                :error-message="form.errors.description"
                hide-bottom-space
              />
              <q-select
                v-model="form.category_id"
                label="Kategori"
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
                label="Supplier"
                use-input
                input-debounce="300"
                clearable
                :options="filteredSuppliers"
                map-options
                emit-value
                @filter="filterSuppliers"
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
              <CheckBox
                v-model="form.active"
                :disable="form.processing"
                label="Aktif"
              />
              <div class="text-subtitle1 q-pt-lg">Info Inventori</div>
              <q-input
                v-model.trim="form.barcode"
                label="Barcode"
                lazy-rules
                :error="!!form.errors.barcode"
                :disable="form.processing"
                :error-message="form.errors.barcode"
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.uom"
                label="Satuan"
                lazy-rules
                :error="!!form.errors.uom"
                :disable="form.processing"
                :error-message="form.errors.uom"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.stock"
                label="Stok"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.stock"
                :errorMessage="form.errors.stock"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.min_stock"
                label="Stok Minimum"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.min_stock"
                :errorMessage="form.errors.min_stock"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.max_stock"
                label="Stok Maksimum"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.max_stock"
                :errorMessage="form.errors.max_stock"
                hide-bottom-space
              />
              <div class="text-subtitle1 q-pt-lg">Info Harga</div>
              <CheckBox
                v-model="form.price_editable"
                :disable="form.processing"
                label="Harga bisa diubah saat penjualan"
              />
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
              <LocaleNumberInput
                v-model:modelValue="form.price_1"
                label="Harga Eceran (Rp)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.price_1"
                :errorMessage="form.errors.price_1"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.price_2"
                label="Harga Partai (Rp)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.price_2"
                :errorMessage="form.errors.price_2"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.price_3"
                label="Harga Grosir (Rp)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.price_3"
                :errorMessage="form.errors.price_3"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-if="$can('admin.product:view-cost')"
                v-model:modelValue="margin"
                label="Margin (%)"
                lazyRules
                :disable="form.processing"
                :maxDecimals="2"
                hide-bottom-space
              />
              <div class="text-subtitle1 q-pt-lg">Info Lainnya</div>
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
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="$goBack()"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
