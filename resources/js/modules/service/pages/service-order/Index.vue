<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatNumber } from "@/helpers/formatter";
import useTableHeight from "@/composables/useTableHeight";
import { useTableClickProtection } from "@/composables/useTableClickProtection";

const title = "Order Service";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);

const filter = reactive({
  search: "",
  service_status: "all",
  order_status: "open", // Default hanya tampilkan yang masih aktif
  ...getQueryParams(),
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "received_datetime",
  descending: true,
});

const columns = [
  {
    name: "order_code",
    label: "Kode",
    field: "order_code",
    align: "left",
    sortable: true,
  },
  {
    name: "customer_name",
    label: "Pelanggan",
    field: "customer_name",
    align: "left",
    sortable: true,
  },
  {
    name: "device",
    label: "Perangkat",
    field: "device",
    align: "left",
  },
  {
    name: "service_status",
    label: "Status",
    field: "service_status_label",
    align: "center",
  },
  {
    name: "total_cost",
    label: "Total (Rp)",
    field: "total_cost",
    align: "right",
    sortable: true,
  },
  {
    name: "action",
    align: "right",
  },
];

// Opsi filter untuk Service Status (dari konstanta Model)
const serviceStatuses = [
  { value: "all", label: "Semua Status" },
  { value: "received", label: "Diterima" },
  { value: "checking", label: "Dicek" },
  { value: "working", label: "Dikerjakan" },
  { value: "completed", label: "Siap Diambil" },
  { value: "picked", label: "Sudah Diambil" },
];

onMounted(() => {
  fetchItems();
});

// Watcher untuk auto-fetch saat filter berubah
watch(
  () => [filter.search, filter.service_status],
  () => {
    pagination.value.page = 1;
    fetchItems();
  }
);

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("service.service-order.data"),
    loading,
    tableRef,
  });
};

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Order ${row.order_code} milik ${row.customer_name}?`,
    url: route("service.service-order.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const onRowClicked = (row) =>
  router.get(route("service.service-order.detail", { id: row.id }));

const { protectClick } = useTableClickProtection();
const protectedRowClick = protectClick(onRowClicked);

// Helper warna badge status
const getStatusColor = (status) => {
  const colors = {
    received: "grey-7",
    checking: "blue",
    working: "orange-9",
    completed: "green-8",
    picked: "black",
  };
  return colors[status] || "grey";
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  // Mobile: Hanya Kode/Nama dan Action
  return columns.filter(
    (col) => col.name === "order_code" || col.name === "action"
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
        v-if="$can('service.service-order.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('service.service-order.add'))"
      />
    </template>

    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="col-xs-12 col-sm-3"
            v-model="filter.service_status"
            :options="serviceStatuses"
            label="Progres Service"
            dense
            outlined
            map-options
            emit-value
          />
          <q-input
            class="col"
            outlined
            dense
            debounce="400"
            v-model="filter.search"
            placeholder="Cari Nota, Nama, atau HP..."
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
        row-key="id"
        v-model:pagination="pagination"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        @request="fetchItems"
        binary-state-sort
      >
        <template v-slot:loading>
          <q-inner-loading showing color="primary" />
        </template>

        <template v-slot:body="props">
          <q-tr
            :props="props"
            class="cursor-pointer"
            @click.stop="protectedRowClick(props.row, $event)"
          >
            <q-td key="order_code" :props="props">
              <div class="text-weight-bold text-primary">
                #{{ props.row.order_code }}
              </div>
              <div class="text-caption text-grey-7" v-if="$q.screen.lt.md">
                {{ props.row.customer_name }} • {{ props.row.device }}
              </div>
              <q-badge
                v-if="$q.screen.lt.md"
                :color="getStatusColor(props.row.service_status)"
                dense
              >
                {{ props.row.service_status_label }}
              </q-badge>
            </q-td>

            <q-td key="customer_name" :props="props" v-if="$q.screen.gt.sm">
              <div>{{ props.row.customer_name }}</div>
              <div class="text-caption text-grey-6">
                {{ props.row.customer_phone }}
              </div>
            </q-td>

            <q-td key="device" :props="props" v-if="$q.screen.gt.sm">
              <div class="ellipsis" style="max-width: 200px">
                {{ props.row.device }}
              </div>
            </q-td>

            <q-td key="service_status" :props="props" v-if="$q.screen.gt.sm">
              <q-badge
                :color="getStatusColor(props.row.service_status)"
                class="q-pa-xs"
              >
                {{ props.row.service_status_label }}
              </q-badge>
            </q-td>

            <q-td key="total_cost" :props="props" v-if="$q.screen.gt.sm">
              <div class="text-weight-medium">
                {{
                  props.row.total_cost > 0
                    ? formatNumber(props.row.total_cost)
                    : "-"
                }}
              </div>
            </q-td>

            <q-td key="action" :props="props">
              <q-btn icon="more_vert" dense flat rounded @click.stop>
                <q-menu
                  auto-close
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <q-list style="min-width: 120px">
                    <q-item clickable @click="onRowClicked(props.row)">
                      <q-item-section avatar
                        ><q-icon name="visibility" size="xs"
                      /></q-item-section>
                      <q-item-section>Detail</q-item-section>
                    </q-item>
                    <q-item
                      clickable
                      @click="
                        router.get(
                          route('service.service-order.edit', props.row.id)
                        )
                      "
                    >
                      <q-item-section avatar
                        ><q-icon name="edit" size="xs"
                      /></q-item-section>
                      <q-item-section>Edit</q-item-section>
                    </q-item>
                    <q-separator />
                    <q-item
                      clickable
                      class="text-negative"
                      @click="deleteItem(props.row)"
                    >
                      <q-item-section avatar
                        ><q-icon name="delete" size="xs"
                      /></q-item-section>
                      <q-item-section>Hapus</q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
              </q-btn>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </authenticated-layout>
</template>
