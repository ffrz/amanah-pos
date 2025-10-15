<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import {
  formatDateTime,
  formatDateTimeFromNow,
  formatNumber,
  plusMinusSymbol,
} from "@/helpers/formatter";
import { createMonthOptions, createYearOptions } from "@/helpers/options";
import { getCurrentMonth, getCurrentYear } from "@/helpers/datetime";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";

const title = "Riwayat Pembelian";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const currentYear = getCurrentYear();
const yearOptions = createYearOptions(currentYear - 2, currentYear, true, true);
const monthOptions = createMonthOptions(true);

const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);

const filter = reactive({
  search: "",
  year: currentYear,
  month: getCurrentMonth(),
  ...getQueryParams(),
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "datetime",
  descending: true,
});

const columns = [
  {
    name: "expand",
    label: "",
    field: "expand",
    align: "left",
  },
  {
    name: "id",
    label: $q.screen.lt.md ? "Item" : "Kode Trx",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "datetime",
    label: "Waktu",
    field: "datetime",
    align: "left",
    sortable: true,
  },
  {
    name: "grand_total",
    label: "Jumlah (Rp.)",
    field: "grand_total",
    align: "right",
  },
  { name: "notes", label: "Catatan", field: "notes", align: "left" },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("customer.purchasing-history.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) =>
    ["id", "action", "expand", "grand_total"].includes(col.name)
  );
});

watch(
  () => filter.year,
  (newVal) => {
    if (newVal === null) {
      filter.month = null;
    }
  }
);

const onRowClicked = (row) => {
  router.get(route("customer.purchasing-history.detail", { id: row.id }));
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        class="q-ml-sm"
        size="sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        flat
        rounded
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            v-model="filter.year"
            :options="yearOptions"
            label="Tahun"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />
          <q-select
            v-if="filter.year !== 'all'"
            v-model="filter.month"
            :options="monthOptions"
            label="Bulan"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            :disable="filter.year === null"
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
        v-model:pagination="pagination"
        row-key="id"
        :filter="filter.search"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
        @request="fetchItems"
        binary-state-sort
        :pagination="{ rowsPerPage: 0 }"
        :style="{ height: tableHeight }"
        color="primary"
        flat
        bordered
        square
      >
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>
        <template v-slot:no-data="{ icon, message, filter }">
          <div class="full-width row flex-center text-grey-8 q-gutter-sm">
            <span>
              {{ message }}
              {{ filter ? " with term " + filter : "" }}</span
            >
          </div>
        </template>
        <template v-slot:body="props">
          <q-tr
            :props="props"
            @click.stop="onRowClicked(props.row)"
            class="cursor-pointer"
          >
            <q-td auto-width @click.stop="props.expand = !props.expand">
              <q-btn
                size="xs"
                color="grey"
                round
                dense
                :icon="
                  props.expand ? 'keyboard_arrow_up' : 'keyboard_arrow_down'
                "
              />
            </q-td>
            <q-td key="id" :props="props" class="wrap-column">
              <template v-if="!$q.screen.gt.sm">
                <q-icon name="tag" class="inline-icon" />
                {{ props.row.code }}
              </template>
              <div>
                <template v-if="!$q.screen.gt.sm">
                  <q-icon name="calendar_today" class="inline-icon" />
                </template>
                <q-tooltip>
                  {{ formatDateTimeFromNow(props.row.datetime) }}
                </q-tooltip>
                {{ formatDateTime(props.row.datetime) }}
              </div>

              <template v-if="!$q.screen.gt.sm">
                <LongTextView :text="props.row.notes" icon="notes" />
              </template>
            </q-td>
            <q-td key="datetime" :props="props" class="wrap-column">
              {{ formatDateTime(props.row.datetime) }}
            </q-td>
            <q-td key="grand_total" :props="props" style="text-align: right">
              {{ formatNumber(props.row.grand_total) }}
            </q-td>
            <q-td key="notes" :props="props">
              <LongTextView :text="props.row.notes" />
            </q-td>
          </q-tr>
          <q-tr v-show="props.expand" :props="props">
            <q-td colspan="100%">
              <div v-for="(item, index) in props.row.details" :key="index">
                {{ index + 1 }}) {{ item.quantity }} {{ item.product_uom }}
                {{ item.product_name }} x Rp. {{ formatNumber(item.price) }} =
                <b> Rp. {{ formatNumber(item.subtotal_price) }} </b>
              </div>
              <div v-if="props.row.total_discount > 0"></div>
              <div v-if="props.row.total_tax > 0"></div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </authenticated-layout>
</template>
