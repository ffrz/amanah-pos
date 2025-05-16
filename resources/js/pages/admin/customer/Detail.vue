<script setup>
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Pelanggan";

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
              <div class="text-subtitle1 text-bold text-grey-8">Info Pelanggan</div>
              <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:70px">Nama</td>
                      <td style="width:1px">:</td>
                      <td>{{ page.props.data.name }}</td>
                    </tr>
                    <tr>
                      <td>Telepon</td>
                      <td>:</td>
                      <td>{{ page.props.data.phone }}</td>
                    </tr>
                    <tr>
                      <td>Alamat</td>
                      <td>:</td>
                      <td>{{ page.props.data.address }}</td>
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
