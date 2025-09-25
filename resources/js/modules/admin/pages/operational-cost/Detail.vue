<script setup>
import { formatDate, formatDateTime, formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Biaya Operasional";
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
          @click="$inertia.get(route('admin.operational-cost.index'))"
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
                Rincian Biaya Operasional
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 110px">ID</td>
                    <td style="width: 1px">:</td>
                    <td>
                      {{ page.props.data.id }}
                    </td>
                  </tr>
                  <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>
                      {{ formatDate(page.props.data.date) }}
                    </td>
                  </tr>

                  <tr v-if="page.props.data.finance_account">
                    <td>Akun</td>
                    <td>:</td>
                    <td>
                      {{ page.props.data.finance_account.name }}
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
                  <tr v-if="page.props.data.category">
                    <td>Kategori</td>
                    <td>:</td>
                    <td>{{ page.props.data.category.name }}</td>
                  </tr>
                  <tr>
                    <td>Deskripsi</td>
                    <td>:</td>
                    <td>{{ page.props.data.description }}</td>
                  </tr>
                  <tr>
                    <td>Jumlah</td>
                    <td>:</td>
                    <td>Rp. {{ formatNumber(page.props.data.amount) }}</td>
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
