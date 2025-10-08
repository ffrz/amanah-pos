<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import {
  createMonthOptions,
  createOptions,
  createYearOptions,
} from "@/helpers/options";
import useTableHeight from "@/composables/useTableHeight";
import { getCurrentMonth, getCurrentYear } from "@/helpers/datetime";
import { useCan } from "@/composables/usePermission";

const title = "Penyesuaian Stok";
const rows = ref([]);
const loading = ref(true);
const showFilter = ref(false);
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

const filter = reactive({
  search: "",
  year: currentYear,
  month: currentMonth,
  status: "all",
  type: "all",
  ...getQueryParams(),
});

const statuses = [
  { value: "all", label: "Semua" },
  ...createOptions(window.CONSTANTS.STOCK_ADJUSTMENT_STATUSES),
];
const types = [
  { value: "all", label: "Semua" },
  ...createOptions(window.CONSTANTS.STOCK_ADJUSTMENT_TYPES),
];

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
});

const columns = [
  {
    name: "id",
    label: "ID",
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
    name: "status",
    label: "Status",
    field: "status",
    align: "center",
    sortable: true,
  },
  {
    name: "type",
    label: "Jenis",
    field: "type",
    align: "center",
    sortable: false,
  },
  {
    name: "total_cost",
    label: "Total Modal",
    field: "total_cost",
    align: "right",
    sortable: false,
  },
  {
    name: "total_price",
    label: "Total Harga",
    field: "total_price",
    align: "right",
    sortable: false,
  },
  {
    name: "notes",
    label: "Notes",
    field: "notes",
    align: "left",
    sortable: false,
  },
  {
    name: "action",
    align: "right",
  },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Penyesuaian Stok #${row.formatted_id}?`,
    url: route("admin.stock-adjustment.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.stock-adjustment.data"),
    loading,
    tableRef,
  });

const onFilterChange = () => {
  fetchItems();
};

const onRowClicked = (row) => {
  if (row.status == "draft")
    router.get(route("admin.stock-adjustment.editor", row.id));
  else router.get(route("admin.stock-adjustment.detail", row.id));
};

const $q = useQuasar();
const computedColumns = computed(() => {
  let cols = columns;

  if (!useCan("admin.product:view-cost")) {
    cols = cols.filter((col) => col.name !== "cost");
  }

  if ($q.screen.gt.sm) return cols;

  return cols.filter((col) => col.name === "id" || col.name === "action");
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #right-button>
      <q-btn
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        rounded
        flat
        @click="showFilter = !showFilter"
      />
      <q-btn
        v-if="$can('admin.stock-adjustment.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.stock-adjustment.create'))"
      />
    </template>
    <template #title>{{ title }}</template>
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
            v-model="filter.status"
            :options="statuses"
            label="Status"
            dense
            map-options
            class="custom-select col-xs-12 col-sm-2"
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.type"
            :options="types"
            label="Jenis"
            dense
            class="custom-select col-xs-12 col-sm-2"
            map-options
            emit-value
            outlined
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
        flat
        bordered
        square
        color="primary"
        class="full-height-table stock-adjustment-list"
        row-key="id"
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
            <span>
              {{ message }}
              {{ filter ? " with term " + filter : "" }}</span
            >
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr
            :props="props"
            @click="onRowClicked(props.row)"
            class="cursor-pointer"
          >
            <q-td key="id" :props="props">
              <template v-if="!$q.screen.lt.md">
                <div class="flex q-gutter-xs">
                  #{{ props.row.formatted_id }}
                </div>
              </template>
              <template v-else>
                <div class="flex q-col-gutter-xs">
                  <div>#{{ props.row.formatted_id }}</div>
                  <div>
                    <q-icon name="history" />
                    {{ formatDateTime(props.row.datetime) }}
                  </div>
                  <q-chip
                    dense
                    size="sm"
                    :color="
                      props.row.status === 'draft'
                        ? 'orange'
                        : props.row.status === 'closed'
                        ? 'green'
                        : props.row.status === 'canceled'
                        ? 'red'
                        : ''
                    "
                    :icon="
                      props.row.status === 'draft'
                        ? 'emergency'
                        : props.row.status === 'closed'
                        ? 'check'
                        : props.row.status === 'canceled'
                        ? 'close'
                        : ''
                    "
                    >{{
                      $CONSTANTS.STOCK_ADJUSTMENT_STATUSES[props.row.status]
                    }}</q-chip
                  >
                </div>
                <div>
                  <q-icon name="category" />
                  {{ $CONSTANTS.STOCK_ADJUSTMENT_TYPES[props.row.type] }}
                </div>
                <div v-if="props.row.created_by">
                  <q-icon name="person" /> Dibuat:
                  <b>{{ props.row.created_by.username }}</b>
                  <q-icon name="history" />
                  {{ formatDateTime(props.row.created_at) }}
                </div>
                <div v-if="props.row.updated_by">
                  <q-icon name="person" /> Diperbarui:
                  <b>{{ props.row.updated_by.username }}</b>
                  <q-icon name="history" />
                  {{ formatDateTime(props.row.updated_at) }}
                </div>
                <div
                  :class="
                    props.row.total_cost < 0
                      ? 'text-red-10'
                      : props.row.total_cost > 0
                      ? 'text-green-10'
                      : ''
                  "
                >
                  <q-icon name="money" />
                  <span v-if="$can('admin.product:view-cost')"
                    >Rp. {{ formatNumber(props.row.total_cost) }} /
                  </span>
                  Rp.
                  {{ formatNumber(props.row.total_price) }}
                </div>
              </template>
            </q-td>
            <q-td key="datetime" :props="props">
              {{ formatDateTime(props.row.datetime) }}
            </q-td>
            <q-td key="status" :props="props" class="text-center">
              {{ $CONSTANTS.STOCK_ADJUSTMENT_STATUSES[props.row.status] }}
            </q-td>
            <q-td key="type" :props="props">
              {{ $CONSTANTS.STOCK_ADJUSTMENT_TYPES[props.row.type] }}
            </q-td>
            <q-td key="total_cost" :props="props">
              <div
                v-if="$can('admin.product:view-cost')"
                :class="
                  props.row.total_cost < 0
                    ? 'text-red-10'
                    : props.row.total_cost > 0
                    ? 'text-green-10'
                    : ''
                "
              >
                {{ formatNumber(props.row.total_cost) }}
              </div>
            </q-td>
            <q-td key="total_price" :props="props">
              <div
                :class="
                  props.row.total_price < 0
                    ? 'text-red-10'
                    : props.row.total_price > 0
                    ? 'text-green-10'
                    : ''
                "
              >
                {{ formatNumber(props.row.total_price) }}
              </div>
            </q-td>
            <q-td key="notes" :props="props">
              {{ props.row.notes }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  v-if="$can('admin.stock-adjustment.delete')"
                  icon="more_vert"
                  dense
                  flat
                  rounded
                  @click.stop
                >
                  <q-menu
                    anchor="bottom right"
                    self="top right"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-list style="width: 200px">
                      <q-item
                        v-if="$can('admin.stock-adjustment.delete')"
                        @click.stop="deleteItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="delete_forever" />
                        </q-item-section>
                        <q-item-section>Hapus</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </authenticated-layout>
</template>
