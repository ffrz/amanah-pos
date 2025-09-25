<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit, transformPayload } from "@/helpers/client-req-handler";
import { formatNumber } from "@/helpers/formatter";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { createOptions } from "@/helpers/options";
import DateTimePicker from "@/components/DateTimePicker.vue";
import { useQuasar } from "quasar";
import { computed, ref } from "vue";
import { useProductCategoryFilter } from "@/composables/useProductCategoryFilter";
import { useSupplierFilter } from "@/composables/useSupplierFilter";

const page = usePage();
const title = "Buat Penyesuaian Stok";

// Data produk dan opsi filter dari props
const allProducts = page.props.products;

const { filteredCategories, filterCategories } = useProductCategoryFilter(
  page.props.categories
);
const { filteredSuppliers, filterSupplierFn } = useSupplierFilter(
  page.props.suppliers
);
// Model filter
const filter = ref({
  search: null,
  category_id: null,
  supplier_id: null,
});

const selectedProducts = ref([]);
const form = useForm({
  type: "stock_opname",
  datetime: new Date(),
  notes: null,
  product_ids: [],
});

const columns = [
  {
    name: "name",
    label: "Nama Produk",
    field: "name",
    align: "left",
    sortable: true,
  },
  {
    name: "stock",
    label: "Stok",
    field: "stock",
    align: "center",
    sortable: false,
    format: (val, row) => formatNumber(val) + " " + row.uom,
  },
  {
    name: "category",
    label: "Kategori",
    field: "category.name",
    align: "left",
  },
  {
    name: "supplier",
    label: "Supplier",
    field: "supplier.name",
    align: "left",
  },
  { name: "type", label: "Jenis", field: "product_type", align: "left" },
];

const types = createOptions(window.CONSTANTS.STOCK_ADJUSTMENT_TYPES);

// Logika filtering produk
const filteredProducts = computed(() => {
  return allProducts.filter((p) => {
    let matches = true;

    // Filter berdasarkan pencarian
    if (filter.value.search) {
      const searchTerm = filter.value.search.toLowerCase();
      matches =
        p.name.toLowerCase().includes(searchTerm) ||
        p.description.toLowerCase().includes(searchTerm) ||
        (p.barcode && p.barcode.toLowerCase().includes(searchTerm));
    }

    // Filter berdasarkan kategori
    if (matches && filter.value.category_id) {
      matches = p.category_id === filter.value.category_id;
    }

    // Filter berdasarkan supplier
    if (matches && filter.value.supplier_id) {
      matches = p.supplier_id === filter.value.supplier_id;
    }

    return matches;
  });
});

const submit = () => {
  form.product_ids = selectedProducts.value.map((p) => p.id);
  transformPayload(form, { datetime: "YYYY-MM-DD HH:mm:ss" });
  handleSubmit({ form, url: route("admin.stock-adjustment.create") });
};

const $q = useQuasar();
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "name");
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
          @click="router.get(route('admin.stock-adjustment.index'))"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-xs">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-sm">
              <div class="text-subtitle2"><b>Info Penyesuaian Stok</b></div>
              <date-time-picker
                v-model="form.datetime"
                label="Waktu"
                :error="!!form.errors.datetime"
                :disable="form.processing"
                :error-message="form.errors.datetime"
              />
              <q-select
                v-model="form.type"
                :options="types"
                label="Jenis Penyesuaian"
                class="custom-select"
                :error="!!form.errors.type"
                :disable="form.processing"
                map-options
                emit-value
                :error-message="form.errors.type"
                hide-bottom-space
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
                :rules="[]"
                hide-bottom-space
              />
            </q-card-section>
            <q-card-section>
              <div class="text-subtitle2"><b>Pilih Produk</b></div>
              <!-- Tambahkan baris filter di sini -->
              <div class="row q-col-gutter-sm q-mb-md">
                <q-input
                  v-model="filter.search"
                  label="Cari Produk"
                  dense
                  outlined
                  class="col-12 col-sm-4"
                  clearable
                />
                <q-select
                  v-model="filter.category_id"
                  label="Kategori"
                  class="custom-select col-xs-12 col-md-4"
                  outlined
                  use-input
                  input-debounce="300"
                  clearable
                  :options="filteredCategories"
                  map-options
                  dense
                  emit-value
                  @filter="filterCategories"
                  style="min-width: 150px"
                />
                <q-select
                  v-model="filter.supplier_id"
                  label="Pemasok"
                  class="custom-select col-xs-12 col-md-4"
                  outlined
                  use-input
                  input-debounce="300"
                  clearable
                  :options="filteredSuppliers"
                  map-options
                  dense
                  emit-value
                  @filter="filterSupplierFn"
                  style="min-width: 150px"
                />
              </div>

              <q-table
                flat
                bordered
                square
                color="primary"
                class="full-height-table"
                row-key="id"
                virtual-scroll
                :columns="computedColumns"
                :rows="filteredProducts"
                binary-state-sort
                :hide-pagination="true"
                dense
                :rows-per-page-options="[0]"
                selection="multiple"
                v-model:selected="selectedProducts"
              >
                <template v-slot:body-cell-name="props">
                  <q-td :props="props">
                    <div v-if="$q.screen.lt.md">
                      <!-- Tampilkan data khusus untuk layar kecil -->
                      <div class="text-bold">{{ props.row.name }}</div>
                      <div v-if="props.row.category">
                        {{ props.row.category.name }}
                      </div>
                      <div class="text-caption text-grey-8">
                        {{ formatNumber(props.row.stock) }} {{ props.row.uom }}
                      </div>
                    </div>
                    <div v-else>
                      {{ props.row.name }}
                    </div>
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="play_arrow"
                type="submit"
                label="Lanjutkan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="router.get(route('admin.stock-adjustment.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
