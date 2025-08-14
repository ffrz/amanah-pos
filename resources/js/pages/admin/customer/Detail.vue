<script setup>
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { computed, onMounted, reactive, ref } from "vue";
import { formatNumber, plusMinusSymbol } from "@/helpers/formatter";

const page = usePage();
const title = "Rincian Santri";
const tab = ref("main");
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  customer_id: page.props.data.id,
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
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
  {
    name: "amount",
    label: "Jumlah",
    field: "amount",
    align: "right",
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
    url: route("admin.customer-wallet-transaction.data"),
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
          @click="$inertia.get(route('admin.customer.index'))"
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
            $inertia.get(
              route('admin.customer.edit', { id: page.props.data.id })
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
              <q-tab name="main" label="Info Santri" />
              <q-tab name="history" label="Riwayat Transaksi" />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                <!-- <div class="text-subtitle1 text-bold text-grey-8">Info Santri</div> -->
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width: 120px">NIS</td>
                      <td style="width: 1px">:</td>
                      <td>{{ page.props.data.nis }}</td>
                    </tr>
                    <tr>
                      <td>Nama</td>
                      <td>:</td>
                      <td>{{ page.props.data.name }}</td>
                    </tr>
                    <tr>
                      <td>Nama Wali</td>
                      <td>:</td>
                      <td>{{ page.props.data.parent_name }}</td>
                    </tr>
                    <tr>
                      <td>No. Telepon</td>
                      <td>:</td>
                      <td>{{ page.props.data.phone }}</td>
                    </tr>
                    <tr>
                      <td>Alamat</td>
                      <td>:</td>
                      <td>{{ page.props.data.address }}</td>
                    </tr>
                    <tr>
                      <td>Saldo</td>
                      <td>:</td>
                      <td>Rp. {{ formatNumber(page.props.data.balance) }}</td>
                    </tr>
                    <tr>
                      <td>Status</td>
                      <td>:</td>
                      <td>
                        {{ page.props.data.active ? "Aktif" : "Tidak Aktif" }}
                      </td>
                    </tr>
                    <tr v-if="page.props.data.created_at">
                      <td>Dibuat</td>
                      <td>:</td>
                      <td>
                        {{
                          $dayjs(new Date(page.props.data.created_at)).format(
                            "DD MMMM YY HH:mm:ss"
                          )
                        }}
                      </td>
                    </tr>
                    <tr v-if="page.props.data.updated_at">
                      <td>Diperbarui</td>
                      <td>:</td>
                      <td>
                        {{
                          $dayjs(new Date(page.props.data.updated_at)).format(
                            "DD MMMM YY HH:mm:ss"
                          )
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Terakhir login</td>
                      <td>:</td>
                      <td>
                        {{
                          page.props.data.last_login_datetime
                            ? $dayjs(
                                new Date(page.props.data.last_login_datetime)
                              ).format("DD MMMM YY HH:mm:ss")
                            : "Belum pernah login"
                        }}
                      </td>
                    </tr>
                    <tr v-if="page.props.data.last_activity_datetime">
                      <td>Aktifitas Terakhir</td>
                      <td>:</td>
                      <td>
                        {{
                          $dayjs(
                            new Date(page.props.data.last_activity_datetime)
                          ).format("DD MMMM YY HH:mm:ss")
                        }}
                        <br />{{ page.props.data.last_activity_description }}
                      </td>
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
                        <div>
                          <b>#{{ props.row.id }}</b
                          >-
                          {{
                            $dayjs(new Date(props.row.created_datetime)).format(
                              "DD/MM/YYYY hh:mm:ss"
                            )
                          }}
                        </div>
                        <q-badge size="xs"
                          ><q-icon name="category" />
                          {{ props.row.type_label }}</q-badge
                        >
                        <template v-if="$q.screen.lt.md">
                          <div>
                            <q-icon name="money" />
                            <span
                              :class="
                                props.row.amount < 0
                                  ? 'text-red-10'
                                  : props.row.amount > 0
                                  ? 'text-green-10'
                                  : ''
                              "
                            >
                              {{ plusMinusSymbol(props.row.amount) }}
                              {{ formatNumber(props.row.amount) }}
                            </span>
                          </div>
                          <div class="text-grey-8">
                            <q-icon name="notes" /> {{ props.row.notes }}
                          </div>
                        </template>
                      </q-td>
                      <q-td key="notes" :props="props">
                        {{ props.row.notes }}
                      </q-td>
                      <q-td key="amount" :props="props">
                        <div
                          :class="
                            props.row.amount < 0
                              ? 'text-red-10'
                              : props.row.amount > 0
                              ? 'text-green-10'
                              : ''
                          "
                        >
                          {{ plusMinusSymbol(props.row.amount) }}
                          {{ formatNumber(props.row.amount) }}
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
