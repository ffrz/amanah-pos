<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { create_options, check_role, getQueryParams, formatNumber } from "@/helpers/utils";
import { useQuasar } from "quasar";

const title = "Penyesuaian Stok";
const rows = ref([]);
const loading = ref(true);
const showFilter = ref(false);

const filter = reactive({
  search: "",
  status: "all",
  type: "all",
  ...getQueryParams()
});

const statuses = [
  { value: "all", label: "Semua" },
  ...create_options(window.CONSTANTS.STOCKADJUSTMENT_STATUSES),
];
const types = [
  { value: "all", label: "Semua" },
  ...create_options(window.CONSTANTS.STOCKADJUSTMENT_TYPES),
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
    message: `Hapus Transaksi #${row.id}?`,
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
  });

const onFilterChange = () => {
  fetchItems();
};

const onRowClicked = (row) => {
  if (row.status == 'draft')
    router.get(route("admin.stock-adjustment.editor", row.id));
  else
    router.get(route("admin.stock-adjustment.detail", row.id));
};

const $q = useQuasar();
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id" || col.name === "action");
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #right-button>
      <q-btn icon="add" dense color="primary" @click="router.get(route('admin.stock-adjustment.create'))" />
      <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
    </template>
    <template #title>{{ title }}</template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select v-model="filter.status" :options="statuses" label="Status" dense map-options
            class="custom-select col-xs-12 col-sm-2" emit-value outlined @update:model-value="onFilterChange" />
          <q-select v-model="filter.type" :options="types" label="Jenis" dense class="custom-select col-xs-12 col-sm-2"
            map-options emit-value outlined @update:model-value="onFilterChange" />

          <q-input class="col" outlined dense debounce="300" v-model="filter.search" placeholder="Cari" clearable>
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm">
      <q-table flat bordered square color="primary" class="full-height-table stock-adjustment-list" row-key="id"
        virtual-scroll v-model:pagination="pagination" :filter="filter.search" :loading="loading"
        :columns="computedColumns" :rows="rows" :rows-per-page-options="[10, 25, 50]" @request="fetchItems"
        binary-state-sort>
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>

        <template v-slot:no-data="{ icon, message, filter }">
          <div class="full-width row flex-center text-grey-8 q-gutter-sm">
            <span>
              {{ message }}
              {{ filter ? " with term " + filter : "" }}</span>
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr :props="props" @click="onRowClicked(props.row)" class="cursor-pointer">
            <q-td key="id" :props="props">
              <template v-if="!$q.screen.lt.md">
                <div class="flex q-gutter-xs">
                  <div><b>#{{ props.row.id }}</b></div>
                </div>
              </template>
              <template v-else>
                <div class="flex q-col-gutter-xs">
                  <div><b>#{{ props.row.id }}</b></div>
                  <div><q-icon name="history" /> {{ $dayjs(new Date(props.row.datetime)).format("DD/MM/YYYY HH:mm") }}
                  </div>
                  <q-chip dense size="sm"
                    :color="props.row.status === 'draft' ? 'orange' : (props.row.status === 'closed' ? 'green' : (props.row.status === 'canceled' ? 'red' : ''))"
                    :icon="props.row.status === 'draft' ? 'emergency' : (props.row.status === 'closed' ? 'check' : (props.row.status === 'canceled' ? 'close' : ''))">{{
                      $CONSTANTS.STOCKADJUSTMENT_STATUSES[props.row.status] }}</q-chip>
                </div>
                <div>
                  <q-icon name="category" /> {{ $CONSTANTS.STOCKADJUSTMENT_TYPES[props.row.type] }}
                </div>
                <div v-if="props.row.created_by">
                  <q-icon name="person" /> Dibuat: <b>{{ props.row.created_by.username }}</b> <q-icon name="history" />
                  {{ $dayjs(new Date(props.row.created_datetime)).format("DD/MM/YYYY HH:mm") }}
                </div>
                <div v-if="props.row.updated_by">
                  <q-icon name="person" /> Diperbarui: <b>{{ props.row.updated_by.username }}</b> <q-icon
                    name="history" /> {{ $dayjs(new Date(props.row.updated_datetime)).format("DD/MM/YYYY HH:mm") }}
                </div>
                <div
                  :class="props.row.total_cost < 0 ? 'text-red-10' : (props.row.total_cost > 0 ? 'text-green-10' : '')">
                  <q-icon name="money" /> Rp. {{ formatNumber(props.row.total_cost) }} / Rp. {{
                    formatNumber(props.row.total_price) }}
                </div>
              </template>
            </q-td>
            <q-td key="datetime" :props="props">
              {{ $dayjs(new Date(props.row.datetime)).format("DD/MM/YYYY HH:mm") }}
            </q-td>
            <q-td key="status" :props="props" class="text-center">
              {{ $CONSTANTS.STOCKADJUSTMENT_STATUSES[props.row.status] }}
            </q-td>
            <q-td key="type" :props="props">
              {{ $CONSTANTS.STOCKADJUSTMENT_TYPES[props.row.type] }}
            </q-td>
            <q-td key="total_cost" :props="props">
              <div
                :class="props.row.total_cost < 0 ? 'text-red-10' : (props.row.total_cost > 0 ? 'text-green-10' : '')">
                {{ formatNumber(props.row.total_cost) }}
              </div>
            </q-td>
            <q-td key="total_price" :props="props">
              <div
                :class="props.row.total_price < 0 ? 'text-red-10' : (props.row.total_price > 0 ? 'text-green-10' : '')">
                {{ formatNumber(props.row.total_price) }}
              </div>
            </q-td>
            <q-td key="notes" :props="props">
              {{ props.row.notes }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn icon="more_vert" dense flat style="height: 40px; width: 30px" @click.stop>
                  <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                    <q-list style="width: 200px">
                      <q-item @click.stop="deleteItem(props.row)" clickable
                        :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" v-ripple v-close-popup>
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
