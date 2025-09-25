<script setup>
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Pemasok";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$goBack()"
        />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          v-if="$can('admin.supplier.edit')"
          icon="edit"
          dense
          color="grey"
          size="sm"
          rounded
          flat
          @click="
            router.get(route('admin.supplier.edit', { id: page.props.data.id }))
          "
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">
                Info Pemasok
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 70px">Nama</td>
                    <td style="width: 1px">:</td>
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
                    <td>No. Rek</td>
                    <td>:</td>
                    <td>{{ page.props.data.bank_account_number }}</td>
                  </tr>
                  <tr>
                    <td>Alamat Retur</td>
                    <td>:</td>
                    <td>{{ page.props.data.return_address }}</td>
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
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
