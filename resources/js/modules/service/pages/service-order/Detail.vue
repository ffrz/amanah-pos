<script setup>
import { formatNumber, formatPhoneNumber } from "@/helpers/formatter";
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const page = usePage();
const data = page.props.data;
const title = `Rincian Order Servis #${data.order_code}`;
const tab = ref("main");

const editOrder = () =>
  router.get(route("service.service-order.edit", data.id));
const backToIndex = () => router.get(route("service.service-order.index"));
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <template #left-button>
      <q-btn
        icon="arrow_back"
        dense
        flat
        rounded
        color="grey-7"
        @click="backToIndex"
      />
    </template>

    <template #right-button>
      <q-btn
        v-if="$can('service.service-order.edit')"
        icon="edit"
        label="Edit"
        rounded
        color="primary"
        @click="editOrder"
      />
    </template>

    <div class="row justify-center">
      <div class="col-xs-12 col-md-8 col-lg-7 q-pa-xs">
        <q-card square flat bordered>
          <q-tabs
            v-model="tab"
            dense
            class="bg-grey-1 text-grey-7"
            active-color="primary"
            indicator-color="primary"
            align="left"
          >
            <q-tab name="main" label="Info Utama" />
            <q-tab name="service" label="Progres & Timeline" />
            <q-tab name="billing" label="Biaya & Garansi" />
          </q-tabs>

          <q-separator />

          <q-tab-panels v-model="tab" animated>
            <q-tab-panel name="main" class="q-pa-md">
              <div class="text-subtitle1 text-bold text-primary q-mb-sm">
                Info Order
              </div>
              <table class="detail-table full-width">
                <tbody>
                  <tr>
                    <td class="label">No. Nota</td>
                    <td>
                      : <span class="text-bold">#{{ data.order_code }}</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="label">Status Dokumen</td>
                    <td>
                      :
                      <q-badge
                        :label="data.order_status_label"
                        :color="
                          data.order_status === 'open' ? 'blue' : 'grey-8'
                        "
                      />
                    </td>
                  </tr>
                  <tr>
                    <td class="label">Diterima Oleh</td>
                    <td>
                      : {{ data.received_by?.name || "-" }} pada
                      {{
                        $dayjs(data.received_datetime).format(
                          "DD MMM YYYY HH:mm"
                        )
                      }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <div
                class="text-subtitle1 text-bold text-primary q-mt-lg q-mb-sm"
              >
                Info Pelanggan
              </div>
              <table class="detail-table full-width">
                <tbody>
                  <tr>
                    <td class="label">Nama</td>
                    <td>
                      :
                      <i-link
                        v-if="data.customer_id"
                        :href="route('admin.customer.detail', data.customer_id)"
                        class="text-bold"
                      >
                        {{ data.customer_name }}
                      </i-link>
                      <span v-else class="text-bold">{{
                        data.customer_name
                      }}</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="label">WhatsApp</td>
                    <td>: {{ formatPhoneNumber(data.customer_phone) }}</td>
                  </tr>
                  <tr>
                    <td class="label">Alamat</td>
                    <td>: {{ data.customer_address || "-" }}</td>
                  </tr>
                </tbody>
              </table>

              <div
                class="text-subtitle1 text-bold text-primary q-mt-lg q-mb-sm"
              >
                Detail Perangkat
              </div>
              <table class="detail-table full-width">
                <tbody>
                  <tr>
                    <td class="label">Jenis / Nama</td>
                    <td>: {{ data.device_type }} - {{ data.device }}</td>
                  </tr>
                  <tr>
                    <td class="label">Serial Number</td>
                    <td>: {{ data.device_sn || "-" }}</td>
                  </tr>
                  <tr>
                    <td class="label">Kelengkapan</td>
                    <td>: {{ data.equipments }}</td>
                  </tr>
                </tbody>
              </table>

              <div
                class="text-subtitle1 text-bold text-primary q-mt-lg q-mb-sm"
              >
                Keluhan & Analisa
              </div>
              <div class="bg-grey-1 q-pa-sm rounded-borders border-grey-3">
                <div class="text-caption text-grey-7 text-bold">KELUHAN:</div>
                <div class="q-mb-md">{{ data.problems }}</div>
                <div class="text-caption text-grey-7 text-bold">TINDAKAN:</div>
                <div>{{ data.actions || "Belum ada tindakan." }}</div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="service" class="q-pa-md">
              <table class="detail-table full-width q-mb-lg">
                <tbody>
                  <tr>
                    <td class="label">Status Servis</td>
                    <td>
                      :
                      <span class="text-bold">{{
                        data.service_status_label
                      }}</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="label">Hasil Perbaikan</td>
                    <td>
                      :
                      <span class="text-bold text-uppercase">{{
                        data.repair_status_label
                      }}</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="label">Teknisi</td>
                    <td>: {{ data.technician?.name || "Belum ditentukan" }}</td>
                  </tr>
                </tbody>
              </table>

              <q-timeline color="primary" class="q-px-sm">
                <q-timeline-entry heading>Riwayat Pengerjaan</q-timeline-entry>

                <q-timeline-entry
                  v-if="data.picked_datetime"
                  title="Barang Diambil"
                  color="black"
                  icon="person_pin"
                  :subtitle="
                    $dayjs(data.picked_datetime).format('DD MMM YYYY HH:mm')
                  "
                >
                  Unit telah diserahkan kembali kepada pelanggan.
                </q-timeline-entry>

                <q-timeline-entry
                  v-if="data.completed_datetime"
                  title="Servis Selesai"
                  :color="data.repair_status === 'repairable' ? 'green' : 'red'"
                  icon="done_all"
                  :subtitle="
                    $dayjs(data.completed_datetime).format('DD MMM YYYY HH:mm')
                  "
                >
                  Status Akhir: {{ data.repair_status_label }}
                </q-timeline-entry>

                <q-timeline-entry
                  v-if="data.worked_datetime"
                  title="Proses Pengerjaan"
                  color="orange"
                  icon="engineering"
                  :subtitle="
                    $dayjs(data.worked_datetime).format('DD MMM YYYY HH:mm')
                  "
                >
                  Unit sedang dalam penanganan teknisi.
                </q-timeline-entry>

                <q-timeline-entry
                  v-if="data.checked_datetime"
                  title="Selesai Diperiksa"
                  color="blue"
                  icon="fact_check"
                  :subtitle="
                    $dayjs(data.checked_datetime).format('DD MMM YYYY HH:mm')
                  "
                >
                  Hasil diagnosa awal telah keluar.
                </q-timeline-entry>

                <q-timeline-entry
                  v-if="data.received_datetime"
                  title="Unit Diterima"
                  color="grey-7"
                  icon="login"
                  :subtitle="
                    $dayjs(data.received_datetime).format('DD MMM YYYY HH:mm')
                  "
                >
                  Registrasi order servis masuk ke sistem.
                </q-timeline-entry>
              </q-timeline>
            </q-tab-panel>

            <q-tab-panel name="billing" class="q-pa-md">
              <div class="text-subtitle1 text-bold text-primary q-mb-sm">
                Rincian Biaya
              </div>
              <table class="detail-table full-width">
                <tbody>
                  <tr>
                    <td class="label">Estimasi Biaya</td>
                    <td>: Rp. {{ formatNumber(data.estimated_cost) }}</td>
                  </tr>
                  <tr>
                    <td class="label">Uang Muka (DP)</td>
                    <td>
                      :
                      <span class="text-orange-9 text-bold"
                        >Rp. {{ formatNumber(data.down_payment) }}</span
                      >
                    </td>
                  </tr>
                  <tr class="bg-blue-1">
                    <td class="label text-bold">Total Biaya Akhir</td>
                    <td>
                      :
                      <span class="text-bold text-h6 text-primary"
                        >Rp. {{ formatNumber(data.total_cost) }}</span
                      >
                    </td>
                  </tr>
                  <tr>
                    <td class="label">Status Bayar</td>
                    <td>
                      :
                      <q-badge
                        :label="data.payment_status_label"
                        :color="
                          data.payment_status === 'fully_paid' ? 'green' : 'red'
                        "
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <div
                class="text-subtitle1 text-bold text-primary q-mt-lg q-mb-sm"
              >
                Informasi Garansi
              </div>
              <table class="detail-table full-width">
                <tbody>
                  <tr>
                    <td class="label">Masa Garansi</td>
                    <td>: {{ data.warranty_day_count }} Hari</td>
                  </tr>
                  <tr v-if="data.warranty_start_date">
                    <td class="label">Mulai s/d</td>
                    <td>
                      :
                      {{
                        $dayjs(data.warranty_start_date).format("DD MMM YYYY")
                      }}
                      s/d
                      {{
                        $dayjs(data.warranty_start_date)
                          .add(data.warranty_day_count, "day")
                          .format("DD MMM YYYY")
                      }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <div
                class="text-subtitle1 text-bold text-primary q-mt-lg q-mb-sm"
              >
                Catatan Internal
              </div>
              <div class="bg-amber-1 q-pa-sm border-amber-3 text-italic">
                {{ data.notes || "Tidak ada catatan." }}
              </div>

              <div
                class="q-mt-xl q-pa-sm bg-grey-2 text-caption text-grey-7 border-grey-3"
              >
                <div v-if="data.creator">
                  Dibuat oleh: {{ data.creator.name }} ({{
                    $dayjs(data.created_at).format("DD/MM/YYYY HH:mm")
                  }})
                </div>
                <div v-if="data.updater">
                  Terakhir diupdate: {{ data.updater.name }} ({{
                    $dayjs(data.updated_at).format("DD/MM/YYYY HH:mm")
                  }})
                </div>
                <div v-if="data.closed_by">
                  Ditutup oleh: {{ data.closed_by.name }} ({{
                    $dayjs(data.closed_datetime).format("DD/MM/YYYY HH:mm")
                  }})
                </div>
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </q-card>
      </div>
    </div>
  </authenticated-layout>
</template>

<style scoped>
.detail-table tr td {
  padding: 6px 4px;
  vertical-align: top;
}
.detail-table tr td.label {
  width: 160px;
  color: #666;
}
.border-grey-3 {
  border: 1px solid #ddd;
}
.border-amber-3 {
  border: 1px solid #ffe082;
}
</style>
