<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import {
  formatDateTime,
  formatNumber,
  formatNumberWithSymbol,
  formatMoneyWithSymbol,
} from "@/helpers/formatter";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";
import ImageViewer from "@/components/ImageViewer.vue";
import dayjs from "dayjs";
import DateTimePicker from "@/components/DateTimePicker.vue";

const title = "Daftar Utang ke Supplier";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const startDate = dayjs().startOf("month").toDate();
const endDate = dayjs().endOf("month").toDate();

const filter = reactive({
  search: "",
  start_date: startDate,
  end_date: endDate,
  type: null,
  supplier_id: null,
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
    name: "supplier_id",
    label: "Supplier",
    field: "supplier_id",
    align: "left",
  },
  { name: "amount", label: "Jumlah (Rp)", field: "amount", align: "right" },
  {
    name: "running_balance",
    label: "Saldo (Rp)",
    field: "running_balance",
    align: "right",
  },
  { name: "notes", label: "Catatan", field: "notes", align: "left" },
  { name: "action", align: "right" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus ledger #${row.code}? Saldo supplier akan dikembalikan.`,
    url: route("admin.supplier-ledger.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
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
    url: route("admin.supplier-ledger.data"),
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
      >
        <q-tooltip>Filter Data</q-tooltip>
      </q-btn>

      <!-- Tombol Penyesuaian / Opname -->
      <q-btn
        v-if="false && $can('admin.supplier-ledger.adjust')"
        icon="tune"
        dense
        rounded
        class="q-ml-sm"
        color="secondary"
        text-color="white"
        @click="router.get(route('admin.supplier-ledger.adjust'))"
      >
        <q-tooltip>Penyesuaian Saldo (Opname)</q-tooltip>
      </q-btn>

      <!-- Tombol Transaksi Manual -->
      <q-btn
        v-if="$can('admin.supplier-ledger.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.supplier-ledger.add'))"
      >
        <q-tooltip>Catat Transaksi Manual</q-tooltip>
      </q-btn>
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
          <q-input
            class="col"
            outlined
            dense
            debounce="300"
            v-model="filter.search"
            placeholder="Cari Kode / Catatan"
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

        <template v-slot:body="props">
          <q-tr
            :props="props"
            class="cursor-pointer hover-bg-grey-1"
            @click.stop="
              router.get(
                route('admin.supplier-ledger.detail', {
                  id: props.row.id,
                })
              )
            "
          >
            <!-- Kolom Kode & Info Dasar -->
            <q-td key="datetime" :props="props" class="wrap-column">
              <div class="text-bold text-primary">
                <q-icon class="inline-icon" name="tag" />
                {{ props.row.code }}

                <q-badge color="primary" class="q-ml-sm">
                  {{ props.row.type_label }}
                </q-badge>
              </div>

              <div class="text-caption text-grey-7">
                <q-icon class="inline-icon" name="calendar_today" />
                {{ formatDateTime(props.row.datetime) }}
              </div>

              <div
                v-if="props.row.ref"
                class="text-caption text-grey-8 q-mt-xs"
              >
                <q-icon class="inline-icon" name="tag" />
                Ref: {{ props.row.ref.code || "#" + props.row.ref.id }}
              </div>

              <template v-if="!$q.screen.gt.sm">
                <div class="q-mt-xs text-bold">
                  <q-icon class="inline-icon" name="person" />
                  {{ props.row.supplier?.code }} -
                  {{ props.row.supplier?.name }}
                </div>
                <div>
                  <div
                    :class="
                      props.row.amount >= 0 ? 'text-green-8' : 'text-red-8'
                    "
                    class="row justify-between"
                  >
                    <div>
                      <q-icon class="inline-icon" name="paid" /> Jumlah:
                    </div>
                    <div>
                      {{ formatMoneyWithSymbol(props.row.amount) }}
                    </div>
                  </div>
                  <div
                    v-if="true"
                    class="text-grey-8 text-bold row justify-between"
                  >
                    <div>
                      <q-icon class="inline-icon" name="balance" /> Saldo:
                    </div>
                    <div>
                      {{ formatMoneyWithSymbol(props.row.running_balance) }}
                    </div>
                  </div>
                </div>
              </template>
            </q-td>

            <q-td key="supplier_id" :props="props">
              <div class="text-bold">{{ props.row.supplier?.name }}</div>
              <div class="text-caption text-grey">
                {{ props.row.supplier?.code }}
              </div>
            </q-td>

            <!-- Kolom Mutasi -->
            <q-td key="amount" :props="props" class="text-right">
              <div
                :class="
                  props.row.amount >= 0
                    ? 'text-positive text-bold'
                    : 'text-negative text-bold'
                "
              >
                {{ formatNumberWithSymbol(props.row.amount) }}
              </div>
            </q-td>

            <!-- Kolom Saldo Berjalan -->
            <q-td
              key="running_balance"
              :props="props"
              class="text-right text-bold"
            >
              {{ formatNumber(props.row.running_balance) }}
            </q-td>

            <q-td key="notes" :props="props">
              <LongTextView :text="props.row.notes" />
            </q-td>

            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <!-- Lihat Bukti -->
                <q-btn
                  v-if="props.row.image_path"
                  icon="image"
                  color="primary"
                  dense
                  flat
                  rounded
                  @click.stop="showAttachment(props.row.image_path)"
                >
                  <q-tooltip>Lihat Bukti</q-tooltip>
                </q-btn>

                <!-- Hapus (Hanya jika tidak ada ref otomatis dari sistem) -->
                <q-btn
                  v-if="
                    !props.row.ref_type && $can('admin.supplier-ledger.delete')
                  "
                  icon="delete"
                  color="negative"
                  size="sm"
                  dense
                  flat
                  rounded
                  @click.stop="deleteItem(props.row)"
                >
                  <q-tooltip>Hapus</q-tooltip>
                </q-btn>

                <!-- Jika ada ref_type, disable delete & kasih tooltip -->
                <q-btn
                  v-else
                  icon="lock"
                  color="grey-5"
                  dense
                  flat
                  rounded
                  disable
                  size="sm"
                >
                  <q-tooltip
                    >Transaksi Otomatis (Tidak bisa dihapus manual)</q-tooltip
                  >
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
