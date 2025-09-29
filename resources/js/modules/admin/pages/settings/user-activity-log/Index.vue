<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { Dialog, useQuasar } from "quasar";
import useTableHeight from "@/composables/useTableHeight";
import { showError, showInfo } from "@/composables/useNotify";
import { formatDateTime } from "@/helpers/formatter";
import { useTableClickProtection } from "@/composables/useTableClickProtection";

const title = "Log Aktifitas Pengguna";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const { protectClick } = useTableClickProtection();

const filter = reactive({
  search: "",
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
    label: $q.screen.lt.md ? "Log Item" : "ID",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "logged_at",
    label: "Waktu",
    field: "logged_at",
    align: "left",
    sortable: true,
  },
  {
    name: "username",
    label: "Pengguna",
    field: "username",
    align: "left",
    sortable: true,
  },
  {
    name: "activity_category",
    label: "Kategori Aktifitas",
    field: "activity_category",
    align: "left",
    sortable: true,
  },
  {
    name: "activity_name",
    label: "Nama Aktifitas",
    field: "activity_name",
    align: "left",
    sortable: true,
  },
  {
    name: "description",
    label: "Deskripsi",
    field: "description",
    align: "left",
    sortable: true,
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
    message: `Hapus log #${row.id}?`,
    url: route("admin.user-activity-log.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.user-activity-log.data"),
    loading,
    tableRef,
  });
};

const onRowClicked = (row) =>
  router.get(route("admin.user-activity-log.detail", { id: row.id }));

const protectedRowClick = protectClick(onRowClicked);

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id" || col.name === "action");
});

const confirmClear = () => {
  // TODO: Buatkan dialog konfirmasi custom yang mendukung icon dan warna agar reusable,
  // lalu ganti penggunaan seluruh dialog di aplikasi agar lebih seragam dan mudah dimaintain!
  Dialog.create({
    title: "PERINGATAN: Hapus Permanen Log",
    message: `Anda yakin ingin menghapus **SELURUH** log aktivitas pengguna? Aksi ini **tidak dapat dibatalkan** dan akan menghapus semua rekaman riwayat.`,
    focus: "cancel",
    cancel: true,
    persistent: true,
    ok: {
      label: "YA, HAPUS SEKARANG",
      color: "negative",
      push: true,
    },
    cancel: {
      label: "Batal",
      color: "white",
      textColor: "dark",
    },
  }).onOk(() => {
    axios
      .post(route("admin.user-activity-log.clear"))
      .then(() => {
        showInfo("Seluruh log telah dibersihkan.");
        fetchItems();
      })
      .catch((error) => {
        const errorMessage =
          error.response?.data?.message ||
          "Terjadi kesalahan saat membersihkan log aktifitas pengguna.";
        showError(errorMessage, "bottom");
        console.error(error);
      });
  });
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
        v-if="$can('admin.user-activity-log.clear')"
        icon="delete_sweep"
        dense
        rounded
        flat
        class="q-ml-sm"
        color="negative"
        @click="confirmClear"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
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
        class="full-height-table"
        ref="tableRef"
        s
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
            class="cursor-pointer"
            @click.stop="protectedRowClick(props.row, $event)"
          >
            <q-td key="id" :props="props">
              <div>
                <template v-if="$q.screen.lt.md">
                  <q-icon name="tag" class="inline-icon" />
                  ID Aktifitas:
                </template>
                {{ props.row.id }}
              </div>
              <div v-if="$q.screen.lt.md">
                <div>
                  <q-icon name="calendar_clock" class="inline-icon" />
                  {{ formatDateTime(props.row.logged_at) }}
                </div>
                <div>
                  <q-icon name="person" class="inline-icon" />
                  <i-link
                    @click.stop
                    :href="
                      route('admin.user.detail', { id: props.row.user_id })
                    "
                  >
                    {{ props.row.username }}</i-link
                  >
                </div>
                <div>
                  <q-icon name="category" class="inline-icon" />
                  {{ props.row.activity_category_label }} >
                  {{ props.row.activity_name_label }}
                </div>
                <div>
                  <q-icon name="notes" class="inline-icon" />
                  {{ props.row.description }}
                </div>
              </div>
            </q-td>
            <q-td key="logged_at" :props="props">
              {{ formatDateTime(props.row.logged_at) }}
            </q-td>
            <q-td key="username" :props="props">
              <i-link
                @click.stop
                :href="route('admin.user.detail', { id: props.row.user_id })"
              >
                {{ props.row.username }}
              </i-link>
            </q-td>
            <q-td key="activity_category" :props="props">
              {{ props.row.activity_category_label }}
            </q-td>
            <q-td key="activity_name" :props="props">
              {{ props.row.activity_name_label }}
            </q-td>
            <q-td key="description" :props="props" class="wrap-column">
              {{ props.row.description }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  v-if="$can('admin.user-role.delete')"
                  @click.stop="deleteItem(props.row)"
                  icon="delete_forever"
                  flat
                  color="red"
                  dense
                  size="sm"
                  rounded
                />
              </div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </authenticated-layout>
</template>
