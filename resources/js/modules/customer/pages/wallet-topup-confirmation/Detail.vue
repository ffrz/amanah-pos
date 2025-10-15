<script setup>
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Konfirmasi Top Up";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="
            $inertia.get(route('customer.wallet-topup-confirmation.index'))
          "
        />
      </div>
    </template>
    <template #title>
      <span class="text-subtitle2">{{ title }}</span>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">
                Rincian Konfirmasi
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 110px">Kode Trx</td>
                    <td style="width: 1px">:</td>
                    <td>
                      {{ page.props.data.code }}
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
                      {{ page.props.data.customer.code }} -
                      {{ page.props.data.customer.name }}
                    </td>
                  </tr>
                  <tr>
                    <td>Akun Tujuan</td>
                    <td>:</td>
                    <td>
                      <!-- {{ page.props.data.finance_account.name }}<br /> -->
                      <template
                        v-if="page.props.data.finance_account.type === 'bank'"
                      >
                        Rek {{ page.props.data.finance_account.bank }} an
                        {{ page.props.data.finance_account.holder }}
                        <br />
                        {{ page.props.data.finance_account.number }}
                      </template>
                    </td>
                  </tr>
                  <tr>
                    <td>Jumlah</td>
                    <td>:</td>
                    <td>Rp. {{ formatNumber(page.props.data.amount) }}</td>
                  </tr>
                  <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS
                          .CUSTOMER_WALLET_TRANSACTION_CONFIRMATION_STATUSES[
                          page.props.data.status
                        ]
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes }}</td>
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
                          @click="showViewer = true"
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
