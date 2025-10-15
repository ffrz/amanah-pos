<script setup>
import { formatDate, formatDateTime, formatNumber } from "@/helpers/formatter";

const props = defineProps({
  data: Object,
});
</script>

<template>
  <div class="column">
    <q-card-section class="q-pa-none">
      <div class="bg-grey-2 q-pa-xs">
        <div class="text-subtitle1 text-bold text-grey-10">
          INVOICE #{{ props.data.code }}
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="row q-col-gutter-sm q-pa-sm" align="left">
      <div class="col-12 col-sm-6">
        <div class="text-subtitle2 text-bold text-grey-8">Info Pemasok</div>
        <table class="full-width">
          <tbody>
            <tr>
              <td>
                <template v-if="props.data.supplier">
                  <i-link
                    :href="
                      route('admin.supplier.detail', {
                        id: props.data.supplier_id,
                      })
                    "
                  >
                    <q-icon class="inline-icon" name="person" />
                    {{ props.data.supplier_name }}
                  </i-link>
                </template>
                <template v-else>
                  <div>
                    <q-icon class="inline-icon" name="person" /> Pemasok Umum
                  </div>
                </template>
              </td>
            </tr>
            <tr v-if="props.data.supplier_phone">
              <td>
                <q-icon class="inline-icon" name="phone" />
                {{ props.data.supplier_phone }}
              </td>
            </tr>
            <tr v-if="props.data.supplier_address">
              <td>
                <q-icon class="inline-icon" name="home_pin" />
                {{ props.data.supplier_address }}
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
                {{ $CONSTANTS.PURCHASE_ORDER_STATUSES[props.data.status] }}
              </td>
            </tr>
            <tr>
              <td class="text-grey-7">Status Pembayaran</td>
              <td>:</td>
              <td>
                {{
                  $CONSTANTS.PURCHASE_ORDER_PAYMENT_STATUSES[
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
                  $CONSTANTS.PURCHASE_ORDER_DELIVERY_STATUSES[
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
              colspan="2"
              class="text-center q-pa-sm"
              style="border-bottom: 2px solid #ddd"
            >
              Qty
            </th>
            <th
              class="text-right q-pa-sm"
              style="border-bottom: 2px solid #ddd"
            >
              Harga Beli (Rp)
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
              <i-link
                :href="route('admin.product.detail', { id: item.product_id })"
              >
                {{ item.product_name }}
              </i-link>
            </td>
            <td
              class="text-right q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              {{ item.quantity }}
            </td>
            <td>
              {{ item.product_uom }}
            </td>
            <td
              class="text-right q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              {{ formatNumber(item.cost) }}
            </td>
            <td
              class="text-right q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              {{ formatNumber(item.subtotal_cost) }}
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
        <div class="row justify-end q-gutter-y-xs">
          <div class="col-12 row justify-between">
            <div class="text-subtitle2 text-grey-7">Grand Total (Rp)</div>
            <div class="text-subtitle2 text-bold">
              {{ formatNumber(props.data.grand_total) }}
            </div>
          </div>
        </div>
      </div>
    </q-card-section>
  </div>
</template>
