<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { create_options, create_options_from_technicians, create_options_from_customers_with_phone, scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import DatePicker from "@/components/DatePicker.vue";

import { ref } from "vue";

const page = usePage();
const order_statuses = create_options(window.CONSTANTS.SERVICEORDER_ORDERSTATUSES);
const payment_statuses = create_options(window.CONSTANTS.SERVICEORDER_PAYMENTSTATUSES);
const service_statuses = create_options(window.CONSTANTS.SERVICEORDER_SERVICESTATUSES);
const repair_statuses = create_options(window.CONSTANTS.SERVICEORDER_REPAIRSTATUSES);
const customers = ref([{ value: 0, label: '<< Pelanggan Baru >>' }, ...create_options_from_customers_with_phone(page.props.customers)]);
const filteredCustomers = ref([...customers.value]);
const technicians = ref(create_options_from_technicians(page.props.technicians));
const filteredTechnicians = ref([...technicians.value]);
const title = !!page.props.data.id ? `Edit Order Servis #${page.props.data.id}` : 'Tambah Order Servis';
const form = useForm({
  id: page.props.data.id,
  order_status: page.props.data.order_status,

  customer_id: page.props.data.customer_id ?? 0,
  customer_name: page.props.data.customer_name,
  customer_phone: page.props.data.customer_phone,
  customer_address: page.props.data.customer_address,

  device_type: page.props.data.device_type,
  device: page.props.data.device,
  equipments: page.props.data.equipments,
  device_sn: page.props.data.device_sn,

  problems: page.props.data.problems,
  actions: page.props.data.actions,

  service_status: page.props.data.service_status ?? '',
  repair_status: page.props.data.repair_status ?? '',

  technician_id: page.props.data.technician_id ?? '',
  received_datetime: page.props.data.received_datetime ?? '',
  checked_datetime: page.props.data.checked_datetime ?? '',
  worked_datetime: page.props.data.worked_datetime ?? '',
  completed_datetime: page.props.data.completed_datetime ?? '',
  picked_datetime: page.props.data.picked_datetime ?? '',

  payment_status: page.props.data.payment_status,
  down_payment: parseFloat(page.props.data.down_payment) ?? 0,
  estimated_cost: parseFloat(page.props.data.estimated_cost) ?? 0,
  total_cost: parseFloat(page.props.data.total_cost) ?? 0,

  warranty_start_date: page.props.data.warranty_start_date ?? '',
  warranty_day_count: page.props.data.warranty_day_count ?? '',

  notes: page.props.data.notes,
});

const submit = () => {
  tab.value = 'main';
  handleSubmit({ form, url: route('admin.service-order.save') });
}

const filterCustomers = (val, update) => {
  update(() => {
    const search = val.toLowerCase();
    filteredCustomers.value = customers.value.filter(item =>
      item.label.toLowerCase().includes(search)
    );
  });
};

const filterTechnicians = (val, update) => {
  update(() => {
    const search = val.toLowerCase();
    filteredTechnicians.value = technicians.value.filter(item =>
      item.label.toLowerCase().includes(search)
    );
  });
};

const onCustomerChange = (val) => {
  if (!!val) {
    const customer = page.props.customers.find(customer => customer.id === val);
    form.customer_name = customer.name;
    form.customer_phone = customer.phone;
    form.customer_address = customer.address;
  }
  else {
    form.customer_name = '';
    form.customer_phone = '';
    form.customer_address = '';
  }
}

const tab = ref('main');

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form @submit.prevent="submit" class="q-gutter-md" @validation-error="scrollToFirstErrorField">
          <div class="row">
            <q-card square flat bordered class="q-card col">
              <q-tabs v-model="tab" align="left">
                <q-tab name="main" label="Info Utama" />
                <q-tab name="service" label="Info Servis" />
                <q-tab name="other" label="Lainnya" />
              </q-tabs>
              <q-tab-panels v-model="tab">
                <q-tab-panel name="main">
                  <div class="text-subtitle1">Info Order</div>
                  <input type="hidden" name="id" v-model="form.id" />
                  <q-input :model-value="form.id ? form.id : 'Otomatis'" label="#No Order" readonly />
                  <q-select v-model="form.order_status" label="Status Order" :options="order_statuses" map-options
                    emit-value :error="!!form.errors.order_status" :disable="form.processing" />
                  <table v-if="!!form.id">
                    <tbody>
                      <tr>
                        <td>Dibuat Oleh</td>
                        <td>:</td>
                        <td>{{ page.props.data.created_by.name }} ({{ page.props.data.created_by.username }}) - {{
                          $dayjs(new Date(page.props.data.created_datetime)).format('dddd, D MMMM YYYY pukul HH:mm:ss')
                        }}
                        </td>
                      </tr>
                      <tr v-if="!!page.props.data.updated_datetime">
                        <td>Diperbarui oleh</td>
                        <td>:</td>
                        <td>{{ page.props.data.updated_by.name }} ({{ page.props.data.updated_by.username }}) - {{
                          $dayjs(new Date(page.props.data.updated_datetime)).format('dddd, D MMMM YYYY pukul HH:mm:ss')
                        }}
                        </td>
                      </tr>
                      <tr v-if="!!page.props.data.closed_datetime">
                        <td>Diselesaikan oleh</td>
                        <td>:</td>
                        <td>{{ page.props.data.closed_by.name }} ({{ page.props.data.closed_by.username }}) - {{
                          $dayjs(new Date(page.props.data.closed_datetime)).format('dddd, D MMMM YYYY pukul HH:mm:ss')
                          }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="text-subtitle1 q-pt-lg">Info Pelanggan</div>
                  <q-select v-model="form.customer_id" label="Pelanggan" use-input input-debounce="300" clearable
                    :options="filteredCustomers" map-options emit-value @filter="filterCustomers"
                    @update:model-value="onCustomerChange" :error="!!form.errors.customer_id"
                    :disable="form.processing">
                    <template v-slot:no-option>
                      <q-item>
                        <q-item-section>Pelanggan tidak ditemukan</q-item-section>
                      </q-item>
                    </template>
                  </q-select>
                  <q-input v-model.trim="form.customer_name" label="Nama" lazy-rules
                    :error="!!form.errors.customer_name" :disable="form.processing"
                    :error-message="form.errors.customer_name" :rules="[
                      (val) => (val && val.length > 0) || 'Nama harus diisi.',
                    ]" />
                  <q-input v-model.trim="form.customer_phone" type="text" label="No HP" lazy-rules
                    :disable="form.processing" :error="!!form.errors.customer_phone"
                    :error-message="form.errors.customer_phone" :rules="[
                      (val) => (val && val.length > 0) || 'No HP harus diisi.',
                    ]" />
                  <q-input v-model.trim="form.customer_address" type="textarea" autogrow counter maxlength="1000"
                    label="Alamat" lazy-rules :disable="form.processing" :error="!!form.errors.customer_address"
                    :error-message="form.errors.customer_address" :rules="[
                      (val) => (val && val.length > 0) || 'Alamat harus diisi.',
                    ]" />
                  <div class="text-subtitle1 q-pt-md">Info Perangkat</div>
                  <q-input v-model.trim="form.device_type" label="Jenis Perangkat" lazy-rules :error="!!form.errors.device_type"
                    :disable="form.processing" :error-message="form.errors.device_type" :rules="[
                      (val) => (val && val.length > 0) || 'Jenis perangkat harus diisi.',
                    ]" />
                  <q-input v-model.trim="form.device" label="Perangkat" lazy-rules :error="!!form.errors.device"
                    :disable="form.processing" :error-message="form.errors.device" :rules="[
                      (val) => (val && val.length > 0) || 'Perangkat harus diisi.',
                    ]" />
                  <q-input v-model.trim="form.equipments" type="text" label="Perlengkapan" lazy-rules
                    :disable="form.processing" :error="!!form.errors.equipments" :error-message="form.errors.equipments"
                    :rules="[
                      (val) => (val && val.length > 0) || 'Perlengkapan harus diisi.',
                    ]" />
                  <q-input v-model.trim="form.device_sn" type="text" label="SN / PN / ID" lazy-rules
                    :disable="form.processing" :error="!!form.errors.device_sn"
                    :error-message="form.errors.device_sn" />
                  <div class="text-subtitle1 q-pt-md">Kendala dan Tindakan</div>
                  <q-input v-model.trim="form.problems" label="Masalah / Keluhan" lazy-rules
                    :error="!!form.errors.problems" :disable="form.processing" :error-message="form.errors.problems"
                    :rules="[
                      (val) => (val && val.length > 0) || 'Masalah / keluhan harus diisi.',
                    ]" />
                  <q-input v-model.trim="form.actions" type="text" label="Tindakan" lazy-rules
                    :disable="form.processing" :error="!!form.errors.actions" :error-message="form.errors.actions"
                    :rules="[
                      (val) => (val && val.length > 0) || 'Tindakan harus diisi.',
                    ]" />
                </q-tab-panel>
                <q-tab-panel name="service">
                  <q-select v-model="form.service_status" label="Status Servis" :options="service_statuses" map-options
                    emit-value :error="!!form.errors.service_status" :disable="form.processing" />
                  <q-select v-model="form.repair_status" label="Sukses / Gagal" :options="repair_statuses" map-options
                    emit-value :error="!!form.errors.repair_status" :disable="form.processing" />
                  <q-select v-model="form.technician_id" label="Teknisi" :options="filteredTechnicians" map-options
                    clearable emit-value @filter="filterTechnicians" use-input input-debounce="300"
                    :error="!!form.errors.technician_id" :disable="form.processing">
                    <template v-slot:no-option>
                      <q-item>
                        <q-item-section>Teknisi tidak ditemukan</q-item-section>
                      </q-item>
                    </template>
                  </q-select>
                  <date-time-picker v-model="form.received_datetime" label="Tanggal Diterima"
                    :error="!!form.errors.received_datetime" :disable="form.processing" />
                  <date-time-picker v-model="form.checked_datetime" label="Tanggal Diperiksa"
                    :error="!!form.errors.checked_datetime" :disable="form.processing" />
                  <date-time-picker v-model="form.worked_datetime" label="Tanggal Dikerjakan"
                    :error="!!form.errors.worked_datetime" :disable="form.processing" />
                  <date-time-picker v-model="form.completed_datetime" label="Tanggal Selesai"
                    :error="!!form.errors.completed_datetime" :disable="form.processing" />
                  <date-time-picker v-model="form.picked_datetime" label="Tanggal Diambil"
                    :error="!!form.errors.picked_datetime" :disable="form.processing" />
                </q-tab-panel>
                <q-tab-panel name="other">
                  <div class="text-subtitle1">Info Biaya</div>
                  <q-select v-model="form.payment_status" label="Status Pembayaran" :options="payment_statuses"
                    map-options emit-value :error="!!form.errors.payment_status" :disable="form.processing" />
                  <LocaleNumberInput v-model:modelValue="form.estimated_cost" label="Biaya Perkiraan" lazyRules
                    :disable="form.processing" :error="!!form.errors.estimated_cost"
                    :errorMessage="form.errors.estimated_cost" :rules="[]" />
                  <LocaleNumberInput v-model:modelValue="form.down_payment" label="Uang Muka" lazyRules
                    :disable="form.processing" :error="!!form.errors.down_payment"
                    :errorMessage="form.errors.down_payment" :rules="[]" />
                  <LocaleNumberInput v-model:modelValue="form.total_cost" label="Total Biaya" lazyRules
                    :disable="form.processing" :error="!!form.errors.down_payment"
                    :errorMessage="form.errors.down_payment" :rules="[]" />
                  <date-picker v-model="form.warranty_start_date" label="Tanggal Mulai Garansi"
                    :error="!!form.errors.warranty_start_date" :disable="form.processing" />
                  <q-input v-model.trim="form.warranty_day_count" label="Lama Garanasi (hari)" type="number" step="1" lazy-rules
                    :disable="form.processing" />
                  <q-input v-model.trim="form.notes" type="textarea" label="Catatan" autogrow counter
                    maxlength="1000" lazy-rules :disable="form.processing" :error="!!form.errors.notes"
                    :error-message="form.errors.notes" />
                </q-tab-panel>
              </q-tab-panels>
              <q-card-section class="q-gutter-sm">
                <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
                <q-btn icon="cancel" label="Batal" :disable="form.processing"
                  @click="router.get(route('admin.service-order.index'))" />
              </q-card-section>
            </q-card>
          </div>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>
