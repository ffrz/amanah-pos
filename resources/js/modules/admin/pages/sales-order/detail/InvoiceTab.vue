<script setup>
import WaLink from "@/components/WaLink.vue";
import {
  formatDate,
  formatDateTime,
  formatNumber,
  formatNumberWithSymbol,
} from "@/helpers/formatter";
import { computed } from "vue";

const props = defineProps({
  data: Object,
});

const total = computed(() => {
  return (
    parseFloat(props.data.grand_total) + parseFloat(props.data.total_discount)
  );
});
</script>

<template>
  <div class="column">
    <q-card-section class="q-pa-none">
      <div class="bg-grey-2 q-pa-xs">
        <div class="text-subtitle1 text-bold text-grey-10">
          INVOICE #{{ props.data.code }}
        </div>
        <div class="text-caption text-grey-8">
          Kasir: {{ props.data.cashier.username }} -
          {{ props.data.cashier.name }} |
          {{ formatDateTime(props.data.datetime) }}
          <template v-if="props.data.cashier_session">
            | Session ID: {{ props.data.cashier_session.id }} | Terminal:
            {{ props.data.cashier_session.cashier_terminal.name }}
          </template>
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="row q-col-gutter-sm q-pa-sm" align="left">
      <div class="col-12 col-sm-6">
        <div class="text-subtitle2 text-bold text-grey-8">Info Pelanggan</div>
        <table class="full-width">
          <tbody>
            <tr>
              <td>
                <template v-if="props.data.customer">
                  <q-icon class="inline-icon" name="person" />
                  <i-link
                    :href="
                      route('admin.customer.detail', {
                        id: props.data.customer_id,
                      })
                    "
                  >
                    {{ props.data.customer_name }} ({{
                      props.data.customer_code
                    }})
                  </i-link>
                </template>
                <template v-else>
                  <div>
                    <q-icon class="inline-icon" name="person" /> Pelanggan Umum
                  </div>
                </template>
              </td>
            </tr>
            <tr v-if="props.data.customer_phone">
              <td>
                <q-icon class="inline-icon" name="phone" />
                <WaLink :phone="props.data.customer_phone" />
              </td>
            </tr>
            <tr v-if="props.data.customer_address">
              <td>
                <q-icon class="inline-icon" name="home_pin" />
                <template v-if="1">
                  {{ props.data.customer_address }}
                </template>
              </td>
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
                {{ formatDateTime(props.data.datetime) }}
              </td>
            </tr>
            <tr>
              <td class="text-grey-7">Status Order</td>
              <td style="width: 1px">:</td>
              <td>
                {{ $CONSTANTS.SALES_ORDER_STATUSES[props.data.status] }}
              </td>
            </tr>
            <tr v-if="props.data.status == 'closed'">
              <td class="text-grey-7">Status Pembayaran</td>
              <td>:</td>
              <td>
                {{
                  $CONSTANTS.SALES_ORDER_PAYMENT_STATUSES[
                    props.data.payment_status
                  ]
                }}
              </td>
            </tr>
            <tr v-if="false">
              <td class="text-grey-7">Status Pengiriman</td>
              <td>:</td>
              <td>
                {{
                  $CONSTANTS.SALES_ORDER_DELIVERY_STATUSES[
                    props.data.delivery_status
                  ]
                }}
              </td>
            </tr>
            <tr v-if="props.data.due_date">
              <td class="text-grey-7">Jatuh Tempo</td>
              <td>:</td>
              <td>
                {{ formatDate(props.data.due_date) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pa-sm">
      <table class="full-width" style="border-collapse: collapse">
        <thead class="text-grey-8">
          <tr>
            <th class="text-left q-pa-sm" style="border-bottom: 2px solid #ddd">
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
          <tr v-for="item in props.data.details" :key="item.id">
            <td class="q-pa-sm" style="border-bottom: 1px solid #eee">
              <i-link :href="route('admin.product.detail', item.product_id)">
                {{ item.product_name }}
              </i-link>
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
              <span>{{ formatNumber(item.quantity) }}</span>
              <span class="text-caption q-ml-xs">{{ item.product_uom }}</span>
            </td>

            <td
              class="text-right q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              {{ formatNumber(item.subtotal_price) }}
            </td>
          </tr>

          <tr v-if="props.data.details.length == 0">
            <td colspan="4" class="text-center text-grey">Belum ada item</td>
          </tr>
        </tbody>
      </table>
    </q-card-section>

    <q-separator />

    <q-card-section class="row" align="left">
      <div class="col-12 col-sm-6">
        <div class="text-subtitle2 text-bold text-grey-8 q-mb-xs">Catatan:</div>
        <div class="text-body2 text-grey-7 text-italic">
          {{ props.data.notes ? props.data.notes : "-" }}
        </div>
      </div>
      <div class="col-12 col-sm-6">
        <template v-if="props.data.status == 'closed' && props.data.change > 0">
          <div class="row justify-end q-gutter-y-xs">
            <div class="col-12 row justify-between">
              <div class="text-subtitle2 text-grey-7">Jumlah Bayar</div>
              <div class="text-subtitle2 text-bold">
                {{
                  formatNumber(
                    parseInt(props.data.total_paid) +
                      parseInt(props.data.change)
                  )
                }}
              </div>
            </div>
          </div>
          <div
            v-if="props.data.status == 'closed'"
            class="row justify-end q-gutter-y-xs"
          >
            <div class="col-12 row justify-between">
              <div class="text-subtitle2 text-grey-7">Kembalian</div>
              <div class="text-subtitle2 text-bold">
                {{ formatNumber(props.data.change) }}
              </div>
            </div>
          </div>
        </template>
        <div
          v-if="total != parseFloat(data.grand_total)"
          class="row justify-end q-gutter-y-xs"
        >
          <div class="col-12 row justify-between">
            <div class="text-subtitle2 text-grey-7">Total</div>
            <div class="text-subtitle2 text-bold">
              Rp
              {{ formatNumber(total) }}
            </div>
          </div>
        </div>
        <div
          v-if="props.data.total_discount != 0"
          class="row justify-end q-gutter-y-xs text-negative"
        >
          <div class="col-12 row justify-between">
            <div class="text-subtitle2">Diskon Akhir</div>
            <div class="text-subtitle2 text-bold">
              Rp -{{ formatNumber(props.data.total_discount) }}
            </div>
          </div>
        </div>
        <div class="row justify-end q-gutter-y-xs">
          <div class="col-12 row justify-between">
            <div class="text-subtitle1 text-grey-7">Grand Total</div>
            <div class="text-subtitle1 text-bold">
              Rp {{ formatNumber(props.data.grand_total) }}
            </div>
          </div>
        </div>
        <div
          v-if="props.data.status == 'closed' && props.data.total_return != 0"
          class="row justify-end q-gutter-y-xs"
        >
          <div class="col-12 row justify-between">
            <div class="text-subtitle2 text-grey-7">Total Retur (Rp)</div>
            <div class="text-subtitle2 text-bold">
              {{ formatNumber(props.data.total_return) }}
            </div>
          </div>
        </div>
        <!-- <div class="row justify-end q-gutter-y-xs">
          <div class="col-12 row justify-between">
            <div class="text-subtitle2 text-grey-7">Total Dibayar (Rp)</div>
            <div class="text-subtitle2 text-bold">
              {{ formatNumber(Math.abs(props.data.total_paid)) }}
            </div>
          </div>
        </div> -->
        <div
          v-if="props.data.status == 'closed' && props.data.balance != 0"
          class="row justify-end q-gutter-y-xs"
        >
          <div class="col-12 row justify-between">
            <div class="text-subtitle2 text-grey-7">Saldo (Rp)</div>
            <div class="text-subtitle2 text-bold">
              {{ formatNumberWithSymbol(props.data.balance) }}
            </div>
          </div>
        </div>
      </div>
    </q-card-section>
  </div>
</template>
