<script setup>
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Pembelian";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('customer.purchasing-history.index'))"
        />
      </div>
    </template>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle2 text-bold text-grey-8">Info Order</div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 110px">Kode Trx</td>
                    <td style="width: 1px">:</td>
                    <td>
                      {{ page.props.data.code }}
                    </td>
                  </tr>
                  <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>
                      {{ formatDateTime(page.props.data.datetime) }}
                    </td>
                  </tr>
                  <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td>
                      <q-icon name="person" />
                      {{ page.props.data.customer?.code }} -
                      {{ page.props.data.customer?.name }}
                      <div v-if="page.props.data.customer?.phone">
                        <q-icon name="phone" />
                        {{ page.props.data.customer?.phone }}
                      </div>
                      <div v-if="page.props.data.customer?.address">
                        <q-icon name="home_pin" />
                        {{ page.props.data.customer?.address }}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Total Pembelian</td>
                    <td>:</td>
                    <td class="text-bold">
                      Rp. {{ formatNumber(page.props.data.grand_total) }}
                    </td>
                  </tr>
                  <tr v-if="page.props.data.cashier">
                    <td>Kasir</td>
                    <td>:</td>
                    <td>{{ page.props.data.cashier.name }}</td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes }}</td>
                  </tr>
                </tbody>
              </table>
              <div class="text-subtitle2 text-bold text-grey-8 q-pt-sm">
                Rincian Pembelian
              </div>
              <table class="full-width item-list-table">
                <thead>
                  <tr>
                    <th style="width: 1%">No</th>
                    <th>Item</th>
                    <th style="width: 25%">Jumlah (Rp)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in page.props.data.details"
                    :key="item.id"
                  >
                    <td align="right">{{ index + 1 }}</td>
                    <td>
                      {{ formatNumber(item.quantity) }}
                      {{ item.product_uom }}
                      {{ item.product_name }}
                      @ Rp {{ formatNumber(item.price) }}
                    </td>
                    <td align="right">
                      {{ formatNumber(item.subtotal_price) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="2" align="right">Total Pembelian</th>
                    <th align="right">
                      {{ formatNumber(page.props.data.total_price) }}
                    </th>
                  </tr>
                  <tr>
                    <td colspan="2" align="right">Pajak</td>
                    <td align="right">
                      {{ formatNumber(page.props.data.total_tax) }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" align="right">Diskon</td>
                    <td align="right">
                      {{ formatNumber(page.props.data.total_discount) }}
                    </td>
                  </tr>
                  <tr>
                    <th colspan="2" align="right">Grand Total</th>
                    <th align="right">
                      {{ formatNumber(page.props.data.grand_total) }}
                    </th>
                  </tr>
                </tfoot>
              </table>

              <div class="text-subtitle2 text-bold text-grey-8 q-pt-sm">
                Rincian Pembayaran
              </div>
              <table class="full-width item-list-table">
                <thead>
                  <tr>
                    <th style="width: 1%">No</th>
                    <th>Item</th>
                    <th style="width: 25%">Jumlah (Rp)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in page.props.data.payments"
                    :key="item.id"
                  >
                    <td align="right">{{ index + 1 }}</td>
                    <td>
                      {{ $CONSTANTS.SALES_ORDER_PAYMENT_TYPES[item.type] }}
                    </td>
                    <td align="right">
                      {{ formatNumber(item.amount) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="2" align="right">Total Bayar</th>
                    <th align="right">
                      {{ formatNumber(page.props.data.total_paid) }}
                    </th>
                  </tr>
                </tfoot>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<style scoped>
.item-list-table {
  border-collapse: collapse;
}

.item-list-table,
.item-list-table td,
.item-list-table th {
  padding: 0 5px;
  border: 1px solid #aaa;
}

.item-list-table th {
  background: #ccc;
  color: #333;
  font-weight: bold;
}
</style>
