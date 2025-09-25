<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams } from "@/helpers/utils";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { Dialog, useQuasar } from "quasar";
import { getCurrentMonth, getCurrentYear } from "@/helpers/datetime";
import {
  createMonthOptions,
  createOptions,
  createYearOptions,
} from "@/helpers/options";
import useTableHeight from "@/composables/useTableHeight";
import SalesOrderStatusChip from "@/components/SalesOrderStatusChip.vue";
import SalesOrderPaymentStatusChip from "@/components/SalesOrderPaymentStatusChip.vue";
import SalesOrderDeliveryStatusChip from "@/components/SalesOrderDeliveryStatusChip.vue";
import MyLink from "@/components/MyLink.vue";
import axios from "axios";

const title = "Penjualan";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const currentYear = getCurrentYear();
const currentMonth = getCurrentMonth();

const years = [
  { label: "Semua Tahun", value: "all" },
  { label: `${currentYear}`, value: currentYear },
  ...createYearOptions(currentYear - 2, currentYear - 1).reverse(),
];

const months = [
  { value: "all", label: "Semua Bulan" },
  ...createMonthOptions(),
];

const statusOptions = [
  { value: "all", label: "Semua Status" },
  ...createOptions(window.CONSTANTS.SALES_ORDER_STATUSES),
];

const paymentStatusOptions = [
  { value: "all", label: "Semua Status" },
  ...createOptions(window.CONSTANTS.SALES_ORDER_PAYMENT_STATUSES),
];

const deliveryStatusOptions = [
  { value: "all", label: "Semua Status" },
  ...createOptions(window.CONSTANTS.SALES_ORDER_DELIVERY_STATUSES),
];

const filter = reactive({
  search: "",
  year: currentYear,
  month: currentMonth,
  status: "all",
  payment_status: "all",
  delivery_status: "all",
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
    label: "Info Order",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "customer_id",
    label: "Pelanggan",
    field: "customer_id",
    align: "left",
  },
  {
    name: "total",
    label: "Total",
    field: "total",
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

const onRowClicked = (row) => {
  if (row.status == "draft") {
    editItem(row);
    return;
  }

  viewItem(row);
};

const editItem = (row) => {
  router.get(route("admin.sales-order.edit", row.id));
};

const viewItem = (row) => {
  router.get(route("admin.sales-order.detail", row.id));
};

const cancelItem = (row) => {
  Dialog.create({
    title: "Konfirmasi Pembatalan",
    icon: "question",
    message: `Batalkan transaksi #${row.formatted_id}?`,
    focus: "cancel",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    axios
      .post(
        route("admin.sales-order.cancel", {
          id: row.id,
        }),
        {
          id: row.id,
        }
      )
      .then((response) => {
        const updatedItem = response.data.data;
        const itemIndex = rows.value.findIndex(
          (item) => item.id === updatedItem.id
        );

        if (itemIndex === -1) {
          console.warn("Item tidak ditemukan di tabel.");
          return;
        }

        rows.value[itemIndex].status = "canceled";

        $q.notify({
          message: response.data.message,
          position: "bottom",
        });
      })
      .catch((error) => {
        const errorMessage =
          error.response?.data?.message ||
          "Terjadi kesalahan saat membatalkan transaksi.";
        $q.notify({
          message: errorMessage,
          color: "warning",
          position: "bottom",
        });
        console.error(error);
      });
  });
};

const deleteItem = (row) =>
  handleDelete({
    title: "Konfirmasi Penghapusan",
    message: `Hapus transaksi #${row.formatted_id}?`,
    url: route("admin.sales-order.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.sales-order.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id" || col.name === "action");
});

watch(
  () => filter.year,
  (newVal) => {
    if (newVal === null) {
      filter.month = null;
    }
  }
);
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
        v-if="$can('admin.sales-order.edit')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.sales-order.add'))"
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
            v-if="filter.year != 'all'"
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
          <q-select
            v-model="filter.payment_status"
            :options="paymentStatusOptions"
            label="Status Pembayaran"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />
          <q-select
            v-if="false"
            v-model="filter.delivery_status"
            :options="deliveryStatusOptions"
            label="Status Pengiriman"
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
            @click.prevent="onRowClicked(props.row)"
          >
            <q-td key="id" :props="props" class="wrap-column">
              <div>
                <q-icon name="tag" />
                {{ props.row.formatted_id }}
              </div>
              <div>
                <q-icon class="inline-icon" name="calendar_today" />{{
                  formatDateTime(props.row.datetime)
                }}
              </div>
              <template v-if="!$q.screen.gt.sm">
                <div v-if="props.row.customer">
                  <q-icon name="person" />
                  <my-link
                    :href="
                      route('admin.customer.detail', {
                        id: props.row.customer.id,
                      })
                    "
                    @click.stop
                  >
                    {{ props.row.customer.username }} -
                    {{ props.row.customer.name }}
                  </my-link>
                </div>
                <div>Rp. {{ formatNumber(props.row.grand_total) }}</div>
                <div v-if="props.row.notes">
                  <q-icon name="notes" /> {{ props.row.notes }}
                </div>
              </template>
              <div>
                <SalesOrderStatusChip :status="props.row.status" />
                <SalesOrderPaymentStatusChip
                  :status="props.row.payment_status"
                />
                <SalesOrderDeliveryStatusChip
                  v-if="false"
                  :status="props.row.delivery_status"
                />
              </div>
            </q-td>
            <q-td key="customer_id" :props="props">
              <div v-if="props.row.customer">
                <q-icon name="person" class="inline-icon" />
                {{ props.row.customer_username }} -
                {{ props.row.customer_name }}
                <br />
                <q-icon name="phone" class="inline-icon" />
                {{ props.row.customer_phone }}
                <br />
                <q-icon name="home_pin" class="inline-icon" />
                {{ props.row.customer_address }}
              </div>
            </q-td>
            <q-td key="total" :props="props">
              {{ formatNumber(props.row.grand_total) }}
            </q-td>
            <q-td key="action" :props="props" @click.stop>
              <div class="flex justify-end">
                <q-btn
                  v-if="
                    $can('admin.sales-order.detail') ||
                    $can('admin.sales-order.edit') ||
                    $can('admin.sales-order.cancel') ||
                    $can('admin.sales-order.delete')
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
                        v-if="
                          props.row.status != 'draft' &&
                          $can('admin.sales-order.detail')
                        "
                        @click.stop="viewItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="visibility" />
                        </q-item-section>
                        <q-item-section> Lihat </q-item-section>
                      </q-item>
                      <q-item
                        v-if="
                          props.row.status == 'draft' &&
                          $can('admin.sales-order.edit')
                        "
                        @click.stop="editItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section> Edit </q-item-section>
                      </q-item>
                      <q-item
                        v-if="
                          props.row.status == 'draft' &&
                          $can('admin.sales-order.cancel')
                        "
                        @click.stop="cancelItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="cancel" />
                        </q-item-section>
                        <q-item-section> Batalkan </q-item-section>
                      </q-item>
                      <q-separator />
                      <q-item
                        v-if="$can('admin.sales-order.delete')"
                        @click.stop="deleteItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="delete_forever" />
                        </q-item-section>
                        <q-item-section> Hapus </q-item-section>
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
