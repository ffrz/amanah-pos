<script setup>
import { formatNumber } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Layanan";

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">Info Layanan</div>
              <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:120px">Nama</td>
                      <td style="width:1px">:</td>
                      <td>{{ page.props.data.name }}</td>
                    </tr>
                    <tr>
                      <td>Deskripsi</td>
                      <td>:</td>
                      <td>{{ page.props.data.description }}</td>
                    </tr>
                    <tr>
                      <td>Harga / Biaya</td>
                      <td>:</td>
                      <td>Rp. {{ formatNumber(page.props.data.price) }}</td>
                    </tr>
                    <tr>
                      <td>Perkiraan Durasi</td>
                      <td>:</td>
                      <td>{{ formatNumber(page.props.data.duration) }}</td>
                    </tr>
                    <tr>
                      <td>Status</td>
                      <td>:</td>
                      <td>{{ page.props.data.active ? 'Aktif' : 'Tidak Aktif' }}</td>
                    </tr>
                  </tbody>
                </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
