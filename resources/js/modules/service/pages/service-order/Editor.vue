<script setup>
import { ref, computed, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import DatePicker from "@/components/DatePicker.vue";
import { formatPhoneNumber } from "@/helpers/formatter";

const page = usePage();
const tab = ref("main");

// --- State & Options ---
const title = !!page.props.data.id
  ? `Edit Order Servis #${page.props.data.order_code}`
  : "Tambah Order Servis";

// Predefined Device Types
const deviceTypeOptions = ["Laptop", "PC", "Printer", "Scanner", "HP"];
const deviceTypes = ref([...deviceTypeOptions]);

// Map existing customers and add "Pelanggan Baru" option
const customers = [
  { value: 0, label: "<< Buat Baru >>" },
  ...page.props.customers.map((c) => {
    // Ambil bagian yang ingin ditampilkan, bersihkan whitespace,
    // lalu filter hanya yang memiliki nilai (bukan null/empty string)
    const labelParts = [
      c.name,
      c.code,
      c.phone ? formatPhoneNumber(c.phone) : "",
      c.address,
    ]
      .map((part) => part?.toString().trim()) // Trim jika ada datanya
      .filter((part) => !!part); // Hanya ambil yang truthy (tidak kosong)

    return {
      value: c.id,
      label: labelParts.join(" - "), // Gabungkan dengan pemisah " - "
    };
  }),
];
const filteredCustomers = ref([...customers]);

const technicians = page.props.technicians.map((t) => ({
  value: t.id,
  label: t.name,
}));
const filteredTechnicians = ref([...technicians]);

// --- Form Initialization ---
const form = useForm({
  id: page.props.data.id ?? null,
  order_status: page.props.data.order_status ?? "open",

  customer_id: page.props.data.customer_id ?? 0,
  customer_name: page.props.data.customer_name ?? "",
  customer_phone: page.props.data.customer_phone ?? "",
  customer_address: page.props.data.customer_address ?? "",

  device_type: page.props.data.device_type ?? "Laptop",
  device: page.props.data.device ?? "",
  device_sn: page.props.data.device_sn ?? "",
  equipments: page.props.data.equipments ?? "",

  problems: page.props.data.problems ?? "",
  actions: page.props.data.actions ?? "",
  technician_id: page.props.data.technician_id ?? null,

  service_status: page.props.data.service_status ?? "received",
  repair_status: page.props.data.repair_status ?? "pending",

  received_datetime:
    page.props.data.received_datetime ?? new Date().toISOString(),
  checked_datetime: page.props.data.checked_datetime ?? null,
  worked_datetime: page.props.data.worked_datetime ?? null,
  completed_datetime: page.props.data.completed_datetime ?? null,
  picked_datetime: page.props.data.picked_datetime ?? null,

  payment_status: page.props.data.payment_status ?? "unpaid",
  down_payment: parseFloat(page.props.data.down_payment) ?? 0,
  estimated_cost: parseFloat(page.props.data.estimated_cost) ?? 0,
  total_cost: parseFloat(page.props.data.total_cost) ?? 0,

  warranty_start_date: page.props.data.warranty_start_date ?? null,
  warranty_day_count: page.props.data.warranty_day_count ?? 0,
  notes: page.props.data.notes ?? "",
});

// --- Logic ---
const onCustomerChange = (val) => {
  if (val && val !== 0) {
    const customer = page.props.customers.find((c) => c.id === val);
    form.customer_name = customer.name;
    form.customer_phone = formatPhoneNumber(customer.phone);
    form.customer_address = customer.address;
  } else {
    form.customer_name = "";
    form.customer_phone = "";
    form.customer_address = "";
  }
};

const filterCustomers = (val, update) => {
  update(() => {
    const search = val.toLowerCase();
    filteredCustomers.value = customers.filter((v) =>
      v.label.toLowerCase().includes(search)
    );
  });
};

const filterTechnicians = (val, update) => {
  update(() => {
    const search = val.toLowerCase();
    filteredTechnicians.value = technicians.filter((v) =>
      v.label.toLowerCase().includes(search)
    );
  });
};

// Logic untuk menambah Jenis Perangkat baru
const filterDeviceTypes = (val, update) => {
  update(() => {
    const search = val.toLowerCase();
    deviceTypes.value = deviceTypeOptions.filter((v) =>
      v.toLowerCase().includes(search)
    );
  });
};

const createDeviceType = (val, done) => {
  if (val.length > 0) {
    if (!deviceTypeOptions.includes(val)) {
      deviceTypeOptions.push(val);
    }
    done(val, "toggle");
  }
};

const submit = () => {
  handleSubmit({
    form,
    url: route("service.service-order.save", { id: form.id }),
  });
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <div class="row justify-center">
      <div class="col-xs-12 col-md-8 col-lg-7 q-pa-xs">
        <q-form
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered>
            <q-tabs
              v-model="tab"
              class="bg-grey-1 text-grey-7"
              active-color="primary"
              indicator-color="primary"
              align="left"
            >
              <q-tab name="main" label="Pelanggan & Unit" />
              <q-tab name="service" label="Progres Servis" />
              <q-tab name="billing" label="Biaya & Garansi" />
            </q-tabs>

            <q-separator />

            <q-tab-panels v-model="tab" animated>
              <q-tab-panel name="main" class="q-gutter-y-sm">
                <div class="row q-col-gutter-sm">
                  <q-input
                    class="col-12 col-sm-6"
                    :model-value="
                      form.id ? page.props.data.order_code : 'Otomatis'
                    "
                    label="No. Nota"
                    readonly
                  />
                  <q-select
                    class="col-12 col-sm-6"
                    v-model="form.order_status"
                    label="Status Dokumen"
                    :options="[
                      { label: 'Aktif', value: 'open' },
                      { label: 'Selesai', value: 'closed' },
                      { label: 'Batal', value: 'canceled' },
                    ]"
                    map-options
                    emit-value
                  />
                </div>

                <div class="text-subtitle2 q-mt-md text-primary">
                  Data Pelanggan
                </div>
                <q-select
                  v-model="form.customer_id"
                  label="Cari Pelanggan"
                  use-input
                  fill-input
                  hide-selected
                  input-debounce="300"
                  :options="filteredCustomers"
                  @filter="filterCustomers"
                  @update:model-value="onCustomerChange"
                  map-options
                  emit-value
                />

                <q-input
                  v-model="form.customer_name"
                  label="Nama Pelanggan *"
                  :rules="[(val) => !!val || 'Wajib diisi']"
                  hide-bottom-space
                />
                <q-input v-model="form.customer_phone" label="No. WhatsApp *" />
                <q-input
                  v-model="form.customer_address"
                  label="Alamat"
                  type="textarea"
                  autogrow
                />

                <div class="text-subtitle2 q-mt-md text-primary">
                  Data Perangkat
                </div>
                <div class="row q-col-gutter-sm">
                  <q-select
                    class="col-4"
                    v-model="form.device_type"
                    label="Jenis"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    :options="deviceTypes"
                    @filter="filterDeviceTypes"
                    @new-value="createDeviceType"
                    hint="Ketik lalu enter untuk baru"
                  />
                  <q-input
                    class="col-8"
                    v-model="form.device"
                    label="Nama Perangkat / Model *"
                    :rules="[(val) => !!val || 'Wajib diisi']"
                    hide-bottom-space
                  />
                </div>
                <q-input v-model="form.device_sn" label="Serial Number (S/N)" />
                <q-input
                  v-model="form.equipments"
                  label="Kelengkapan *"
                  placeholder="Mis: Unit + Charger"
                  :rules="[(val) => !!val || 'Wajib diisi']"
                  hide-bottom-space
                />
              </q-tab-panel>

              <q-tab-panel name="service" class="q-gutter-y-sm">
                <q-input
                  v-model="form.problems"
                  label="Keluhan Utama *"
                  type="textarea"
                  autogrow
                  :rules="[(val) => !!val || 'Wajib diisi']"
                  hide-bottom-space
                />
                <q-input
                  v-model="form.actions"
                  label="Tindakan / Solusi"
                  type="textarea"
                  autogrow
                />

                <q-select
                  v-model="form.technician_id"
                  label="Teknisi"
                  use-input
                  :options="filteredTechnicians"
                  @filter="filterTechnicians"
                  map-options
                  emit-value
                  clearable
                />

                <div class="row q-col-gutter-sm q-mt-sm">
                  <q-select
                    class="col-6"
                    v-model="form.service_status"
                    label="Status Servis"
                    :options="[
                      { label: 'Diterima', value: 'received' },
                      { label: 'Dicek', value: 'checking' },
                      { label: 'Dikerjakan', value: 'working' },
                      { label: 'Siap', value: 'completed' },
                      { label: 'Diambil', value: 'picked' },
                    ]"
                    map-options
                    emit-value
                  />
                  <q-select
                    class="col-6"
                    v-model="form.repair_status"
                    label="Hasil"
                    :options="[
                      { label: 'Pending', value: 'pending' },
                      { label: 'Bisa', value: 'repairable' },
                      { label: 'Gagal', value: 'unrepairable' },
                    ]"
                    map-options
                    emit-value
                  />
                </div>

                <div class="text-subtitle2 q-mt-md text-grey-8">Timeline</div>
                <div class="row q-col-gutter-sm">
                  <DateTimePicker
                    class="col-6"
                    v-model="form.received_datetime"
                    label="Tgl Diterima"
                  />
                  <DateTimePicker
                    class="col-6"
                    v-model="form.checked_datetime"
                    label="Tgl Diperiksa"
                  />
                  <DateTimePicker
                    class="col-6"
                    v-model="form.worked_datetime"
                    label="Tgl Dikerjakan"
                  />
                  <DateTimePicker
                    class="col-6"
                    v-model="form.completed_datetime"
                    label="Tgl Selesai"
                  />
                  <DateTimePicker
                    class="col-6"
                    v-model="form.picked_datetime"
                    label="Tgl Diambil"
                  />
                </div>
              </q-tab-panel>

              <q-tab-panel name="billing" class="q-gutter-y-sm">
                <q-select
                  v-model="form.payment_status"
                  label="Status Pembayaran"
                  :options="[
                    { label: 'Belum Bayar', value: 'unpaid' },
                    { label: 'DP/Sebagian', value: 'partially_paid' },
                    { label: 'Lunas', value: 'fully_paid' },
                  ]"
                  map-options
                  emit-value
                />

                <LocaleNumberInput
                  v-model="form.estimated_cost"
                  label="Estimasi Biaya"
                />
                <LocaleNumberInput
                  v-model="form.down_payment"
                  label="Uang Muka (DP)"
                />
                <LocaleNumberInput
                  v-model="form.total_cost"
                  label="Total Biaya Akhir"
                />

                <div class="row q-col-gutter-sm q-mt-sm">
                  <DatePicker
                    class="col-7"
                    v-model="form.warranty_start_date"
                    label="Mulai Garansi"
                  />
                  <q-input
                    class="col-5"
                    v-model.number="form.warranty_day_count"
                    type="number"
                    label="Hari Garansi"
                  />
                </div>

                <q-input
                  v-model="form.notes"
                  label="Catatan Internal"
                  type="textarea"
                  autogrow
                />

                <div
                  v-if="form.id"
                  class="q-mt-lg q-pa-sm bg-blue-grey-1 text-caption text-blue-grey-8"
                >
                  <div v-if="page.props.data.creator">
                    Dibuat oleh: {{ page.props.data.creator.name }} ({{
                      $dayjs(page.props.data.created_at).format(
                        "DD/MM/YY HH:mm"
                      )
                    }})
                  </div>
                  <div v-if="page.props.data.updater">
                    Update: {{ page.props.data.updater.name }} ({{
                      $dayjs(page.props.data.updated_at).format(
                        "DD/MM/YY HH:mm"
                      )
                    }})
                  </div>
                </div>
              </q-tab-panel>
            </q-tab-panels>

            <q-separator />

            <q-card-section class="q-gutter-sm text-right">
              <q-btn
                flat
                label="Batal"
                color="grey-7"
                @click="router.get(route('service.service-order.index'))"
                :disable="form.processing"
              />
              <q-btn
                unelevated
                icon="save"
                label="Simpan Order"
                color="primary"
                @click="submit"
                :disable="form.processing"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>

<style scoped>
.q-tab-panel {
  padding: 16px;
}
</style>
