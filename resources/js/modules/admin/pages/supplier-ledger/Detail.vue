<script setup>
import {
  formatDateTime,
  formatNumber,
  plusMinusSymbol,
} from "@/helpers/formatter";
import { usePage, router } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Transaksi Utang / Piutang";
const data = page.props.data;
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.supplier-ledger.index'))"
        />
      </div>
    </template>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8 q-mb-md">
                Informasi Transaksi Utang / Piutang Supplier
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 130px">Kode Trx</td>
                    <td style="width: 1px">:</td>
                    <td class="text-bold text-primary">
                      {{ data.code }}
                    </td>
                  </tr>
                  <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>
                      {{ formatDateTime(data.datetime) }}
                    </td>
                  </tr>
                  <tr>
                    <td>Supplier</td>
                    <td>:</td>
                    <td>
                      <div>
                        <q-icon name="store" class="inline-icon" />
                        <i-link
                          :href="
                            route('admin.supplier.detail', {
                              id: data.supplier_id,
                            })
                          "
                          class="text-weight-bold"
                        >
                          &nbsp;{{ data.supplier.name }}
                        </i-link>
                      </div>
                      <div class="text-grey-8 text-caption">
                        {{ data.supplier.code }}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Jenis Transaksi</td>
                    <td>:</td>
                    <td>
                      <q-badge color="secondary" outline>
                        {{ data.type_label }}
                      </q-badge>
                    </td>
                  </tr>

                  <tr v-if="data.ref">
                    <td>Referensi</td>
                    <td>:</td>
                    <td>
                      {{ data.ref_type }} #{{ data.ref.code || data.ref.id }}
                    </td>
                  </tr>

                  <tr>
                    <td>Jumlah</td>
                    <td>:</td>
                    <td
                      class="text-bold text-subtitle2"
                      :class="
                        data.amount >= 0 ? 'text-orange-9' : 'text-green-8'
                      "
                    >
                      {{ plusMinusSymbol(data.amount) }} Rp.
                      {{ formatNumber(Math.abs(data.amount)) }}
                    </td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ data.notes || "-" }}</td>
                  </tr>

                  <tr v-if="data.created_at">
                    <td>Dibuat</td>
                    <td>:</td>
                    <td>
                      {{ formatDateTime(data.created_at) }}
                    </td>
                  </tr>

                  <template v-if="data.image_path">
                    <tr>
                      <td>Lampiran</td>
                      <td>:</td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="3" class="bg-white">
                        <q-img
                          :src="`/${data.image_path}`"
                          class="q-mt-sm rounded-borders"
                          style="
                            max-width: 100%;
                            max-height: 400px;
                            border: 1px solid #ddd;
                          "
                          fit="contain"
                        />
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
