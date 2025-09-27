<script setup>
import { usePage } from "@inertiajs/vue3";
import {
  formatDateTime,
  formatDateTimeFromNow,
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
            Info Pelanggan

            <q-btn
              v-if="$can('admin.customer.edit')"
              icon="edit"
              size="sm"
              dense
              flat
              rounded
              class="q-ml-sm"
              color="grey"
              @click="
                $inertia.get(
                  route('admin.customer.edit', { id: page.props.data.id })
                )
              "
            />
          </div>
        </td>
      </tr>
      <tr>
        <td style="width: 120px">Username</td>
        <td style="width: 1px">:</td>
        <td>{{ page.props.data.code }}</td>
      </tr>
      <tr>
        <td>Jenis</td>
        <td>:</td>
        <td>
          {{ $CONSTANTS.CUSTOMER_TYPES[page.props.data.type] }}
        </td>
      </tr>
      <tr>
        <td>Nama</td>
        <td>:</td>
        <td>{{ page.props.data.name }}</td>
      </tr>
      <tr>
        <td>No. Telepon</td>
        <td>:</td>
        <td>{{ page.props.data.phone ? page.props.data.phone : "-" }}</td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td>:</td>
        <td>{{ page.props.data.address ? page.props.data.address : "-" }}</td>
      </tr>
      <tr>
        <td>Saldo Wallet</td>
        <td>:</td>
        <td>Rp. {{ formatNumber(page.props.data.wallet_balance) }}</td>
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
          <div class="text-bold text-grey-8 q-mt-md">Info Akun</div>
        </td>
      </tr>
      <tr v-if="page.props.data.created_at">
        <td>Dibuat</td>
        <td>:</td>
        <td>
          {{ formatDateTimeFromNow(page.props.data.created_at) }} oleh
          {{ page.props.data.creator.name }}
          ({{ formatDateTime(page.props.data.created_at) }})
        </td>
      </tr>
      <tr v-if="page.props.data.updated_at">
        <td>Diperbarui</td>
        <td>:</td>
        <td>
          {{ formatDateTimeFromNow(page.props.data.updated_at) }}
          oleh
          {{ page.props.data.updater.name }}
          ({{ formatDateTime(page.props.data.updated_at) }})
        </td>
      </tr>
      <tr>
        <td>Terakhir login</td>
        <td>:</td>
        <td>
          {{
            page.props.data.last_login_datetime
              ? formatDateTime(page.props.data.last_login_datetime)
              : "Belum pernah login"
          }}
          <span class="text-grey-8"
            >({{
              formatDateTimeFromNow(page.props.data.last_login_datetime)
            }})</span
          >
        </td>
      </tr>
      <tr v-if="page.props.data.last_activity_datetime">
        <td>Aktifitas Terakhir</td>
        <td>:</td>
        <td>
          {{ formatDateTime(page.props.data.last_activity_datetime) }}
          <span class="text-grey-8"
            >({{
              formatDateTimeFromNow(page.props.data.last_activity_datetime)
            }})<br />{{ page.props.data.last_activity_description }}</span
          >
        </td>
      </tr>
    </tbody>
  </table>
</template>
