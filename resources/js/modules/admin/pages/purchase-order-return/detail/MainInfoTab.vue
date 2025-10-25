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
          RETUR #{{ props.data.code }}
        </div>
        <div class="text-caption text-grey-8">
          {{ formatDateTime(props.data.datetime) }}
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
                    {{ props.data.supplier_name }} ({{
                      props.data.supplier?.code
                    }})
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
        <div class="text-subtitle2 text-bold text-grey-8">Info Retur</div>
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
              <td class="text-grey-7">Status Retur</td>
              <td style="width: 1px">:</td>
              <td>
                {{ props.data.status_label }}
              </td>
            </tr>
            <tr>
              <td class="text-grey-7">Status Refund</td>
              <td>:</td>
              <td>
                {{ props.data.refund_status_label }}
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
              {{ formatNumber(item.cost) }}
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
