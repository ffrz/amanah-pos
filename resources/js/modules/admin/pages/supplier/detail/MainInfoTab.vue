<script setup>
import WaLink from "@/components/WaLink.vue";
import { handleDelete } from "@/helpers/client-req-handler";
import {
  formatMoney,
  formatDateTime, // [TAMBAHAN] Import helper tanggal
} from "@/helpers/formatter";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();

const confirmDelete = () => {
  handleDelete({
    message: `Hapus Pemasok ${page.props.data.name}?`,
    url: route("admin.supplier.delete", page.props.data.id),
    onSuccess: () => {
      router.get(route("admin.supplier.index"));
    },
  });
};
</script>

<template>
  <table class="detail">
    <tbody>
      <tr>
        <td colspan="3">
          <div class="text-bold text-grey-8">Info Pemasok</div>
        </td>
      </tr>
      <tr>
        <td style="width: 120px">Kode</td>
        <td style="width: 1px">:</td>
        <td>{{ page.props.data.code }}</td>
      </tr>
      <tr>
        <td>Nama</td>
        <td>:</td>
        <td>{{ page.props.data.name }}</td>
      </tr>
      <tr>
        <td>Saldo Deposit</td>
        <td>:</td>
        <td
          :class="
            page.props.data.wallet_balance < 0 ? 'text-red' : 'text-grey-8'
          "
        >
          {{ formatMoney(page.props.data.wallet_balance) }}
        </td>
      </tr>
      <tr>
        <td>
          {{ page.props.data.balance < 0 ? "Utang" : "Utang / Piutang" }}
        </td>
        <td>:</td>
        <td :class="page.props.data.balance < 0 ? 'text-red' : 'text-grey-8'">
          {{ formatMoney(page.props.data.balance) }}
        </td>
      </tr>

      <tr v-if="page.props.data.phone_1">
        <td>No Telepon</td>
        <td>:</td>
        <td><WaLink :phone="page.props.data.phone_1" /></td>
      </tr>
      <tr v-if="page.props.data.phone_2">
        <td>No Telepon 2</td>
        <td>:</td>
        <td><WaLink :phone="page.props.data.phone_2" /></td>
      </tr>
      <tr v-if="page.props.data.phone_3">
        <td>No Telepon 3</td>
        <td>:</td>
        <td><WaLink :phone="page.props.data.phone_3" /></td>
      </tr>

      <tr v-if="page.props.data.address">
        <td>Alamat</td>
        <td>:</td>
        <td>{{ page.props.data.address }}</td>
      </tr>
      <tr v-if="page.props.data.return_address">
        <td>Alamat Retur</td>
        <td>:</td>
        <td>{{ page.props.data.return_address }}</td>
      </tr>

      <tr v-if="page.props.data.url_1">
        <td>URL / Web 1</td>
        <td>:</td>
        <td>
          <a :href="page.props.data.url_1" target="_blank" class="text-primary">
            {{ page.props.data.url_1 }}
            <q-icon name="open_in_new" size="xs" />
          </a>
        </td>
      </tr>
      <tr v-if="page.props.data.url_2">
        <td>URL / Web 2</td>
        <td>:</td>
        <td>
          <a :href="page.props.data.url_2" target="_blank" class="text-primary">
            {{ page.props.data.url_2 }}
            <q-icon name="open_in_new" size="xs" />
          </a>
        </td>
      </tr>

      <tr
        v-if="
          page.props.data.bank_account_name_1 ||
          page.props.data.bank_account_number_1
        "
      >
        <td>Rekening 1</td>
        <td>:</td>
        <td>
          <div class="text-weight-bold">
            {{ page.props.data.bank_account_name_1 }}
            {{ page.props.data.bank_account_number_1 }}
          </div>
          <div
            v-if="page.props.data.bank_account_holder_1"
            class="text-caption text-grey-7"
          >
            a.n. {{ page.props.data.bank_account_holder_1 }}
          </div>
        </td>
      </tr>
      <tr
        v-if="
          page.props.data.bank_account_name_2 ||
          page.props.data.bank_account_number_2
        "
      >
        <td>Rekening 2</td>
        <td>:</td>
        <td>
          <div class="text-weight-bold">
            {{ page.props.data.bank_account_name_2 }}
            {{ page.props.data.bank_account_number_2 }}
          </div>
          <div
            v-if="page.props.data.bank_account_holder_2"
            class="text-caption text-grey-7"
          >
            a.n. {{ page.props.data.bank_account_holder_2 }}
          </div>
        </td>
      </tr>

      <tr v-if="page.props.data.notes">
        <td>Catatan</td>
        <td>:</td>
        <td style="white-space: pre-line">{{ page.props.data.notes }}</td>
      </tr>

      <tr>
        <td>Status</td>
        <td>:</td>
        <td>
          <q-badge :color="page.props.data.active ? 'positive' : 'grey'">
            {{ page.props.data.active ? "Aktif" : "Tidak Aktif" }}
          </q-badge>
        </td>
      </tr>

      <tr>
        <td class="text-grey-6" style="font-size: 0.85em">Dibuat</td>
        <td>:</td>
        <td class="text-grey-6" style="font-size: 0.85em">
          {{ formatDateTime(page.props.data.created_at) }}
        </td>
      </tr>
      <tr v-if="page.props.data.updated_at">
        <td class="text-grey-6" style="font-size: 0.85em">Diperbarui</td>
        <td>:</td>
        <td class="text-grey-6" style="font-size: 0.85em">
          {{ formatDateTime(page.props.data.updated_at) }}
        </td>
      </tr>
    </tbody>
  </table>

  <div class="q-pt-md" v-if="$can('admin.supplier.delete')">
    <q-btn
      icon="delete"
      label="Hapus"
      color="negative"
      @click="confirmDelete()"
    />
  </div>
</template>
