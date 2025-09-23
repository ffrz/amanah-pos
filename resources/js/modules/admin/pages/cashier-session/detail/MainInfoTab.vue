<script setup>
import { usePage } from "@inertiajs/vue3";
import {
  formatDateTime,
  formatDateTimeFromNow,
  formatMoney,
} from "@/helpers/formatter";

const page = usePage();
</script>

<template>
  <table class="detail">
    <tbody>
      <tr>
        <td colspan="3">
          <div class="text-bold text-grey-8">
            Info Sesi

            <!-- <q-btn
              icon="edit"
              size="sm"
              dense
              flat
              rounded
              class="q-ml-sm"
              color="grey"
              @click="
                $inertia.get(
                  route('admin.cashier-sessions.edit', {
                    id: page.props.data.id,
                  })
                )
              "
            /> -->
          </div>
        </td>
      </tr>
      <tr>
        <td style="width: 120px">Session ID</td>
        <td style="width: 1px">:</td>
        <td>{{ page.props.data.id }}</td>
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
          {{ page.props.data.user.username }} - {{ page.props.data.user.name }}
        </td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>
          {{ !page.props.data.is_closed ? "Aktif" : "Ditutup" }}
        </td>
      </tr>
      <tr>
        <td colspan="100%">
          <div class="q-mt-sm text-grey-8">Info Buka Sesi</div>
        </td>
      </tr>
      <tr>
        <td>Saldo</td>
        <td>:</td>
        <td>
          {{ formatMoney(page.props.data.opening_balance) }}
        </td>
      </tr>
      <tr v-if="page.props.data.opening_notes">
        <td>Catatan</td>
        <td>:</td>
        <td>{{ page.props.data.opening_notes }}</td>
      </tr>

      <template v-if="page.props.data.is_closed">
        <tr>
          <td colspan="100%">
            <div class="q-mt-sm text-grey-8">Info Tutup Sesi</div>
          </td>
        </tr>
        <tr>
          <td>Saldo</td>
          <td>:</td>
          <td>
            {{ formatMoney(page.props.data.closing_balance) }}
          </td>
        </tr>
        <tr v-if="page.props.data.closing_notes">
          <td>Catatan</td>
          <td>:</td>
          <td>{{ page.props.data.closing_notes }}</td>
        </tr>
      </template>

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
