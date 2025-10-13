<script setup>
import {
  formatDateTime,
  formatNumber,
  plusMinusSymbol,
} from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Transaksi";
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
          @click="$goBack()"
        />
      </div>
    </template>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">
                Rincian Transaksi Wallet
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 110px">Kode Trx</td>
                    <td style="width: 1px">:</td>
                    <td>
                      {{ page.props.data.formatted_id }}
                    </td>
                  </tr>
                  <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>
                      {{ formatDateTime(page.props.data.datetime) }}
                    </td>
                  </tr>
                  <tr>
                    <td>Pelanggan</td>
                    <td>:</td>
                    <td>
                      <div>
                        <q-icon name="person" class="inline-icon" />
                        <i-link
                          :href="
                            route('admin.customer.detail', {
                              id: page.props.data.customer_id,
                            })
                          "
                          >&nbsp;{{ page.props.data.customer.code }}</i-link
                        >
                      </div>
                      <div>
                        <q-icon name="person" class="inline-icon" />
                        {{ page.props.data.customer.name }}
                      </div>
                      <div v-if="page.props.data.customer.phone">
                        <q-icon name="phone" class="inline-icon" />
                        {{ page.props.data.customer.phone }}
                      </div>
                      <div v-if="page.props.data.customer.address">
                        <q-icon name="home_pin" class="inline-icon" />
                        {{ page.props.data.customer.address }}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Jenis Transaksi</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS.CUSTOMER_WALLET_TRANSACTION_TYPES[
                          page.props.data.type
                        ]
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Jumlah</td>
                    <td>:</td>
                    <td
                      :class="
                        page.props.data.amount > 0
                          ? 'text-positive'
                          : 'text-negative'
                      "
                    >
                      {{ plusMinusSymbol(page.props.data.amount) }}Rp.
                      {{ formatNumber(Math.abs(page.props.data.amount)) }}
                    </td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes }}</td>
                  </tr>
                  <tr v-if="page.props.data.creator">
                    <td>Dibuat</td>
                    <td>:</td>
                    <td>
                      {{ formatDateTime(page.props.data.created_at) }}
                      oleh
                      {{ page.props.data.creator.name }}
                    </td>
                  </tr>
                  <tr v-if="page.props.data.updater">
                    <td>Diperbarui</td>
                    <td>:</td>
                    <td>
                      {{ formatDateTime(page.props.data.updated_at) }}
                      oleh
                      {{ page.props.data.updater.name }}
                    </td>
                  </tr>
                  <template v-if="page.props.data.image_path">
                    <tr>
                      <td>Lampiran</td>
                      <td>:</td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="3" class="bg-white">
                        <q-img
                          :src="`/${page.props.data.image_path}`"
                          class="q-mt-none"
                          style="max-width: 500px"
                          :style="{ border: '1px solid #ddd' }"
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
