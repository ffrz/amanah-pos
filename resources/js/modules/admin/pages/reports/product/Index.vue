<script setup>
import ReportGeneratorLayout from "../ReportGeneratorLayout.vue";
import BackButton from "@/components/BackButton.vue";
import { createOptions } from "@/helpers/options";
import { usePage } from "@inertiajs/vue3";
import { useProductCategoryFilter } from "@/composables/useProductCategoryFilter";
import { useSupplierFilter } from "@/composables/useSupplierFilter";
import { useProductBrandFilter } from "@/composables/useProductBrandFilter";

const page = usePage();
const title = "Laporan Produk";

const options = page.props.options;

const { filteredBrands, filterBrands } = useProductBrandFilter(
  page.props.brands
);
const { filteredCategories, filterCategories } = useProductCategoryFilter(
  page.props.categories
);
const { filteredSuppliers, filterSupplierFn } = useSupplierFilter(
  page.props.suppliers
);

const statusOptions = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const typeOptions = createOptions(window.CONSTANTS.PRODUCT_TYPES);
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <BackButton :route="route('admin.report.index')" />
      </div>
    </template>
    <template #right-button></template>
    <ReportGeneratorLayout
      :options="options"
      routeName="admin.report.product.generate"
    >
      <template #filter="{ form }">
        <q-select
          label="Status"
          v-model="form.filter.status"
          :options="statusOptions"
          map-options
          emit-value
        />
        <q-select
          label="Jenis"
          v-model="form.filter.types"
          :options="typeOptions"
          map-options
          emit-value
          multiple
          use-chips
          clearable
        />
        <q-select
          label="Merk"
          v-model="form.filter.brands"
          :options="filteredBrands"
          @filter="filterBrands"
          map-options
          emit-value
          use-input
          clearable
          multiple
          use-chips
        />
        <q-select
          label="Kategori"
          v-model="form.filter.categories"
          :options="filteredCategories"
          @filter="filterCategories"
          map-options
          emit-value
          use-input
          clearable
          multiple
          use-chips
        />
        <q-select
          label="Supplier"
          v-model="form.filter.suppliers"
          :options="filteredSuppliers"
          @filter="filterSupplierFn"
          map-options
          emit-value
          use-input
          clearable
          multiple
          use-chips
        />
      </template>
    </ReportGeneratorLayout>
  </authenticated-layout>
</template>
