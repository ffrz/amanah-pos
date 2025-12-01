<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { router, usePage } from "@inertiajs/vue3";
import { formatDateTime, formatMoney } from "@/helpers/formatter";
import {
  handleDelete,
  handleFetchItems,
  handlePost,
} from "@/helpers/client-req-handler";
import ImageViewer from "@/components/ImageViewer.vue";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";
import dayjs from "dayjs";
import DateTimePicker from "@/components/DateTimePicker.vue";

const title = "Daftar Setoran Kas";
const $q = useQuasar();
const page = usePage();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const activeImagePath = ref(null);
const showImageViewer = ref(false);

// Default Date Range: Bulan Ini
const startDate = dayjs().startOf("month").toDate();
const endDate = dayjs().endOf("month").toDate();

// Opsi Status
const statusOptions = [
  { value: "all", label: "Semua Status" },
  { value: "pending", label: "Menunggu Konfirmasi" },
  { value: "approved", label: "Disetujui" },
  { value: "rejected", label: "Ditolak" },
];

// [BARU] Opsi Kasir & Terminal dari Props
const cashierOptions = computed(() => [
  { value: "all", label: "Semua Kasir" },
  ...(page.props.cashiers || []).map((c) => ({
    value: c.id,
    label: c.name,
  })),
]);

const terminalOptions = computed(() => [
  { value: "all", label: "Semua Terminal" },
  ...(page.props.cashier_terminals || []).map((t) => ({
    value: t.id,
    label: t.name,
  })),
]);

const filter = reactive({
  search: "",
  start_date: startDate,
  end_date: endDate,
  status: "all",
  cashier_id: "all",
  terminal_id: "all", // [BARU]
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
    name: "code",
    label: "Kode",
    field: "code",
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
    name: "cashier",
    label: "Kasir",
    field: "cashier",
    align: "left",
    sortable: false,
  },
  {
    name: "flow",
    label: "Alur Kas",
    field: "flow",
    align: "left",
  },
  {
    name: "amount",
    label: "Jumlah (Rp.)",
    field: "amount",
    align: "right",
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
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
  { name: "action", field: "action", align: "right" },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) => {
  const apiFilter = {
    ...filter,
    start_date: filter.start_date
      ? dayjs(filter.start_date).format("YYYY-MM-DD HH:mm:ss")
      : null,
    end_date: filter.end_date
      ? dayjs(filter.end_date).format("YYYY-MM-DD HH:mm:ss")
      : null,
  };

  handleFetchItems({
    pagination,
    filter: apiFilter,
    props,
    rows,
    url: route("admin.cashier-cash-drop.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => ["code", "action"].includes(col.name));
});

// Helper Warna Status
const getStatusColor = (status) => {
  switch (status) {
    case "approved":
      return "positive";
    case "rejected":
      return "negative";
    default:
      return "warning";
  }
};

const getStatusLabel = (status) => {
  switch (status) {
    case "approved":
      return "Disetujui";
    case "rejected":
      return "Ditolak";
    case "pending":
      return "Menunggu";
    default:
      return status;
  }
};

// Actions
const confirmItem = (row, action) => {
  const actionText = action === "accept" ? "Setujui" : "Tolak";

  handlePost({
    message: `${actionText} setoran kas #${row.code}?`,
    url: route("admin.cashier-cash-drop.confirm"),
    fetchItemsCallback: fetchItems,
    loading,
    data: {
      id: row.id,
      action: action, // 'accept' or 'reject'
    },
  });
};

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus data setoran kas ${row.code}?`,
    url: route("admin.cashier-cash-drop.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const showAttachment = (url) => {
  activeImagePath.value = url;
  showImageViewer.value = true;
};

const openCreatePage = () => {
  router.get(route("admin.cashier-cash-drop.add"));
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
        v-if="$can('admin.cashier-cash-drop.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="openCreatePage"
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

          <!-- Filter Status -->
          <q-select
            v-model="filter.status"
            :options="statusOptions"
            label="Status"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />

          <!-- [BARU] Filter Kasir -->
          <q-select
            v-model="filter.cashier_id"
            :options="cashierOptions"
            label="Kasir"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />

          <!-- [BARU] Filter Terminal -->
          <q-select
            v-model="filter.terminal_id"
            :options="terminalOptions"
            label="Terminal"
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
            placeholder="Cari Kode / Catatan"
            clearable
            @update:model-value="onFilterChange"
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
        :style="{ height: tableHeight }"
        flat
        bordered
        square
        color="primary"
        row-key="id"
        v-model:pagination="pagination"
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
                route('admin.cashier-cash-drop.detail', { id: props.row.id })
              )
            "
          >
            <!-- Kolom KODE (Responsif) -->
            <q-td key="code" :props="props" class="wrap-column">
              <div>
                <q-icon
                  v-if="!$q.screen.gt.sm"
                  name="tag"
                  class="inline-icon"
                />
                <span class="text-weight-bold">{{ props.row.code }}</span>
              </div>

              <!-- Tampilan Mobile Only -->
              <template v-if="!$q.screen.gt.sm">
                <div>
                  <q-icon name="calendar_clock" class="inline-icon" />
                  {{ formatDateTime(props.row.datetime) }}
                </div>
                <div>
                  <q-icon name="person" class="inline-icon" />
                  {{ props.row.cashier?.name }}
                </div>
                <div>
                  <q-icon name="outbound" class="inline-icon text-negative" />
                  {{ props.row.source_finance_account?.name }}
                </div>
                <div>
                  <q-icon
                    name="move_to_inbox"
                    class="inline-icon text-positive"
                  />
                  {{ props.row.target_finance_account?.name }}
                </div>
                <div class="text-bold">
                  <q-icon name="money" class="inline-icon" />
                  {{ formatMoney(props.row.amount) }}
                </div>
                <LongTextView
                  v-if="props.row.notes"
                  :text="props.row.notes"
                  icon="notes"
                  class="text-grey-8"
                />
                <div class="q-mt-xs">
                  <q-chip
                    dense
                    :color="getStatusColor(props.row.status)"
                    text-color="white"
                    size="xs"
                  >
                    {{ getStatusLabel(props.row.status) }}
                  </q-chip>
                </div>
              </template>
            </q-td>

            <!-- Kolom WAKTU -->
            <q-td key="datetime" :props="props">
              {{ formatDateTime(props.row.datetime) }}
            </q-td>

            <!-- Kolom KASIR -->
            <q-td key="cashier" :props="props">
              <div class="row items-center">
                {{ props.row.cashier?.name }}
              </div>
            </q-td>

            <!-- Kolom ALUR KAS -->
            <q-td key="flow" :props="props">
              <div class="column" style="font-size: 0.9em">
                <div>
                  <q-icon name="outbound" color="negative" class="q-mr-xs" />
                  <span>{{ props.row.source_finance_account?.name }}</span>
                </div>
                <div>
                  <q-icon
                    name="move_to_inbox"
                    color="positive"
                    class="q-mr-xs"
                  />
                  <span>{{ props.row.target_finance_account?.name }}</span>
                </div>
              </div>
            </q-td>

            <!-- Kolom JUMLAH -->
            <q-td key="amount" :props="props" class="text-right">
              <div class="text-weight-bold">
                {{ formatMoney(props.row.amount) }}
              </div>
            </q-td>

            <!-- Kolom STATUS -->
            <q-td key="status" :props="props" class="text-center">
              <q-chip
                outline
                :color="getStatusColor(props.row.status)"
                size="sm"
                class="text-weight-medium"
              >
                {{ getStatusLabel(props.row.status) }}
              </q-chip>
            </q-td>

            <!-- Kolom CATATAN -->
            <q-td
              key="notes"
              :props="props"
              class="word-wrap"
              style="max-width: 200px"
            >
              <LongTextView icon="notes" :text="props.row.notes" />
            </q-td>

            <!-- Kolom AKSI -->
            <q-td key="action" :props="props">
              <div class="row justify-end no-wrap">
                <!-- Lihat Gambar -->
                <q-btn
                  v-if="props.row.image_path"
                  icon="image"
                  color="primary"
                  dense
                  flat
                  rounded
                  :disable="
                    props.row.image_path === null || props.row.image_path === ''
                  "
                  @click.stop="showAttachment(props.row.image_path)"
                >
                  <q-tooltip>Lihat Bukti</q-tooltip>
                </q-btn>

                <!-- Menu Opsi -->
                <q-btn
                  v-if="
                    $can('admin.cashier-cash-drop.confirm') ||
                    $can('admin.cashier-cash-drop.delete')
                  "
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
                    <q-list style="min-width: 150px">
                      <!-- Opsi Approve -->
                      <q-item
                        v-if="
                          props.row.status === 'pending' &&
                          $can('admin.cashier-cash-drop.confirm')
                        "
                        clickable
                        v-close-popup
                        v-ripple
                        @click="confirmItem(props.row, 'accept')"
                      >
                        <q-item-section avatar>
                          <q-icon name="check" />
                        </q-item-section>
                        <q-item-section>Setujui</q-item-section>
                      </q-item>

                      <!-- Opsi Reject -->
                      <q-item
                        v-if="
                          props.row.status === 'pending' &&
                          $can('admin.cashier-cash-drop.confirm')
                        "
                        clickable
                        v-close-popup
                        v-ripple
                        @click="confirmItem(props.row, 'reject')"
                      >
                        <q-item-section avatar>
                          <q-icon name="cancel" />
                        </q-item-section>
                        <q-item-section>Tolak</q-item-section>
                      </q-item>

                      <q-separator v-if="props.row.status === 'pending'" />

                      <!-- Opsi Hapus -->
                      <q-item
                        v-if="$can('admin.cashier-cash-drop.delete')"
                        clickable
                        v-close-popup
                        v-ripple
                        @click="deleteItem(props.row)"
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

      <ImageViewer
        v-model="showImageViewer"
        :imageUrl="`/${activeImagePath}`"
      />
    </div>
  </authenticated-layout>
</template>
