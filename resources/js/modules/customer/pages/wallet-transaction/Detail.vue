<script setup>
import { formatDate, formatDateTime, formatNumber } from "@/helpers/formatter";
import { router, usePage } from "@inertiajs/vue3";

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
          @click="$inertia.get(route('customer.wallet-transaction.index'))"
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
                Rincian Transaksi
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
                    <td>Jumlah</td>
                    <td>:</td>
                    <td>Rp. {{ formatNumber(page.props.data.amount) }}</td>
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
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes }}</td>
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
