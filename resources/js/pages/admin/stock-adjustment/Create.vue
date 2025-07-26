<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import {
  create_options,
  formatNumber,
  scrollToFirstErrorField,
} from "@/helpers/utils";
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";
import { useQuasar } from "quasar";
import { computed, ref } from "vue";

const page = usePage();
const title = "Buat Penyesuaian Stok";
const products = page.props.products;
const selectedProducts = ref([]);
const form = useForm({
  type: "stock_opname",
  datetime: dayjs().format("YYYY-MM-DD HH:mm:ss"),
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
];

const types = create_options(window.CONSTANTS.STOCKADJUSTMENT_TYPES);

const submit = () => {
  form.product_ids = selectedProducts.value.map((p) => p.id);
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
  <authenticated-layout>
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
      <div class="col col-lg-6 q-pa-sm">
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
              />
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="1000"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                :rules="[]"
              />
            </q-card-section>
            <q-card-section>
              <div class="text-subtitle2"><b>Pilih Produk</b></div>
              <q-table
                flat
                bordered
                square
                color="primary"
                class="full-height-table"
                row-key="id"
                virtual-scroll
                :columns="computedColumns"
                :rows="products"
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
