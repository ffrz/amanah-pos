<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { formatDateTime, formatDateTimeFromNow } from "@/helpers/formatter";
import { handleDelete } from "@/helpers/client-req-handler";
import WaLink from "@/components/WaLink.vue";

const page = usePage();

const confirmDelete = () => {
  handleDelete({
    message: `Hapus Teknisi ${page.props.data.code}?`,
    url: route("service.technician.delete", page.props.data.id),
    onSuccess: () => {
      router.get(route("service.technician.index"));
    },
  });
};
</script>

<template>
  <div class="text-bold text-grey-8">Info Teknisi</div>
  <table class="detail">
    <tbody>
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
        <td>No. Telepon</td>
        <td>:</td>
        <td>
          <WaLink :phone="page.props.data.phone" />
        </td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td>:</td>
        <td>{{ page.props.data.address ? page.props.data.address : "-" }}</td>
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

  <div class="text-bold text-grey-8 q-mt-md">Info Akun</div>
  <table class="detail">
    <tbody>
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
    </tbody>
  </table>
  <div class="q-pt-md" v-if="$can('service.technician.delete')">
    <q-btn
      icon="delete"
      label="Hapus"
      color="negative"
      @click="confirmDelete()"
    />
  </div>
</template>
