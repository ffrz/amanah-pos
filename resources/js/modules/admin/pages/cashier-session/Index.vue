<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatDateTime, formatMoney } from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";
import useTableHeight from "@/composables/useTableHeight";

const title = "Sesi Kasir";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const filter = reactive({
  search: "",
  status: "all",
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
    label: "Sesi",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "opening_info",
    label: "Buka Sesi",
    field: "opening_info",
    sortable: false,
    align: "left",
  },
  {
    name: "closing_info",
    label: "Tutup Sesi",
    field: "closing_info",
    sortable: false,
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
  { value: "closed", label: "Selesai" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Sesi: ${row.cashier_terminal.name} - ${row.user.username}?`,
    url: route("admin.cashier-session.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.cashier-session.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => fetchItems();

const onRowClicked = (row) =>
  router.get(route("admin.cashier-session.detail", { id: row.id }));

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id" || col.name === "action");
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
        v-if="$can('admin.cashier-session.open')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.cashier-session.open'))"
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
            :class="props.row.is_closed ? '' : 'bg-yellow-3'"
            @click="onRowClicked(props.row)"
          >
            <q-td key="id" :props="props" class="wrap-column">
              <div>
                <q-icon class="inline-icon" name="tag" />
                SessionID: {{ props.row.id }}
              </div>
              <div>
                <q-icon class="inline-icon" name="point_of_sale" />
                {{ props.row.cashier_terminal.name }}
              </div>
              <div>
                <q-icon class="inline-icon" name="person" />
                {{ props.row.user.username }} - {{ props.row.user.name }}
              </div>
            </q-td>
            <q-td key="opening_info" :props="props">
              <div>
                <q-icon class="inline-icon" name="calendar_clock" />
                {{
                  props.row.opened_at
                    ? formatDateTime(props.row.opened_at)
                    : "-"
                }}
              </div>
              <div>
                <q-icon class="inline-icon" name="money" />
                {{ formatMoney(props.row.opening_balance) }}
              </div>
              <LongTextView
                v-if="props.row.opening_notes"
                :text="props.row.opening_notes"
                icon="notes"
              />
            </q-td>

            <q-td key="closing_info" :props="props">
              <div>
                <q-icon class="inline-icon" name="calendar_clock" />
                {{
                  props.row.closed_at
                    ? formatDateTime(props.row.closed_at)
                    : "-"
                }}
              </div>
              <div>
                <q-icon class="inline-icon" name="money" />
                {{ formatMoney(props.row.closing_balance) }}
              </div>
              <LongTextView
                v-if="props.row.closing_notes"
                :text="props.row.closing_notes"
                icon="notes"
              />
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  v-if="
                    $can('admin.cashier-session.close') ||
                    $can('admin.cashier-session.delete')
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
                        v-if="$can('admin.cashier-session.close')"
                        :disable="props.row.is_closed"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.cashier-session.close', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="logout" />
                        </q-item-section>
                        <q-item-section>Tutup Sesi Kasir</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.cashier-session.delete')"
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
