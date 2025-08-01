<script setup>
import { handleFetchItems } from "@/helpers/client-req-handler";
import { formatNumber, getQueryParams } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { computed, onMounted, reactive, ref } from "vue";
const page = usePage();
const title = "Rincian Akun";
const tab = ref("main");
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  // search: "",
  // order_status: "all",
  // payment_status: "all",
  // service_status: "all",
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
    label: "#",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "type",
    label: "Jenis",
    field: "type",
    align: "center",
    sortable: true,
  },
  {
    name: "quantity",
    label: "Jumlah",
    field: "quantity",
    align: "center",
    sortable: true,
  },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    // url: route("admin.finance-account-wallet-transaction.data", { finance-account_id: page.props.data.id }),
    loading,
  });

const $q = useQuasar();
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id");
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.finance-account.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="edit"
          dense
          color="primary"
          @click="
            router.get(
              route('admin.finance-account.edit', { id: page.props.data.id })
            )
          "
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-tabs v-model="tab" align="left">
              <q-tab name="main" label="Info Akun" />
              <q-tab name="history" label="Riwayat Transaksi" />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width: 100px">Nama</td>
                      <td style="width: 1px">:</td>
                      <td>{{ page.props.data.name }}</td>
                    </tr>
                    <tr>
                      <td>Jenis</td>
                      <td>:</td>
                      <td>
                        {{
                          $CONSTANTS.FINANCE_ACCOUNT_TYPES[page.props.data.type]
                        }}
                      </td>
                    </tr>
                    <template v-if="page.props.data.type == 'bank'">
                      <tr>
                        <td>Bank</td>
                        <td>:</td>
                        <td>{{ page.props.data.bank }}</td>
                      </tr>
                      <tr>
                        <td>No. Rek</td>
                        <td>:</td>
                        <td>{{ page.props.data.number }}</td>
                      </tr>
                      <tr>
                        <td>Atas Nama</td>
                        <td>:</td>
                        <td>{{ page.props.data.holder }}</td>
                      </tr>
                    </template>
                    <tr>
                      <td>Status</td>
                      <td>:</td>
                      <td>
                        {{ page.props.data.active ? "Aktif" : "Tidak Aktif" }}
                      </td>
                    </tr>
                    <tr>
                      <td>Saldo</td>
                      <td>:</td>
                      <td>Rp. {{ formatNumber(page.props.data.balance) }}</td>
                    </tr>
                    <tr>
                      <td>Catatan</td>
                      <td>:</td>
                      <td>{{ page.props.data.notes }}</td>
                    </tr>
                  </tbody>
                </table>
              </q-tab-panel>

              <q-tab-panel name="history">
                <q-table
                  flat
                  bordered
                  square
                  color="primary"
                  class="full-height-table full-height-table2"
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
                    <div
                      class="full-width row flex-center text-grey-8 q-gutter-sm"
                    >
                      <span>
                        {{ message }}
                        {{ filter ? " with term " + filter : "" }}</span
                      >
                    </div>
                  </template>

                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="id" :props="props">
                        <div class="flex q-gutter-sm">
                          <div>
                            <b>#{{ props.row.id }}</b>
                          </div>
                          <div>
                            -
                            {{
                              $dayjs(
                                new Date(props.row.created_datetime)
                              ).format("DD/MM/YYYY hh:mm:ss")
                            }}
                          </div>
                          <div>
                            -
                            {{
                              props.row.created_by
                                ? props.row.created_by.username
                                : "--"
                            }}
                          </div>
                        </div>
                        <template v-if="$q.screen.lt.md">
                          <div class="">
                            {{
                              $CONSTANTS.STOCKMOVEMENT_REFTYPES[
                                props.row.ref_type
                              ]
                            }}
                          </div>
                          <div
                            :class="
                              props.row.quantity < 0
                                ? 'text-red-10'
                                : props.row.quantity > 0
                                ? 'text-green-10'
                                : ''
                            "
                          >
                            <q-icon
                              :name="
                                props.row.quantity < 0
                                  ? 'arrow_downward'
                                  : props.row.quantity > 0
                                  ? 'arrow_upward'
                                  : ''
                              "
                            />
                            {{ formatNumber(props.row.quantity) }}
                          </div>
                        </template>
                      </q-td>
                      <q-td key="type" :props="props">
                        {{
                          $CONSTANTS.STOCKMOVEMENT_REFTYPES[props.row.ref_type]
                        }}
                      </q-td>
                      <q-td key="quantity" :props="props">
                        <div
                          :class="
                            props.row.quantity < 0
                              ? 'text-red-10'
                              : props.row.quantity > 0
                              ? 'text-green-10'
                              : ''
                          "
                        >
                          <q-icon
                            :name="
                              props.row.quantity < 0
                                ? 'arrow_downward'
                                : props.row.quantity > 0
                                ? 'arrow_upward'
                                : ''
                            "
                          />
                          {{ formatNumber(props.row.quantity) }}
                        </div>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
