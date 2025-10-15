<script setup>
import { formatDateTime, formatMoneyWithSymbol } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Transaksi";
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
          @click="$inertia.get(route('admin.finance-transaction.index'))"
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
                Rincian Transaksi Keuangan
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 110px">Kode</td>
                    <td style="width: 1px">:</td>
                    <td>
                      {{ page.props.data.code }}
                    </td>
                  </tr>
                  <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>
                      {{ formatDateTime(page.props.data.datetime) }}
                    </td>
                  </tr>

                  <tr v-if="page.props.data.account">
                    <td>Akun</td>
                    <td>:</td>
                    <td>
                      <i-link
                        :href="
                          route(
                            'admin.finance-account.detail',
                            page.props.data.account.id
                          )
                        "
                      >
                        {{ page.props.data.account.name }}
                      </i-link>
                      <template v-if="page.props.data.account.type === 'bank'">
                        Rek {{ page.props.data.account.bank }} an
                        {{ page.props.data.account.holder }}
                        <br />
                        {{ page.props.data.account.number }}
                      </template>
                    </td>
                  </tr>
                  <tr v-if="page.props.data.type">
                    <td>Jenis</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS.FINANCE_TRANSACTION_TYPES[
                          page.props.data.type
                        ]
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Jumlah</td>
                    <td>:</td>
                    <td>
                      {{ formatMoneyWithSymbol(page.props.data.amount) }}
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
