<script setup>
import { formatDate, formatDateTime, formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";
import { date } from "quasar";

const page = usePage();
const data = page.props.data;
const title = `Rincian Penjualan`;

const remainingPayment = data.grand_total - data.total_paid;

const print = () => {
  window.print();
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>
      <div class="row items-center q-gutter-x-sm">
        <span>{{ title }}</span>
      </div>
    </template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$goBack()"
        />
      </div>
    </template>
    <template #right-button>
      <q-btn icon="print" dense color="primary" flat rounded @click="print()" />
    </template>

    <q-page class="row justify-center print-visible">
      <div class="col col-lg-6 q-pa-xs" align="center">
        <q-card
          square
          flat
          bordered
          class="full-width"
          style="max-width: 1024px"
        >
          <q-card-section class="q-pa-xs">
            <div class="text-subtitle1 text-bold text-grey-8">
              INVOICE #{{ data.formatted_id }}
            </div>
            <div class="text-caption text-grey-6">
              {{
                date.formatDate(data.transaction_date, "DD MMMM YYYY, HH:mm")
              }}
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section class="row q-col-gutter-sm q-pa-sm" align="left">
            <div class="col-12 col-sm-6">
              <div class="text-subtitle2 text-bold text-grey-8">
                Info Pelanggan
              </div>
              <table class="full-width">
                <tbody>
                  <tr>
                    <td class="text-bold">
                      {{ data.customer?.name || "-" }}
                    </td>
                  </tr>
                  <tr>
                    <td>{{ data.customer?.address || "-" }}</td>
                  </tr>
                  <tr>
                    <td>{{ data.customer?.phone_number || "-" }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="col-12 col-sm-6" align="left">
              <div class="text-subtitle2 text-bold text-grey-8">Info Order</div>
              <table class="full-width">
                <tbody>
                  <tr>
                    <td class="text-grey-7" style="width: 130px">Waktu</td>
                    <td style="width: 1px">:</td>
                    <td>
                      {{ formatDateTime(data.datetime) }}
                    </td>
                  </tr>
                  <tr>
                    <td class="text-grey-7">Status Order</td>
                    <td style="width: 1px">:</td>
                    <td>
                      {{ $CONSTANTS.SALES_ORDER_STATUSES[data.status] }}
                    </td>
                  </tr>
                  <tr>
                    <td class="text-grey-7">Status Pembayaran</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS.SALES_ORDER_PAYMENT_STATUSES[
                          data.payment_status
                        ]
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td class="text-grey-7">Status Pengiriman</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS.SALES_ORDER_DELIVERY_STATUSES[
                          data.delivery_status
                        ]
                      }}
                    </td>
                  </tr>
                  <tr v-if="data.due_date">
                    <td class="text-grey-7">Jatuh Tempo</td>
                    <td>:</td>
                    <td>
                      {{ formatDate(data.due_date) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section class="q-pa-sm">
            <table class="full-width" style="border-collapse: collapse">
              <thead>
                <tr>
                  <th
                    class="text-left q-pa-sm"
                    style="border-bottom: 2px solid #ddd"
                  >
                    Item
                  </th>
                  <th
                    class="text-right q-pa-sm"
                    style="border-bottom: 2px solid #ddd"
                  >
                    Harga (Rp)
                  </th>
                  <th
                    class="text-center q-pa-sm"
                    style="border-bottom: 2px solid #ddd"
                  >
                    Jumlah
                  </th>
                  <th
                    class="text-right q-pa-sm"
                    style="border-bottom: 2px solid #ddd"
                  >
                    Total (Rp)
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in data.details" :key="item.id">
                  <td class="q-pa-sm" style="border-bottom: 1px solid #eee">
                    {{ item.product_name }}
                  </td>
                  <td
                    class="text-right q-pa-sm"
                    style="border-bottom: 1px solid #eee"
                  >
                    {{ formatNumber(item.price) }}
                  </td>
                  <td
                    class="text-center q-pa-sm"
                    style="border-bottom: 1px solid #eee"
                  >
                    {{ item.quantity }}
                  </td>
                  <td
                    class="text-right q-pa-sm"
                    style="border-bottom: 1px solid #eee"
                  >
                    {{ formatNumber(item.subtotal_price) }}
                  </td>
                </tr>
                <tr v-if="data.details.length == 0">
                  <td colspan="4" class="text-center text-grey">
                    Belum ada item
                  </td>
                </tr>
              </tbody>
            </table>
          </q-card-section>

          <q-separator />

          <q-card-section class="row" align="left">
            <div class="col-12 col-sm-6">
              <div class="text-subtitle2 text-bold text-grey-8 q-mb-xs">
                Catatan:
              </div>
              <div class="text-body2 text-grey-7 text-italic">
                {{ data.notes ? data.notes : "-" }}
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="row justify-end q-gutter-y-xs">
                <div class="col-12 row justify-between">
                  <div class="text-subtitle2 text-grey-7">Grand Total (Rp)</div>
                  <div class="text-subtitle2 text-bold">
                    {{ formatNumber(data.grand_total) }}
                  </div>
                </div>
                <div class="col-12 row justify-between">
                  <div class="text-subtitle2 text-green-7">
                    Total Dibayar (Rp)
                  </div>
                  <div class="text-subtitle2 text-bold text-green-7">
                    {{ formatNumber(data.total_paid) }}
                  </div>
                </div>
                <div
                  class="col-12 row justify-between"
                  :class="remainingPayment > 0 ? 'text-red' : 'text-positive'"
                >
                  <div class="text-subtitle1 text-bold">Sisa Pembayaran</div>
                  <div class="text-subtitle1 text-bold">
                    {{
                      remainingPayment > 0
                        ? formatNumber(remainingPayment)
                        : "LUNAS"
                    }}
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<style scoped>
@media print {
  .q-btn,
  .q-header,
  .q-footer,
  .q-drawer-container,
  .no-print {
    display: none !important;
  }
}
</style>
