<script setup>
import { usePage } from "@inertiajs/vue3";
import { formatNumber } from "@/helpers/formatter";

const page = usePage();
</script>

<template>
  <table class="detail">
    <tbody>
      <tr>
        <td style="width: 170px">Nama</td>
        <td style="width: 1px">:</td>
        <td>{{ page.props.data.name }}</td>
      </tr>
      <tr>
        <td>Jenis</td>
        <td>:</td>
        <td>
          {{ $CONSTANTS.FINANCE_ACCOUNT_TYPES[page.props.data.type] }}
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
      <tr>
        <td>Catatan</td>
        <td>:</td>
        <td>{{ page.props.data.notes }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-bold text-grey-9">
          <div class="q-mt-sm">Opsi Lanjutan</div>
        </td>
      </tr>
      <tr>
        <td>Tampilkan di Penjualan</td>
        <td>:</td>
        <td>
          {{ page.props.data.show_in_pos_payment ? "Ya" : "Tidak" }}
        </td>
      </tr>
      <tr>
        <td>Tampilkan di Pembelian</td>
        <td>:</td>
        <td>
          {{ page.props.data.show_in_purchasing ? "Ya" : "Tidak" }}
        </td>
      </tr>
      <tr>
        <td>Tampilkan di Wallet</td>
        <td>:</td>
        <td>
          {{ page.props.data.has_wallet_access ? "Ya" : "Tidak" }}
        </td>
      </tr>
    </tbody>
  </table>
</template>
