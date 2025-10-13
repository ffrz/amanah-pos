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
        <td style="width: 100px">Kode / Nama</td>
        <td style="width: 1px">:</td>
        <td>{{ data.name }}</td>
      </tr>
      <tr>
        <td>Deskripsi</td>
        <td>:</td>
        <td>{{ data.description }}</td>
      </tr>
      <tr>
        <td>Barcode</td>
        <td>:</td>
        <td>{{ data.barcode }}</td>
      </tr>
      <tr>
        <td>Jenis</td>
        <td>:</td>
        <td>
          {{ $CONSTANTS.PRODUCT_TYPES[data.type] }}
        </td>
      </tr>
      <tr>
        <td>Kategori</td>
        <td>:</td>
        <td>{{ data.category?.name }}</td>
      </tr>
      <tr>
        <td>Pemasok</td>
        <td>:</td>
        <td>{{ data.supplier?.name }}</td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>{{ data.active ? "Aktif" : "Tidak Aktif" }}</td>
      </tr>
    </tbody>
  </table>
</template>
