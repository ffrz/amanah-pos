<script setup>
import { handleDelete } from "@/helpers/client-req-handler";
import {
  formatMoneyWithSymbol,
  formatNumberWithSymbol,
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
        <td>
          {{ page.props.data.balance < 0 ? "Utang" : "Utang / Piutang" }}
        </td>
        <td>:</td>
        <td :class="page.props.data.balance < 0 ? 'text-red' : 'text-green'">
          {{ formatMoneyWithSymbol(page.props.data.balance) }}
        </td>
      </tr>
      <tr v-if="page.props.data.phone_1">
        <td>No Telepon</td>
        <td>:</td>
        <td>{{ page.props.data.phone_1 }}</td>
      </tr>
      <tr v-if="page.props.data.phone_2">
        <td>No Telepon 2</td>
        <td>:</td>
        <td>{{ page.props.data.phone_2 }}</td>
      </tr>
      <tr v-if="page.props.data.phone_3">
        <td>No Telepon 3</td>
        <td>:</td>
        <td>{{ page.props.data.phone_3 }}</td>
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
      <tr
        v-if="
          page.props.data.bank_account_name_1 ||
          page.props.data.bank_account_number_1 ||
          page.props.data.bank_account_holder_1
        "
      >
        <td>Rek 1</td>
        <td>:</td>
        <td>
          {{ page.props.data.bank_account_name_1 }}
          {{ page.props.data.bank_account_number_1 }}
          a.n.
          {{ page.props.data.bank_account_holder_1 }}
        </td>
      </tr>
      <tr
        v-if="
          page.props.data.bank_account_name_2 ||
          page.props.data.bank_account_number_2 ||
          page.props.data.bank_account_holder_2
        "
      >
        <td>Rek 2</td>
        <td>:</td>
        <td>
          {{ page.props.data.bank_account_name_2 }}
          {{ page.props.data.bank_account_number_2 }}
          a.n.
          {{ page.props.data.bank_account_holder_2 }}
        </td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>
          {{ page.props.data.active ? "Aktif" : "Tidak Aktif" }}
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
