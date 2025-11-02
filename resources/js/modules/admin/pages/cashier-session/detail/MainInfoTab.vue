<script setup>
import { router, usePage } from "@inertiajs/vue3";
import {
  formatDateTime,
  formatDateTimeFromNow,
  formatNumber,
} from "@/helpers/formatter";

const page = usePage();
</script>

<template>
  <div class="text-bold text-grey-8">Info Sesi</div>
  <table class="detail">
    <tbody>
      <tr>
        <td>Kode</td>
        <td>:</td>
        <td>{{ page.props.data.code }}</td>
      </tr>
      <tr>
        <td>Terminal</td>
        <td>:</td>
        <td>{{ page.props.data.cashier_terminal.name }}</td>
      </tr>
      <tr>
        <td>Kasir</td>
        <td>:</td>
        <td>
          <my-link :href="route('admin.user.detail', page.props.data.user.id)">
            {{ page.props.data.user.name }} -
            {{ page.props.data.user.username }}
          </my-link>
        </td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>
          {{ !page.props.data.is_closed ? "Aktif" : "Selesai" }}
        </td>
      </tr>
    </tbody>
  </table>
  <div class="q-mt-md text-grey-8 text-bold">Info Buka Sesi</div>
  <table class="detail with-currency">
    <tbody>
      <tr>
        <td>Saldo Awal</td>
        <td>:</td>
        <td>Rp.</td>
        <td class="text-right">
          {{ formatNumber(page.props.data.opening_balance) }}
        </td>
        <td></td>
      </tr>
      <tr v-if="page.props.data.opening_notes">
        <td>Catatan</td>
        <td>:</td>
        <td>{{ page.props.data.opening_notes }}</td>
      </tr>
    </tbody>
  </table>
  <div class="q-mt-md text-bold text-grey-8">Rincian Pendapatan & Kas</div>
  <table class="detail with-currency">
    <tbody>
      <tr>
        <td>Total Penjualan</td>
        <td>:</td>
        <td>Rp.</td>
        <td class="text-right">
          {{ formatNumber(page.props.data.total_sales) }}
        </td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <div class="q-mt-md text-bold text-grey-8">Rincian Kas</div>
  <table class="detail with-currency">
    <tbody>
      <tr>
        <td>Total Pemasukan</td>
        <td>:</td>
        <td>Rp.</td>
        <td class="text-right">
          {{ formatNumber(page.props.data.total_income) }}
        </td>
        <td></td>
      </tr>
      <tr>
        <td>Total Pengeluaran</td>
        <td>:</td>
        <td>Rp.</td>
        <td class="text-right">
          {{ formatNumber(page.props.data.total_expense) }}
        </td>
        <td></td>
      </tr>
      <template v-if="!page.props.data.is_closed">
        <tr>
          <td>Saldo Aktual</td>
          <td>:</td>
          <td>Rp.</td>
          <td class="text-right">
            {{ formatNumber(page.props.data.actual_balance) }}
          </td>
          <td></td>
        </tr>
      </template>
    </tbody>
  </table>
  <table class="detail with-currency">
    <tbody>
      <template v-if="page.props.data.is_closed">
        <div class="q-mt-md text-bold text-grey-8">Info Tutup Sesi</div>
        <tr>
          <td>Saldo Akhir</td>
          <td>:</td>
          <td>Rp.</td>
          <td class="text-right">
            {{ formatNumber(page.props.data.closing_balance) }}
          </td>
          <td></td>
        </tr>
        <tr v-if="page.props.data.closing_notes">
          <td>Catatan</td>
          <td>:</td>
          <td>{{ page.props.data.closing_notes }}</td>
        </tr>
      </template>

      <template v-if="$can('admin.cashier-session.close')">
        <tr v-if="!page.props.data.is_closed">
          <td colspan="100%">
            <div class="q-my-md">
              <q-btn
                class="full-width"
                color="accent"
                icon="logout"
                @click="
                  $inertia.get(
                    route('admin.cashier-session.close', {
                      id: page.props.data.id,
                    })
                  )
                "
              >
                Tutup Sesi Kasir
              </q-btn>
            </div>
          </td>
        </tr>
      </template>
    </tbody>
  </table>
  <div class="q-mt-md text-bold text-grey-8">Info Rekaman</div>
  <table class="detail">
    <tbody>
      <tr v-if="page.props.data.created_at">
        <td colspan="100%">
          Dibuat {{ formatDateTimeFromNow(page.props.data.created_at) }} oleh
          {{ page.props.data.creator.name }}
          ({{ formatDateTime(page.props.data.created_at) }})
        </td>
      </tr>
      <tr v-if="page.props.data.updated_at">
        <td colspan="100%">
          Diperbarui
          {{ formatDateTimeFromNow(page.props.data.updated_at) }}
          oleh
          {{ page.props.data.updater.name }}
          ({{ formatDateTime(page.props.data.updated_at) }})
        </td>
      </tr>
    </tbody>
  </table>
</template>
<style>
.detail td:first-child {
  width: 130px;
}
.detail td:nth-child(2) {
  width: 1px;
}
.detail.with-currency td:nth-child(3) {
  width: 1px;
}
.detail.with-currency td:nth-child(4) {
  width: 100px;
}
</style>
