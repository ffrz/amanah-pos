<script setup>
import { usePage } from "@inertiajs/vue3";
import {
  formatDateTime,
  formatDateTimeFromNow,
  formatMoneyWithSymbol,
  formatNumber,
} from "@/helpers/formatter";

const page = usePage();
</script>

<template>
  <table class="detail">
    <tbody>
      <tr>
        <td colspan="3">
          <div class="text-bold text-grey-8">
            Info Cash Register

            <q-btn
              icon="edit"
              size="sm"
              dense
              flat
              rounded
              class="q-ml-sm"
              color="grey"
              @click="
                $inertia.get(
                  route('admin.cash-register.edit', { id: page.props.data.id })
                )
              "
            />
          </div>
        </td>
      </tr>
      <tr>
        <td style="width: 120px">Nama</td>
        <td style="width: 1px">:</td>
        <td>{{ page.props.data.name }}</td>
      </tr>
      <tr>
        <td>Lokasi</td>
        <td>:</td>
        <td>{{ page.props.data.location }}</td>
      </tr>
      <tr>
        <td>Akun Keuangan</td>
        <td>:</td>
        <td>{{ page.props.data.finance_account.name }}</td>
      </tr>
      <tr>
        <td>Saldo</td>
        <td>:</td>
        <td>
          {{ formatMoneyWithSymbol(page.props.data.finance_account.balance) }}
        </td>
      </tr>
      <tr>
        <td>Catatan</td>
        <td>:</td>
        <td>{{ page.props.data.notes }}</td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>
          {{ page.props.data.active ? "Aktif" : "Tidak Aktif" }}
        </td>
      </tr>
      <tr>
        <td colspan="3">
          <div class="text-bold text-grey-8 q-mt-md">Info Rekaman</div>
        </td>
      </tr>
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
