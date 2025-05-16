<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { create_options, check_role, getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";

const title = "Order Cuci";
const rows = ref([]);
const loading = ref(true);
const showFilter = ref(false);

const filter = reactive({
  search: "",
  order_status: "all",
  payment_status: "all",
  service_status: "all",
  ...getQueryParams()
});

const order_statuses = [
  { value: "all", label: "Semua" },
  ...create_options(window.CONSTANTS.WASHORDER_ORDERSTATUSES),
];
const service_statuses = [
  { value: "all", label: "Semua" },
  ...create_options(window.CONSTANTS.WASHORDER_SERVICESTATUSES),
];
const payment_statuses = [
  { value: "all", label: "Semua" },
  ...create_options(window.CONSTANTS.WASHORDER_PAYMENTSTATUSES),
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
    name: "order",
    label: "Order",
    field: "order",
    align: "left",
    sortable: true,
  },
  {
    name: "vehicle",
    label: "Kendaraan",
    field: "vehicle",
    align: "left",
    sortable: true,
  },
  {
    name: "customer",
    label: "Pelanggan",
    field: "customer",
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
    message: `Hapus order #${row.id}?`,
    url: route("admin.wash-order.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.wash-order.data"),
    loading,
  });

const onFilterChange = () => {
  fetchItems();
};

const onRowClicked = (row) => {
  router.get(route("admin.wash-order.detail", row.id));
};

const $q = useQuasar();
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "order" || col.name === "action");
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #right-button>
      <q-btn
        icon="add"
        dense
        color="primary"
        @click="router.get(route('admin.wash-order.add'))"
      />
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
    </template>
    <template #title>{{ title }}</template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            v-model="filter.order_status"
            :options="order_statuses"
            label="Status Pesanan"
            dense
            map-options
            class="custom-select col-xs-12 col-sm-2"
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.service_status"
            :options="service_statuses"
            label="Status Cuci"
            dense
            class="custom-select col-xs-12 col-sm-2"
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.payment_status"
            :options="payment_statuses"
            label="Status Pembayaran"
            dense
            class="custom-select col-xs-12 col-sm-2"
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
    <div class="q-pa-sm">
      <q-table
        flat
        bordered
        square
        color="primary"
        class="full-height-table va-top wash-order-list"
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
            <q-icon size="2em" name="sentiment_dissatisfied" />
            <span>
              {{ message }}
              {{ filter ? " with term " + filter : "" }}</span
            >
            <q-icon size="2em" :name="filter ? 'filter_b_and_w' : icon" />
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr
            :props="props"
            @click="onRowClicked(props.row)"
            class="cursor-pointer"
          >
            <q-td key="order" :props="props">
              <div class="flex q-gutter-xs">
                <div><b>#{{ props.row.id }}</b></div>
                <div>{{ $dayjs(new Date(props.row.order_created_at)).format("DD/MM/YYYY HH:mm") }}</div>
                <q-chip dense size="sm"
                  :color="props.row.order_status === 'pending' ? 'orange'    : (props.row.order_status === 'confirmed' ? 'grey'  : (props.row.order_status === 'completed' ? 'green'  : 'red'))"
                   :icon="props.row.order_status === 'pending' ? 'emergency' : (props.row.order_status === 'confirmed' ? 'check' : (props.row.order_status === 'completed' ? 'done_all'  : 'close'))"
                >{{ $CONSTANTS.WASHORDER_ORDERSTATUSES[props.row.order_status] }}</q-chip>
              </div>
              <template v-if="$q.screen.lt.md">
                <div class="flex q-col-gutter-xs">
                  <div><q-icon name="pin" /> <b>{{ props.row.vehicle_plate_number }}</b></div>
                  <div><q-icon name="directions_car" /> {{ props.row.vehicle_description }}</div>
                </div>
                <div class="flex q-col-gutter-xs q-py-xs">
                  <div><q-icon name="person" /> <b>{{ props.row.customer_name }}</b></div>
                  <div><q-icon name="phone" /> {{ props.row.customer_phone }}</div>
                  <div><q-icon name="location_home" /> {{ props.row.customer_address }}</div>
                </div>
                <div class="flex q-col-gutter-xs q-py-xs">
                  <q-chip dense icon="soap">{{ $CONSTANTS.WASHORDER_SERVICESTATUSES[props.row.service_status] }}</q-chip>
                  <q-chip dense icon="payments">{{ $CONSTANTS.WASHORDER_PAYMENTSTATUSES[props.row.payment_status] }}</q-chip>
                </div>
              </template>
            </q-td>
            <q-td key="vehicle" :props="props">
              <div><q-icon name="devices" /> <b>{{ props.row.vehicle_plate_number }}</b></div>
              <div><q-icon name="report" /> {{ props.row.vehicle_description }}</div>
              <div><q-icon name="task" /> {{ props.row.actions }}</div>
            </q-td>
            <q-td key="customer" :props="props">
              <div><q-icon name="person" /> <b>{{ props.row.customer_name }}</b></div>
              <div><q-icon name="phone" /> {{ props.row.customer_phone }}</div>
              <div><q-icon name="location_home" /> {{ props.row.customer_address }}</div>
            </q-td>
            <q-td key="status" :props="props"> </q-td>
            <q-td
              key="action"
              :props="props"
            >
              <div class="flex justify-end">
                <q-btn
                  icon="more_vert"
                  dense
                  flat
                  style="height: 40px; width: 30px"
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
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="router.get(route('admin.wash-order.duplicate', props.row.id))"
                      >
                        <q-item-section avatar>
                          <q-icon name="file_copy" />
                        </q-item-section>
                        <q-item-section icon="copy"> Duplikat </q-item-section>
                      </q-item>
                      <q-item
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="router.get(route('admin.wash-order.edit', props.row.id))"
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section icon="edit">Edit</q-item-section>
                      </q-item>
                      <q-item
                        @click.stop="deleteItem(props.row)"
                        clickable
                        :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)"
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
