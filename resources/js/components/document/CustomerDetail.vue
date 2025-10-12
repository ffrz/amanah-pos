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
        <td style="width: 100px">Kode</td>
        <td style="width: 1px">:</td>
        <td>{{ data.code }}</td>
      </tr>
      <tr>
        <td>Nama</td>
        <td>:</td>
        <td>{{ data.name }}</td>
      </tr>
      <tr>
        <td>No Telepon</td>
        <td>:</td>
        <td>
          {{ data.phone }}
        </td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td>:</td>
        <td>{{ data.address }}</td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>{{ data.active ? "Aktif" : "Tidak Aktif" }}</td>
      </tr>
    </tbody>
  </table>
</template>
