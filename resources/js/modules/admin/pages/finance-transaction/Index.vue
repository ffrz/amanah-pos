<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import {
  formatDateTime,
  formatNumber,
  formatNumberWithSymbol,
  plusMinusSymbol,
} from "@/helpers/formatter";
import { createOptions } from "@/helpers/options";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";
import ImageViewer from "@/components/ImageViewer.vue";
import dayjs from "dayjs";
import DateTimePicker from "@/components/DateTimePicker.vue";

const page = usePage();
const title = "Transaksi Kas";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const startDate = dayjs().startOf("month").toDate();
const endDate = dayjs().endOf("month").toDate();

const accounts = [
  {
    label: "Semua",
    value: "all",
  },
  ...page.props.accounts.map((account) => ({
    label: account.name,
    value: account.id,
  })),
];

const types = [
  { value: "all", label: "Semua" },
  ...createOptions(window.CONSTANTS.FINANCE_TRANSACTION_TYPES),
];

const filter = reactive({
  search: "",
  type: "all",
  account_id: "all",
  start_date: startDate,
  end_date: endDate,
  ...getQueryParams(),
});

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
    label: $q.screen.lt.md ? "Item" : "Kode",
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
    name: "account",
    label: "Akun",
    field: "account",
    align: "left",
  },
  {
    name: "type",
    label: "Jenis",
    field: "type",
    align: "left",
    sortable: true,
  },
  {
    name: "amount",
    label: "Jumlah (Rp.)",
    field: "amount",
    align: "right",
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
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
    message: `Penghapusan transaksi keuangan bisa mengakibatkan data yang saling berkaitan di modul lain tidak konsisten. Anda setuju untuk menghapus transaksi ${row.code}?`,
    url: route("admin.finance-transaction.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  const apiFilter = {
    ...filter,
    start_date: dayjs(filter.start_date).format("YYYY-MM-DD HH:mm:ss"),
    end_date: dayjs(filter.end_date).format("YYYY-MM-DD HH:mm:ss"),
  };

  handleFetchItems({
    pagination,
    filter: apiFilter,
    props,
    rows,
    url: route("admin.finance-transaction.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id" || col.name === "action");
});

const activeImagePath = ref(null);
const showImageViewer = ref(false);

const showAttachment = (url) => {
  activeImagePath.value = url;
  showImageViewer.value = true;
};
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
      <q-btn
        v-if="$can('admin.finance-transaction.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.finance-transaction.add'))"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <DateTimePicker
            v-model="filter.start_date"
            label="Mulai Tanggal"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            @update:model-value="onFilterChange"
            hide-bottom-space
            date-only
          />
          <DateTimePicker
            v-model="filter.end_date"
            label="Sampai Tanggal"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            @update:model-value="onFilterChange"
            hide-bottom-space
            date-only
          />
          <q-select
            v-model="filter.type"
            :options="types"
            label="Jenis"
            dense
            class="custom-select col-xs-6 col-sm-2"
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.account_id"
            label="Akun"
            :options="accounts"
            map-options
            emit-value
            class="custom-select col-xs-6 col-sm-2"
            outlined
            dense
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
        :style="{ height: tableHeight }"
        class="full-height-table"
        flat
        bordered
        square
        color="primary"
        row-key="id"
        v-model:pagination="pagination"
        :filter="filter.search"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
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
            class="cursor-pointer"
            @click.stop="
              router.get(
                route('admin.finance-transaction.detail', {
                  id: props.row.id,
                })
              )
            "
          >
            <q-td key="id" :props="props">
              <div>
                <template v-if="$q.screen.lt.md">
                  <q-icon class="inline-icon" name="tag" />
                </template>
                {{ props.row.code }}
              </div>
              <template v-if="$q.screen.lt.md">
                <div class="text-grey-8">
                  <q-icon class="inline-icon" name="calendar_clock" />
                  {{ formatDateTime(props.row.datetime) }}
                </div>
                <div class="text-bold text-grey-8">
                  <q-icon name="wallet" class="inline-icon" />
                  {{ props.row.account.name }}
                </div>
                <div
                  class="text-bold"
                  :class="props.row.amount < 0 ? 'text-red' : 'text-green'"
                >
                  <q-icon name="money" class="inline-icon" />
                  {{
                    plusMinusSymbol(props.row.amount) +
                    "Rp." +
                    formatNumber(Math.abs(props.row.amount))
                  }}
                </div>
                <LongTextView
                  v-if="props.row.notes"
                  icon="notes"
                  :text="props.row.notes"
                  class="text-grey-8"
                />
                <q-badge :color="props.row.amount > 0 ? 'green' : 'red'">
                  {{ props.row.type_label }}
                </q-badge>
              </template>
            </q-td>
            <q-td key="datetime" :props="props" class="wrap-column">
              {{ formatDateTime(props.row.datetime) }}
            </q-td>
            <q-td key="account" :props="props">
              {{ props.row.account?.name }}
            </q-td>
            <q-td key="type" :props="props">
              {{ $CONSTANTS.FINANCE_TRANSACTION_TYPES[props.row.type] }}
            </q-td>
            <q-td key="amount" :props="props" style="text-align: right">
              {{ formatNumberWithSymbol(props.row.amount) }}
            </q-td>
            <q-td key="notes" :props="props" class="wrap-column">
              {{ props.row.notes }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end no-wrap">
                <q-btn
                  icon="image"
                  color="primary"
                  dense
                  flat
                  :disable="
                    props.row.image_path === null || props.row.image_path === ''
                  "
                  @click.stop="showAttachment(props.row.image_path)"
                >
                  <q-tooltip v-if="props.row.image_path != null">
                    Lihat Bukti
                  </q-tooltip>
                </q-btn>
                <q-btn
                  v-if="$can('admin.finance-transaction.delete')"
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
                        v-if="$can('admin.finance-transaction.delete')"
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
    <ImageViewer v-model="showImageViewer" :imageUrl="`/${activeImagePath}`" />
  </authenticated-layout>
</template>
