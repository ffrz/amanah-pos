<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatMoney } from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";
import useTableHeight from "@/composables/useTableHeight";
import { useTableClickProtection } from "@/composables/useTableClickProtection";

const title = "Terminal Kasir";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const filter = reactive({
  search: "",
  status: "active",
  ...getQueryParams(),
});

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
    label: $q.screen.lt.md ? "Item" : "Nama",
    field: "name",
    align: "left",
    sortable: true,
  },
  {
    name: "finance_account_id",
    label: "Akun Kas",
    field: "finance_account_id",
    sortable: false,
    align: "left",
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

const statuses = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Terminal Kasir ${row.name}?`,
    url: route("admin.cashier-terminal.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.cashier-terminal.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => fetchItems();

const onRowClicked = (row) =>
  router.get(route("admin.cashier-terminal.detail", { id: row.id }));
const { protectClick } = useTableClickProtection();
const protectedRowClick = protectClick(onRowClicked);

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter(
    (col) =>
      col.name === "name" ||
      col.name === "finance_account_id" ||
      col.name === "action"
  );
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
        v-if="$can('admin.cashier-terminal.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.cashier-terminal.add'))"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-xs-12 col-sm-2"
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
      <q-table
        class="full-height-table"
        :style="{ height: tableHeight }"
        ref="tableRef"
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
            :class="!props.row.active ? 'bg-red-1' : ''"
            class="cursor-pointer"
            @click.stop="protectedRowClick(props.row, $event)"
          >
            <q-td key="name" :props="props" class="wrap-column">
              <div>
                <q-icon name="wallet" />
                {{ props.row.name }}
              </div>
              <div>
                <q-icon name="home_pin" />
                {{ props.row.location }}
              </div>
              <template v-if="$q.screen.lt.md">
                <LongTextView :text="props.row.notes" icon="notes" />
              </template>
            </q-td>
            <q-td key="finance_account_id" :props="props">
              <div>
                <q-icon name="wallet" />
                {{ props.row.finance_account.name }}
              </div>
              <div>
                <q-icon name="money" />
                {{ formatMoney(props.row.finance_account.balance) }}
              </div>
            </q-td>
            <q-td key="notes" :props="props">
              <LongTextView :text="props.row.notes" />
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  v-if="
                    $can('admin.cashier-terminal.edit') ||
                    $can('admin.cashier-terminal.delete')
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
                    <q-list style="width: 200px">
                      <q-item
                        v-if="$can('admin.cashier-terminal.edit')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.cashier-terminal.edit', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.cashier-terminal.delete')"
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
