<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";
import { getCurrentMonth, getCurrentYear } from "@/helpers/datetime";
import {
  createMonthOptions,
  createOptions,
  createYearOptions,
} from "@/helpers/options";

const page = usePage();
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const currentYear = getCurrentYear();
const currentMonth = getCurrentMonth();

const years = [
  { label: "Semua Tahun", value: "all" },
  { label: `${currentYear}`, value: currentYear },
  ...createYearOptions(currentYear - 2, currentYear - 1).reverse(),
];

const months = [
  { value: "all", label: "Semua Bulan" },
  ...createMonthOptions(),
];

const ref_types = [
  {
    value: "all",
    label: "Semua",
  },
  ...createOptions(window.CONSTANTS.STOCK_MOVEMENT_REF_TYPES),
];

const title = "Riwayat Stok";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  type: "all",
  search: "",
  ...getQueryParams(),
});
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "created_at",
  descending: true,
});
let columns = [
  {
    name: "id",
    label: $q.screen.lt.sm ? "Item" : "#",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "created_at",
    label: "Waktu",
    field: "created_at",
    align: "left",
    sortable: true,
  },
  {
    name: "type",
    label: "Jenis",
    field: "type",
    align: "left",
    sortable: true,
  },
  {
    name: "product",
    label: "Produk",
    field: "product",
    align: "left",
  },
  {
    name: "quantity_before",
    label: "Qty Awal",
    field: "quantity_before",
    align: "right",
  },
  {
    name: "quantity_after",
    label: "Qty Akhir",
    field: "quantity_after",
    align: "right",
  },
  {
    name: "quantity",
    label: "Selisih",
    field: "quantity",
    align: "right",
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
];

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const pageFromUrl = urlParams.get("page");

  if (pageFromUrl) {
    pagination.value.page = parseInt(pageFromUrl);
  }
  fetchItems();
});

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.stock-movement.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  let computedColumns = [...columns];
  if ($q.screen.gt.sm) return computedColumns;
  return computedColumns.filter(
    (col) => col.name === "id" || col.name === "quantity"
  );
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        rounded
        flat
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            v-model="filter.year"
            :options="years"
            label="Tahun"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />
          <q-select
            v-if="filter.year != 'all'"
            v-model="filter.month"
            :options="months"
            label="Bulan"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            :disable="filter.year === null"
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.ref_type"
            :options="ref_types"
            label="Jenis"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />
          <q-input
            class="col"
            outlined
            dense
            debounce="300"
            v-model="filter.search"
            placeholder="Cari"
            clearable
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-xs">
      <q-table
        ref="tableRef"
        class="full-height-table"
        flat
        bordered
        square
        color="primary"
        row-key="id"
        virtual-scroll
        v-model:pagination="pagination"
        :filter="filter.search"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
        :style="{ height: tableHeight }"
        @request="fetchItems"
        binary-state-sort
      >
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>
        <template v-slot:no-data="{ icon, message, filter }">
          <div class="full-width row flex-center text-grey-8 q-gutter-sm">
            <span
              >{{ message }} {{ filter ? " with term " + filter : "" }}</span
            >
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr :props="props">
            <q-td key="id" :props="props">
              <div>
                <template v-if="$q.screen.lt.sm">
                  <div>
                    <q-badge>
                      {{
                        $CONSTANTS.STOCK_MOVEMENT_REF_TYPES[props.row.ref_type]
                      }}
                    </q-badge>
                  </div>
                  <q-icon class="inline-icon" name="tag" />
                </template>
                {{ props.row.formatted_id }}
              </div>
              <template v-if="$q.screen.lt.sm">
                <div>
                  <q-icon name="calendar_clock" class="inline-icon" />
                  {{ formatDateTime(props.row.created_at) }}
                </div>
                <div>
                  <q-icon name="token" class="inline-icon" />
                  {{ props.row.product.name }}
                </div>
                <div
                  :class="
                    props.row.quantity > 0 ? 'text-positive' : 'text-negative'
                  "
                >
                  <q-icon class="inline-icon" name="compare_arrows" />
                  {{ formatNumber(props.row.quantity_before) }}
                  {{ props.row.uom }}
                  &rarr;
                  {{ formatNumber(props.row.quantity_after) }}
                  {{ props.row.uom }}
                </div>
                <LongTextView
                  :text="props.row.notes"
                  icon="notes"
                  class="text-grey-8"
                />
              </template>
            </q-td>
            <q-td key="created_at" :props="props">
              {{ formatDateTime(props.row.created_at) }}
            </q-td>
            <q-td key="type" :props="props">
              {{ $CONSTANTS.STOCK_MOVEMENT_REF_TYPES[props.row.ref_type] }}
              <span v-if="props.row.ref">
                {{ props.row.ref }}
              </span>
            </q-td>
            <q-td key="product" :props="props">
              {{ props.row.product.name }}
            </q-td>
            <q-td key="quantity_before" :props="props">
              {{ formatNumber(props.row.quantity_before) }}
              {{ props.row.uom }}
            </q-td>
            <q-td key="quantity_after" :props="props">
              {{ formatNumber(props.row.quantity_after) }}
              {{ props.row.uom }}
            </q-td>
            <q-td
              key="quantity"
              :props="props"
              :class="
                props.row.quantity > 0 ? 'text-positive' : 'text-negative'
              "
            >
              {{ formatNumber(props.row.quantity) }}
              {{ props.row.uom }}
            </q-td>
            <q-td key="notes" :props="props">
              <LongTextView :text="props.row.notes" />
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </authenticated-layout>
</template>
