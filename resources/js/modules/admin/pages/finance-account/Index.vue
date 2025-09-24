<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { createOptions } from "@/helpers/options";
import { formatNumber } from "@/helpers/formatter";
import useTableHeight from "@/composables/useTableHeight";

const page = usePage();
const title = "Akun Kas";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef, 67 + 50);
const loading = ref(true);
const filter = reactive({
  search: "",
  status: "active",
  type: "all",
  ...getQueryParams(),
});
const totalBalance = page.props.totalBalance;

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "name",
  descending: false,
});

const columns = [
  {
    name: "name",
    label: "Nama",
    field: "name",
    align: "left",
    sortable: true,
  },
  {
    name: "type",
    label: "Jenis Akun",
    field: "type",
    align: "left",
    sortable: true,
  },
  {
    name: "balance",
    label: "Saldo (Rp)",
    field: "balance",
    align: "right",
    sortable: true,
  },
  {
    name: "action",
    align: "right",
  },
];

const statuses = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const types = [
  { value: "all", label: "Semua" },
  ...createOptions(window.CONSTANTS.FINANCE_ACCOUNT_TYPES),
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Akun ${row.name}?`,
    url: route("admin.finance-account.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.finance-account.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => fetchItems();
const onRowClicked = (row) =>
  router.get(route("admin.finance-account.detail", { id: row.id }));
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "name" || col.name === "action");
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
      <q-btn
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.finance-account.add'))"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-xs-6 col-sm-2"
            style="min-width: 150px"
            v-model="filter.type"
            :options="types"
            label="Jenis Akun"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            class="custom-select col-xs-6 col-sm-2"
            style="min-width: 150px"
            v-model="filter.status"
            :options="statuses"
            label="Status"
            dense
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
      <q-card class="full-width q-pa-sm q-mb-xs" flat bordered square>
        <div class="q-my-sm text-subtitle">
          Total Saldo Akun Aktif:
          <span class="text-bold text-grey-8"
            >Rp. {{ formatNumber(totalBalance) }}</span
          >
        </div>
      </q-card>
      <q-table
        ref="tableRef"
        class="full-height-table"
        :style="{ height: tableHeight }"
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
            :class="!props.row.active ? 'bg-red-1' : ''"
            class="cursor-pointer"
            @click="onRowClicked(props.row)"
          >
            <q-td key="name" :props="props" class="wrap-column">
              <div class="text-bold text-grey-9">{{ props.row.name }}</div>
              <div class="text-grey-9">
                <div v-if="props.row.type == 'bank'">
                  <q-icon name="wallet" class="inline-icon" />
                  Rekening {{ props.row.bank ?? "-" }}
                  {{ props.row.number ?? "-" }} a.n.
                  {{ props.row.holder ?? "-" }}
                </div>
                <div v-if="props.row.notes">
                  <q-icon name="notes" /> {{ props.row.notes }}
                </div>
              </div>
              <template v-if="$q.screen.lt.md">
                <div>
                  <q-icon name="money" class="inline-icon" /> Rp.
                  {{ formatNumber(props.row.balance) }}
                </div>
              </template>
            </q-td>
            <q-td key="type" :props="props">
              {{ $CONSTANTS.FINANCE_ACCOUNT_TYPES[props.row.type] }}
            </q-td>
            <q-td key="balance" :props="props">
              {{ formatNumber(props.row.balance) }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn icon="more_vert" dense flat rounded @click.stop>
                  <q-menu
                    anchor="bottom right"
                    self="top right"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-list style="width: 200px">
                      <q-item
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route(
                              'admin.finance-account.duplicate',
                              props.row.id
                            )
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="file_copy" />
                        </q-item-section>
                        <q-item-section> Duplikat </q-item-section>
                      </q-item>
                      <q-item
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.finance-account.edit', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item
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
