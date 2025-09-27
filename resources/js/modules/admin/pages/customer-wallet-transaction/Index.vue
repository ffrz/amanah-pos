<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { createMonthOptions, createYearOptions } from "@/helpers/options";
import {
  formatDateTime,
  formatNumber,
  formatNumberWithSymbol,
  plusMinusSymbol,
} from "@/helpers/formatter";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";
import ImageViewer from "@/components/ImageViewer.vue";

const title = "Transaksi Dompet Pelanggan";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;

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
    name: "datetime",
    label: "Waktu",
    field: "datetime",
    align: "left",
    sortable: true,
  },
  {
    name: "customer",
    label: "Pelanggan",
    field: "customer",
    align: "left",
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
  {
    name: "amount",
    label: "Jumlah (Rp.)",
    field: "amount",
    align: "right",
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
    message: `Hapus transaksi #${row.formatted_id}? Seluruh akun terkait akan di refund dan rekaman akan dihapus.`,
    url: route("admin.customer-wallet-transaction.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.customer-wallet-transaction.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter(
    (col) => col.name === "datetime" || col.name === "action"
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
        v-if="$can('admin.customer-wallet-transaction.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.customer-wallet-transaction.add'))"
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
        virtual-scroll
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
                route('admin.customer-wallet-transaction.detail', {
                  id: props.row.id,
                })
              )
            "
          >
            <q-td key="datetime" :props="props" class="wrap-column">
              <div>
                <q-icon name="tag" class="inline-icon" />
                {{ props.row.formatted_id }}
              </div>
              <div>
                <q-icon name="calendar_clock" class="inline-icon" />
                {{ formatDateTime(props.row.datetime) }}
              </div>
              <div v-if="props.row.finance_account">
                <q-icon name="wallet" class="inline-icon" />
                {{
                  props.row.finance_account
                    ? props.row.finance_account.name
                    : "-"
                }}
              </div>
              <template v-if="!$q.screen.gt.sm">
                <LongTextView
                  icon="person"
                  :text="
                    props.row.customer.code + ' - ' + props.row.customer.name
                  "
                />
                <div
                  :class="
                    props.row.amount > 0 ? 'text-positive' : 'text-negative'
                  "
                  class="text-bold"
                >
                  <q-icon name="money" class="inline-icon" />
                  {{ plusMinusSymbol(props.row.amount) }}Rp.
                  {{ formatNumber(Math.abs(props.row.amount)) }}
                </div>
                <LongTextView
                  :text="props.row.notes"
                  icon="notes"
                  class="text-grey-8"
                />
                <div>
                  <q-badge>
                    {{ props.row.type_label }}
                  </q-badge>
                </div>
              </template>
            </q-td>
            <q-td key="customer" :props="props">
              <LongTextView
                :text="
                  props.row.customer.code + ' - ' + props.row.customer.name
                "
              />
            </q-td>
            <q-td key="notes" :props="props">
              <LongTextView :text="props.row.notes" />
            </q-td>
            <q-td key="amount" :props="props" style="text-align: right">
              {{ formatNumberWithSymbol(props.row.amount) }}
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
                  v-if="$can('admin.customer-wallet-transaction.delete')"
                  icon="more_vert"
                  rounded
                  dense
                  flat
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
                        v-if="$can('admin.customer-wallet-transaction.delete')"
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
