<script setup>
import { formatNumber } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const page = usePage();
const title = `Rincian Order Servis #${page.props.data.id}`;
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
              <q-tab name="service" label="Info Servis" />
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
                          $CONSTANTS.SERVICEORDER_ORDERSTATUSES[
                            page.props.data.order_status
                          ]
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Dibuat Oleh</td>
                      <td>:</td>
                      <td>
                        <i-link :href="route('admin.user.detail', {id: page.props.auth.user.id})">
                          {{ page.props.data.created_by.name }} ({{ page.props.data.created_by.username }})
                        </i-link> -
                        {{ $dayjs(new Date(page.props.data.created_datetime)).format("dddd, D MMMM YYYY pukul HH:mm:ss") }}
                      </td>
                    </tr>
                    <tr v-if="!!page.props.data.updated_datetime">
                      <td>Diperbarui oleh</td>
                      <td>:</td>
                      <td>
                        <i-link :href="route('admin.user.detail', {id: page.props.auth.user.id})">
                          {{ page.props.data.updated_by.name }} ({{ page.props.data.updated_by.username }})
                        </i-link> -
                        {{ $dayjs(new Date(page.props.data.updated_datetime)).format("dddd, D MMMM YYYY pukul HH:mm:ss") }}
                      </td>
                    </tr>
                    <tr v-if="!!page.props.data.closed_datetime">
                      <td>Diselesaikan oleh</td>
                      <td>:</td>
                      <td>
                        <i-link :href="route('admin.user.detail', {id: page.props.data.closed_by.id})">
                          {{ page.props.data.closed_by.name }} ({{ page.props.data.closed_by.username }})
                        </i-link> -
                        {{ $dayjs(new Date(page.props.data.closed_datetime)).format("dddd, D MMMM YYYY pukul HH:mm:ss") }}
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
                        <i-link :href="route('admin.customer.detail', {id: page.props.data.customer_id})">
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
                  Info Perangkat
                </div>
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:100px">Jenis</td>
                      <td style="width:1px">:</td>
                      <td>{{ page.props.data.device_type }}</td>
                    </tr>
                    <tr>
                      <td>Perangkat</td>
                      <td>:</td>
                      <td>{{ page.props.data.device }}</td>
                    </tr>
                    <tr>
                      <td>Kelengkapan</td>
                      <td>:</td>
                      <td>{{ page.props.data.equipments }}</td>
                    </tr>
                    <tr>
                      <td>No Seri</td>
                      <td>:</td>
                      <td>{{ page.props.data.device_sn }}</td>
                    </tr>
                  </tbody>
                </table>
                <div class="text-subtitle1 q-pt-md text-bold text-grey-9">
                  Kendala dan Tindakan
                </div>
                <table class="detail">
                  <tbody>
                    <tr>
                      <td style="width:100px">Kendala</td>
                      <td style="width:1px">:</td>
                      <td>{{ page.props.data.problems }}</td>
                    </tr>
                    <tr>
                      <td>Tindakan</td>
                      <td>:</td>
                      <td>{{ page.props.data.actions }}</td>
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
                          $CONSTANTS.SERVICEORDER_SERVICESTATUSES[
                            page.props.data.service_status
                          ]
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Sukses / Gagal</td>
                      <td>:</td>
                      <td>
                        {{
                          $CONSTANTS.SERVICEORDER_REPAIRSTATUSES[
                            page.props.data.repair_status
                          ]
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Teknisi</td>
                      <td>:</td>
                      <td>
                        <i-link v-if="!!page.props.data.technician" href="#">
                          {{ page.props.data.technician.name }}
                        </i-link>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <q-timeline class="q-pa-sm">
                  <q-timeline-entry heading>Riwayat Status</q-timeline-entry>
                  <q-timeline-entry
                    v-if="page.props.data.picked_datetime"
                    title="Diambil"
                    color="green"
                    :subtitle="
                      $dayjs(new Date(page.props.data.picked_datetime)).format(
                        'DD-MM-YYYY HH:mm'
                      )
                    "
                    icon="output"
                    body="Barang telah diambil."
                  />
                  <q-timeline-entry
                    v-if="page.props.data.completed_datetime"
                    :title="
                      page.props.data.repair_status === 'success'
                        ? 'Selesai: SUKSES'
                        : page.props.data.repair_status === 'failed'
                        ? 'Selesai: GAGAL'
                        : ''
                    "
                    :color="
                      page.props.data.repair_status === 'success'
                        ? 'green'
                        : page.props.data.repair_status === 'failed'
                        ? 'red'
                        : 'grey'
                    "
                    :subtitle="
                      $dayjs(
                        new Date(page.props.data.completed_datetime)
                      ).format('DD-MM-YYYY HH:mm')
                    "
                    :icon="
                      page.props.data.repair_status === 'success'
                        ? 'check'
                        : page.props.data.repair_status === 'failed'
                        ? 'close'
                        : 'question_mark'
                    "
                    body="Servis telah selesai."
                  />
                  <q-timeline-entry
                    v-if="page.props.data.worked_datetime"
                    title="Sedang Dikerjakan"
                    color="orange"
                    :subtitle="
                      $dayjs(new Date(page.props.data.worked_datetime)).format(
                        'DD-MM-YYYY HH:mm'
                      )
                    "
                    icon="handyman"
                    body="Barang telah dikerjakan."
                  />
                  <q-timeline-entry
                    v-if="page.props.data.checked_datetime"
                    title="Diperiksa"
                    color="orange"
                    :subtitle="
                      $dayjs(new Date(page.props.data.checked_datetime)).format(
                        'DD-MM-YYYY HH:mm'
                      )
                    "
                    icon="check"
                    body="Barang telah diperiksa."
                  />
                  <q-timeline-entry
                    v-if="page.props.data.received_datetime"
                    title="Diterima"
                    color="orange"
                    :subtitle="
                      $dayjs(
                        new Date(page.props.data.received_datetime)
                      ).format('DD-MM-YYYY HH:mm')
                    "
                    icon="input"
                    body="Barang telah diterima."
                  />
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
                          $CONSTANTS.SERVICEORDER_PAYMENTSTATUSES[
                            page.props.data.payment_status
                          ]
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td>Biaya Perkiraan</td>
                      <td>:</td>
                      <td>
                        Rp. {{ formatNumber(page.props.data.estimated_cost) }}
                      </td>
                    </tr>
                    <tr>
                      <td>Uang Muka</td>
                      <td>:</td>
                      <td>
                        Rp. {{ formatNumber(page.props.data.down_payment) }}
                      </td>
                    </tr>
                    <tr>
                      <td>Total Biaya</td>
                      <td>:</td>
                      <td>
                        Rp. {{ formatNumber(page.props.data.total_cost) }}
                      </td>
                    </tr>
                    <tr>
                      <td>Lama Garansi</td>
                      <td>:</td>
                      <td>{{ page.props.data.warranty_day_count }} hari</td>
                    </tr>
                    <tr>
                      <td class="no-wrap">Tanggal Mulai Garansi</td>
                      <td>:</td>
                      <td>
                        {{
                          page.props.data.warranty_start_date
                            ? $dayjs(
                                new Date(page.props.data.warranty_start_date)
                              ).format("D MMMM YYYY")
                            : ""
                        }}
                      </td>
                    </tr>
                    <tr>
                      <td class="no-wrap">Tanggal Berakhir Garansi</td>
                      <td>:</td>
                      <td>
                        {{
                          page.props.data.warranty_start_date
                            ? $dayjs(
                                new Date(page.props.data.warranty_start_date)
                              )
                                .add(
                                  page.props.data.warranty_day_count - 1,
                                  "day"
                                )
                                .format("D MMMM YYYY")
                            : ""
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
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
