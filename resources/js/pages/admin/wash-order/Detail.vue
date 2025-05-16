<script setup>
import { formatNumber } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const page = usePage();
const title = `Rincian Order Cuci #${page.props.data.id}`;
const tab = ref("main");

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="q-card col">
            <q-tabs v-model="tab" align="left">
              <q-tab name="main" label="Info Utama" />
              <q-tab name="service" label="Info Cuci" />
              <q-tab name="other" label="Lainnya" />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                <div class="text-subtitle1 text-bold text-grey-9">
                  Info Order
                </div>
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:100px">No Order</td>
                      <td style="width:1px">:</td>
                      <td># {{ page.props.data.id }}</td>
                    </tr>
                    <tr>
                      <td>Status</td>
                      <td>:</td>
                      <td>
                        {{
                          $CONSTANTS.WASHORDER_ORDERSTATUSES[
                          page.props.data.order_status
                          ]
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Dibuat Oleh</td>
                      <td>:</td>
                      <td>
                        <!-- <i-link :href="route('admin.user.detail', {id: page.props.auth.user.id})">
                          {{ page.props.data.created_by.name }} ({{ page.props.data.created_by.username }})
                        </i-link> -
                        {{ $dayjs(new Date(page.props.data.created_datetime)).format("dddd, D MMMM YYYY pukul HH:mm:ss") }} -->
                      </td>
                    </tr>
                    <tr v-if="!!page.props.data.updated_datetime">
                      <td>Diperbarui oleh</td>
                      <td>:</td>
                      <td>
                        <!-- <i-link :href="route('admin.user.detail', {id: page.props.auth.user.id})">
                          {{ page.props.data.updated_by.name }} ({{ page.props.data.updated_by.username }})
                        </i-link> -
                        {{ $dayjs(new Date(page.props.data.updated_datetime)).format("dddd, D MMMM YYYY pukul HH:mm:ss") }} -->
                      </td>
                    </tr>
                    <tr v-if="!!page.props.data.closed_datetime">
                      <td>Diselesaikan oleh</td>
                      <td>:</td>
                      <td>
                        <!-- <i-link :href="route('admin.user.detail', {id: page.props.data.closed_by.id})">
                          {{ page.props.data.closed_by.name }} ({{ page.props.data.closed_by.username }})
                        </i-link> -
                        {{ $dayjs(new Date(page.props.data.closed_datetime)).format("dddd, D MMMM YYYY pukul HH:mm:ss") }} -->
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div class="text-subtitle1 q-pt-lg text-bold text-grey-9">
                  Info Pelanggan
                </div>
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:100px">Nama</td>
                      <td style="width:1px">:</td>
                      <td>
                        <i-link :href="route('admin.customer.detail', { id: page.props.data.customer_id })">
                          {{ page.props.data.customer_name }}
                        </i-link>
                      </td>
                    </tr>
                    <tr>
                      <td>Kontak</td>
                      <td>:</td>
                      <td>{{ page.props.data.customer_phone }}</td>
                    </tr>
                    <tr>
                      <td>Alamat</td>
                      <td>:</td>
                      <td>{{ page.props.data.customer_address }}</td>
                    </tr>
                  </tbody>
                </table>
                <div class="text-subtitle1 q-pt-md text-bold text-grey-9">
                  Info Kendaraan
                </div>
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:100px">Plat Nomor</td>
                      <td style="width:1px">:</td>
                      <td>{{ page.props.data.vehicle_plate_number }}</td>
                    </tr>
                    <tr>
                      <td>Kendaraan</td>
                      <td>:</td>
                      <td>{{ page.props.data.vehicle_description }}</td>
                    </tr>
                  </tbody>
                </table>

              </q-tab-panel>
              <q-tab-panel name="service">
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:120px">Status Servis</td>
                      <td style="width:1px">:</td>
                      <td>
                        {{
                          $CONSTANTS.WASHORDER_SERVICESTATUSES[
                          page.props.data.service_status
                          ]
                        }}
                      </td>
                    </tr>
                  </tbody>
                </table>
                <q-timeline class="q-pa-sm">
                  <q-timeline-entry heading>Riwayat Status Pencucian</q-timeline-entry>
                  <q-timeline-entry v-if="page.props.data.order_closed_at" title="Order Selesai" color="green"
                    :subtitle="$dayjs(new Date(page.props.data.order_closed_at)).format(
                      'DD-MM-YYYY HH:mm'
                    )
                      " icon="output" body="Order pencucian telah selesai." />
                  <q-timeline-entry v-if="page.props.data.work_completed_at" title="Pekerjaan Selesai" color="green"
                    :subtitle="$dayjs(new Date(page.props.data.work_completed_at)).format(
                      'DD-MM-YYYY HH:mm'
                    )
                      " icon="output" body="Pekerjaan layanan telah selesai." />
                  <q-timeline-entry v-if="page.props.data.work_started_at" title="Pekerjaan Dimulai"
                    color="green" :subtitle="$dayjs(new Date(page.props.data.order_created_at)).format(
                      'DD-MM-YYYY HH:mm'
                    )
                      " icon="output" body="Pekerjaan layanan telah dimulai." />
                  <q-timeline-entry v-if="page.props.data.order_created_at" title="Order Masuk" color="green" :subtitle="$dayjs(new Date(page.props.data.order_created_at)).format(
                    'DD-MM-YYYY HH:mm'
                  )
                    " icon="output" body="Order telah dicatat pada sistem." />
                </q-timeline>
              </q-tab-panel>
              <q-tab-panel name="other">
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:100px;">Status Pembayaran</td>
                      <td style="width:1px;">:</td>
                      <td>
                        {{
                          $CONSTANTS.WASHORDER_PAYMENTSTATUSES[
                          page.props.data.payment_status
                          ]
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Total Biaya</td>
                      <td>:</td>
                      <td>
                        Rp. {{ formatNumber(page.props.data.total_price) }}
                      </td>
                    </tr>
                    <tr>
                      <td>Catatan</td>
                      <td>:</td>
                      <td>{{ page.props.data.notes }}</td>
                    </tr>
                  </tbody>
                </table>
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
