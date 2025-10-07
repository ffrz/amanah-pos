<script setup>
const props = defineProps({
  data: {
    type: Object,
    default: null,
  },
});
</script>
<template>
  <table class="detail">
    <tbody>
      <tr>
        <td style="width: 100px">Nama</td>
        <td style="width: 1px">:</td>
        <td>{{ data.name }}</td>
      </tr>
      <tr>
        <td>No Telepon</td>
        <td>:</td>
        <td>
          {{ data.phone_1 }}
          <template v-if="data.phone_2"
            ><br />
            {{ data.phone_2 }}</template
          >
          <template v-if="data.phone_3"
            ><br />
            {{ data.phone_3 }}</template
          >
        </td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td>:</td>
        <td>{{ data.address }}</td>
      </tr>
      <tr>
        <td>Alamat Retur</td>
        <td>:</td>
        <td>{{ data.returned_address ?? "-" }}</td>
      </tr>
      <tr>
        <td>No Rek 1</td>
        <td>:</td>
        <td>
          {{ data.bank_account_name_1 }} a.n. {{ data.bank_account_holder_1 }}
          {{ data.bank_account_number_1 }}
        </td>
      </tr>
      <tr>
        <td>No Rek 2</td>
        <td>:</td>
        <td>
          {{ data.bank_account_name_2 }} a.n. {{ data.bank_account_holder_2 }}
          {{ data.bank_account_number_2 }}
        </td>
      </tr>
      <tr>
        <td>URL</td>
        <td>:</td>
        <td>
          <template v-if="data.url_1">
            {{ data.url_1 }}
          </template>
          <template v-if="data.url_2">
            <br />
            {{ data.url_2 }}
          </template>
        </td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>{{ data.active ? "Aktif" : "Tidak Aktif" }}</td>
      </tr>
    </tbody>
  </table>
</template>
