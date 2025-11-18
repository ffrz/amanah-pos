<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
// Pastikan import handleFetchItems sudah mengarah ke file yang dimodifikasi
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

const categories = [
  {
    label: "Semua",
    value: "all",
  },
  {
    label: "Tanpa Kategori",
    value: "none",
  },
  ...page.props.categories.map((cat) => ({
    label: cat.name,
    value: cat.id,
  })),
];

const tags = page.props.tags.map((tag) => ({
  label: tag.name,
  value: tag.id,
}));
const types = [
  { value: "all", label: "Semua" },
  ...createOptions(window.CONSTANTS.FINANCE_TRANSACTION_TYPES),
];

const filter = reactive({
  search: "",
  tags: [],
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
  sortBy: "datetime",
  descending: true,
  nextCursor: null,
  prevCursor: null,
  cursor: null,
});

const columns = [
  {
    name: "datetime",
    label: $q.screen.lt.md ? "Item" : "Kode",
    field: "datetime",
    align: "left",
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

const editItem = (row) => {
  router.get(route("admin.finance-transaction.edit", row.id));
};

const deleteItem = (row) =>
  handleDelete({
    message: `Penghapusan transaksi keuangan bisa mengakibatkan data yang saling berkaitan tidak konsisten. Anda setuju untuk menghapus transaksi ${row.code}?`,
    url: route("admin.finance-transaction.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  const apiFilter = {
    ...filter,
    // Pastikan format tanggal sesuai yang diharapkan oleh backend
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
  // Ketika filter berubah, kita harus mereset kursor agar kembali ke awal data
  pagination.value.nextCursor = null;
  pagination.value.prevCursor = null;
  pagination.value.cursor = null;
  pagination.value.page = 1; // Kembali ke halaman 1
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter(
    (col) => col.name === "datetime" || col.name === "action"
  );
});

const activeImagePath = ref(null);
const showImageViewer = ref(false);

const showAttachment = (url) => {
  activeImagePath.value = url;
  showImageViewer.value = true;
};

// --- LOGIKA BARU UNTUK CURSOR PAGINATION ---
const isCursorMode = computed(() => {
  // Kita anggap mode kursor aktif jika salah satu kursor memiliki nilai
  return (
    pagination.value.nextCursor !== null || pagination.value.prevCursor !== null
  );
});

const goToCursorPage = (cursorType) => {
  // 1. Set kursor yang akan dikirim pada request berikutnya ke properti 'cursor' sementara
  if (cursorType === "next") {
    pagination.value.cursor = pagination.value.nextCursor;
  } else if (cursorType === "prev") {
    pagination.value.cursor = pagination.value.prevCursor;
  } else {
    // Jika tidak ada tipe (misalnya, kembali ke awal), hapus kursor
    pagination.value.cursor = null;
  }

  // 2. Panggil fetchItems. Logika pengiriman 'cursor' akan ditangani oleh handleFetchItems.js
  fetchItems();
};
// --- AKHIR LOGIKA CURSOR PAGINATION ---
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
          <q-select
            v-model="filter.category_id"
            label="Kategori"
            :options="categories"
            map-options
            emit-value
            class="custom-select col-xs-6 col-sm-2"
            outlined
            dense
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.tags"
            label="Label"
            :options="tags"
            map-options
            emit-value
            class="custom-select col-xs-6 col-sm-2"
            outlined
            dense
            multiple
            use-chips
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
        :pagination="pagination"
        :filter="filter.search"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[]"
        @request="() => {}"
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
            <q-td key="datetime" :props="props">
              <div>
                <q-icon class="inline-icon" name="tag" />
                {{ props.row.code }}
              </div>
              <div>
                <q-icon class="inline-icon" name="calendar_clock" />
                {{ formatDateTime(props.row.datetime) }}
              </div>
              <template v-if="$q.screen.lt.md">
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
                <q-badge
                  :color="
                    props.row.type == 'transfer'
                      ? 'grey'
                      : props.row.amount > 0
                      ? 'green-8'
                      : 'red-8'
                  "
                >
                  {{ props.row.type_label }}
                </q-badge>
                <div v-if="props.row.category_id">
                  <q-icon name="category" class="inline-icon" />
                  {{ props.row.category.name }}
                </div>
                <div v-if="props.row.tags.length > 0">
                  <q-badge
                    v-for="tag in props.row.tags"
                    :key="tag.id"
                    class="q-mr-xs"
                    color="grey-6"
                    >#{{ tag.name }}</q-badge
                  >
                </div>
                <LongTextView
                  v-if="props.row.notes"
                  icon="notes"
                  :text="props.row.notes"
                  class="text-grey-8"
                />
              </template>
            </q-td>
            <q-td key="account" :props="props">
              {{ props.row.account?.name }}
            </q-td>
            <q-td key="type" :props="props">
              <q-badge
                :color="
                  props.row.type == 'transfer'
                    ? 'grey'
                    : props.row.amount < 0
                    ? 'red-8'
                    : 'green-8'
                "
              >
                {{ $CONSTANTS.FINANCE_TRANSACTION_TYPES[props.row.type] }}
              </q-badge>
              <div v-if="props.row.category_id">
                <q-icon name="category" class="inline-icon" />
                {{ props.row.category.name }}
              </div>
              <div v-else>
                <q-icon name="category" class="inline-icon" />
                Tanpa Kategori
              </div>
              <div v-if="props.row.tags.length > 0">
                <q-badge
                  v-for="tag in props.row.tags"
                  :key="tag.id"
                  class="q-mr-xs"
                  color="grey-5"
                  >#{{ tag.name }}</q-badge
                >
              </div>
            </q-td>
            <q-td
              key="amount"
              :props="props"
              style="text-align: right"
              :class="props.row.amount > 0 ? 'text-green-8' : 'text-red-8'"
            >
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
                        v-if="$can('admin.finance-transaction.edit')"
                        @click.stop="editItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
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

        <template v-slot:pagination>
          <q-btn
            icon="chevron_left"
            :disable="!pagination.prevCursor"
            @click="goToCursorPage('prev')"
            dense
            rounded
            flat
            class="q-mr-xs"
          />
          <q-btn
            icon="chevron_right"
            :disable="!pagination.nextCursor"
            @click="goToCursorPage('next')"
            dense
            rounded
            flat
            class="q-ml-xs"
          />
        </template>
      </q-table>
    </div>
    <ImageViewer v-model="showImageViewer" :imageUrl="`/${activeImagePath}`" />
  </authenticated-layout>
</template>
