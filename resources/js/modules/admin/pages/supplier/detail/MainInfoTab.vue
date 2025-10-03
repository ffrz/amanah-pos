<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
</script>

<template>
  <table class="detail">
    <tbody>
      <tr>
        <td colspan="3">
          <div class="text-bold text-grey-8">
            Info Pemasok

            <q-btn
              v-if="$can('admin.supplier.edit')"
              icon="edit"
              size="sm"
              dense
              flat
              rounded
              class="q-ml-sm"
              color="grey"
              @click="
                $inertia.get(
                  route('admin.supplier.edit', { id: page.props.data.id })
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
</template>
