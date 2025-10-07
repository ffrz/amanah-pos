<script setup>
import { formatMoneyWithSymbol, formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { computed } from "vue";

const page = usePage();
const title = `Rincian Penyesuaian Stok #${page.props.data.id}`;
const rows = page.props.details;

const columns = [
  {
    name: "no",
    label: "#",
    field: "no",
    align: "left",
  },
  {
    name: "product_name",
    label: "Nama Produk",
    field: "product_name",
    align: "left",
  },
  {
    name: "uom",
    label: "Satuan",
    field: "uom",
    align: "left",
  },
  {
    name: "old_quantity",
    label: "Stok Lama",
    field: "old_quantity",
    align: "right",
    format: (val) => formatNumber(val),
  },
  {
    name: "new_quantity",
    label: "Stok Baru",
    field: "new_quantity",
    align: "right",
    format: (val) => formatNumber(val),
  },
  {
    name: "balance",
    label: "Selisih Stok",
    field: "balance",
    align: "right",
    format: (val) => formatNumber(val),
  },
  {
    name: "cost_balance",
    label: "Selisih Modal",
    field: "cost_balance",
    align: "right",
  },
  {
    name: "price_balance",
    label: "Selisih Harga",
    field: "price_balance",
    align: "right",
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
];

const $q = useQuasar();
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "no");
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>

    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.stock-adjustment.index'))"
        />
      </div>
    </template>
    <div class="row justify-center">
      <div class="col col-lg-8 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="q-card col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-9">
                Info Penyesuaian Stok
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 120px">ID</td>
                    <td style="width: 1px">:</td>
                    <td># {{ page.props.data.id }}</td>
                  </tr>
                  <tr>
                    <td>Jenis</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS.STOCK_ADJUSTMENT_TYPES[page.props.data.type]
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS.STOCK_ADJUSTMENT_STATUSES[
                          page.props.data.status
                        ]
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>
                      {{
                        $dayjs(page.props.data.datetime).format(
                          "dddd, D MMMM YYYY pukul HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr v-if="!!page.props.data.created_at">
                    <td>Dibuat Oleh</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.creator">
                        <i-link
                          :href="
                            route('admin.user.detail', {
                              id: page.props.data.creator,
                            })
                          "
                        >
                          {{ page.props.data.creator.username }} -
                          {{ page.props.data.creator.name }}
                        </i-link>
                        -
                      </template>
                      {{
                        $dayjs(new Date(page.props.data.created_at)).format(
                          "dddd, D MMMM YYYY pukul HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr v-if="!!page.props.data.updater">
                    <td>Diperbarui oleh</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.updater">
                        <i-link
                          :href="
                            route('admin.user.detail', {
                              id: page.props.data.updater,
                            })
                          "
                        >
                          {{ page.props.data.updater.username }} -
                          {{ page.props.data.updater.name }}
                        </i-link>
                        -
                      </template>
                      {{
                        $dayjs(new Date(page.props.data.updated_at)).format(
                          "dddd, D MMMM YYYY pukul HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Total Modal</td>
                    <td>:</td>
                    <td>
                      <div
                        :class="
                          page.props.data.total_cost < 0
                            ? 'text-red-10'
                            : page.props.data.total_cost > 0
                            ? 'text-green-10'
                            : ''
                        "
                      >
                        {{ formatMoneyWithSymbol(page.props.data.total_cost) }}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Total Harga</td>
                    <td>:</td>
                    <td>
                      <div
                        :class="
                          page.props.data.total_price < 0
                            ? 'text-red-10'
                            : page.props.data.total_price > 0
                            ? 'text-green-10'
                            : ''
                        "
                      >
                        {{ formatMoneyWithSymbol(page.props.data.total_price) }}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>
                      {{ page.props.data.notes }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-9 q-pt-md">
                Rincian
              </div>
              <q-table
                flat
                bordered
                square
                color="primary"
                class="full-height-table"
                row-key="id"
                dense
                :columns="computedColumns"
                :rows="rows"
                :hide-pagination="true"
                :rows-per-page-options="[0]"
              >
                <template v-slot:body-cell-no="props">
                  <q-td :props="props">
                    <template v-if="!$q.screen.lt.md">
                      {{ props.rowIndex + 1 }}
                    </template>
                    <template v-else>
                      <div class="text-grey-9">
                        <b>#{{ props.rowIndex + 1 }}</b> -
                        {{ props.row.product_name }}
                      </div>
                      <div>
                        Stok Lama: {{ formatNumber(props.row.old_quantity) }}
                        {{ props.row.uom }}<br />
                        Stok Baru: {{ formatNumber(props.row.new_quantity) }}
                        {{ props.row.uom }}<br />

                        <div
                          :class="
                            props.row.balance < 0
                              ? 'text-red-10'
                              : props.row.balance > 0
                              ? 'text-green-10'
                              : ''
                          "
                        >
                          Selisih:
                          {{
                            (props.row.balance > 0 ? "+" : "") +
                            formatNumber(props.row.balance)
                          }}
                          {{ props.row.uom }}<br />
                          (Rp. {{ formatNumber(props.row.subtotal_cost) }} / Rp.
                          {{ formatNumber(props.row.subtotal_price) }})
                        </div>
                        <br />
                        {{ props.row.notes }}
                      </div>
                    </template>
                  </q-td>
                </template>
                <template v-slot:body-cell-balance="props">
                  <q-td :props="props">
                    <div
                      :class="
                        props.row.balance < 0
                          ? 'text-red-10'
                          : props.row.balance > 0
                          ? 'text-green-10'
                          : ''
                      "
                    >
                      {{
                        (props.row.balance > 0 ? "+" : "") +
                        formatNumber(props.row.balance)
                      }}
                    </div>
                  </q-td>
                </template>
                <template v-slot:body-cell-cost_balance="props">
                  <q-td :props="props">
                    <div
                      :class="
                        props.row.balance < 0
                          ? 'text-red-10'
                          : props.row.balance > 0
                          ? 'text-green-10'
                          : ''
                      "
                    >
                      {{ formatNumber(props.row.subtotal_cost) }}
                    </div>
                  </q-td>
                </template>
                <template v-slot:body-cell-price_balance="props">
                  <q-td :props="props">
                    <div
                      :class="
                        props.row.balance < 0
                          ? 'text-red-10'
                          : props.row.balance > 0
                          ? 'text-green-10'
                          : ''
                      "
                    >
                      {{ formatNumber(props.row.subtotal_price) }}
                    </div>
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
