<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { computed, watch } from 'vue';
import { create_options, create_options_from_customers_with_phone, scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import DatePicker from "@/components/DatePicker.vue";
import { formatNumber } from "@/helpers/utils";

import { ref } from "vue";

const page = usePage();
const order_statuses = create_options(window.CONSTANTS.WASHORDER_ORDERSTATUSES);
const payment_statuses = create_options(window.CONSTANTS.WASHORDER_PAYMENTSTATUSES);
const service_statuses = create_options(window.CONSTANTS.WASHORDER_SERVICESTATUSES);
const vehicles = page.props.vehicles;
const filteredVehicles = ref(page.props.vehicles);
const services = [{ id: null, name: 'Tidak Dipilih', price: 0 }, ...page.props.services];
const customers = ref([{ value: 0, label: '<< Pelanggan Baru >>' }, ...create_options_from_customers_with_phone(page.props.customers)]);
const filteredCustomers = ref([...customers.value]);

const service_options = computed(() => {
  return services.map(service => ({
    label: `${service.name} - Rp ${formatNumber(service.price)}`,
    value: service.id,
    price: service.price,
    name: service.name
  }))
})

const title = !!page.props.data.id ? `Edit Order Cuci #${page.props.data.id}` : 'Tambah Order Cuci';
const form = useForm({
  id: page.props.data.id,
  order_status: page.props.data.order_status,

  customer_id: page.props.data.customer_id ?? 0,
  customer_name: page.props.data.customer_name,
  customer_phone: page.props.data.customer_phone,
  customer_address: page.props.data.customer_address,

  vehicle_plate_number: page.props.data.vehicle_plate_number,
  vehicle_description: page.props.data.vehicle_description,

  problems: page.props.data.problems,
  actions: page.props.data.actions,

  service_status: page.props.data.service_status ?? '',
  payment_status: page.props.data.payment_status,

  service_1: page.props.data.service_1,
  service_2: page.props.data.service_2,
  service_3: page.props.data.service_3,
  service_4: page.props.data.service_4,
  service_5: page.props.data.service_5,

  total_price: parseFloat(page.props.data.total_price) ?? 0,

  notes: page.props.data.notes,
});

const submit = () => {
  tab.value = 'main';
  handleSubmit({ form, url: route('admin.wash-order.save') });
}

const filterVehicles = (val, update) => {
  update(() => {
    const search = val.toLowerCase();
    filteredVehicles.value = vehicles.filter(item =>
      item.toLowerCase().includes(search)
    );
  });
};

const filterCustomers = (val, update) => {
  update(() => {
    const search = val.toLowerCase();
    filteredCustomers.value = customers.value.filter(item =>
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

const calculateTotalPrice = () => {
  const prices = [
    services.find(s => s.id === form.service_1)?.price || 0,
    services.find(s => s.id === form.service_2)?.price || 0,
    services.find(s => s.id === form.service_3)?.price || 0,
    services.find(s => s.id === form.service_4)?.price || 0,
    services.find(s => s.id === form.service_5)?.price || 0,
  ];
  form.total_price = prices.reduce((sum, val) => sum + (parseFloat(val) || 0), 0);
};

watch(
  () => [
    form.service_1,
    form.service_2,
    form.service_3,
    form.service_4,
    form.service_5
  ],
  calculateTotalPrice
);

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
                <q-tab name="main" label="Info Order" />
                <q-tab name="other" label="Info Layanan dan Biaya" />
              </q-tabs>
              <q-tab-panels v-model="tab">
                <q-tab-panel name="main">
                  <div class="text-subtitle1">Info Order</div>
                  <input type="hidden" name="id" v-model="form.id" />
                  <q-input :model-value="form.id ? form.id : 'Otomatis'" label="#No Order" readonly />
                  <q-select v-model="form.order_status" label="Status Order" :options="order_statuses" map-options
                    emit-value :error="!!form.errors.order_status" :disable="form.processing" />
                  <div class="text-subtitle1 q-pt-md">Info Kendaraan</div>
                  <q-input v-model.trim="form.vehicle_plate_number" label="No Polisi" lazy-rules
                    :error="!!form.errors.vehicle_plate_number" :disable="form.processing"
                    @blur="val => form.vehicle_plate_number = form.vehicle_plate_number?.toUpperCase()"
                    :error-message="form.errors.vehicle_plate_number" :rules="[
                      (val) => (val && val.length > 0) || 'No Polisi harus diisi.',
                    ]" />
                  <q-select v-model="form.vehicle_description" label="Kendaraan" use-input input-debounce="300"
                    clearable :options="filteredVehicles" map-options emit-value @filter="filterVehicles"
                    new-value-mode="add"
                    @blur="val => form.vehicle_description = form.vehicle_description?.toUpperCase()"
                    :error="!!form.errors.vehicle_description" :error-message="form.errors.vehicle_description" :rules="[
                      (val) => (val && val.length > 0) || 'Kendaraan harus diisi.',
                    ]" :disable="form.processing">
                  </q-select>
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
                    @blur="val => form.customer_name = form.customer_name?.toUpperCase()"
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
                  <div class="text-subtitle1 q-pt-lg">Info Lainnya</div>
                  <q-input v-model.trim="form.notes" type="textarea" label="Catatan" autogrow counter maxlength="1000"
                    lazy-rules :disable="form.processing" :error="!!form.errors.notes"
                    :error-message="form.errors.notes" />
                </q-tab-panel>
                <q-tab-panel name="other">
                  <div class="text-subtitle1">Info Layanan</div>
                  <q-select v-model="form.service_1" label="Layanan 1" :options="service_options" map-options emit-value
                    :error="!!form.errors.service_1" :disable="form.processing" />
                  <q-select v-model="form.service_2" label="Layanan 2" :options="service_options" map-options emit-value
                    :error="!!form.errors.service_2" :disable="form.processing" />
                  <q-select v-model="form.service_3" label="Layanan 3" :options="service_options" map-options emit-value
                    :error="!!form.errors.service_3" :disable="form.processing" />
                  <q-select v-model="form.service_4" label="Layanan 4" :options="service_options" map-options emit-value
                    :error="!!form.errors.service_4" :disable="form.processing" />
                  <q-select v-model="form.service_5" label="Layanan 5" :options="service_options" map-options emit-value
                    :error="!!form.errors.service_5" :disable="form.processing" />
                  <div class="text-subtitle1">Info Biaya</div>
                  <q-select v-model="form.payment_status" label="Status Pembayaran" :options="payment_statuses"
                    map-options emit-value :error="!!form.errors.payment_status" :disable="form.processing" />
                  <LocaleNumberInput v-model:modelValue="form.total_price" label="Total Biaya" lazyRules
                    :disable="form.processing" :error="!!form.errors.total_price"
                    :errorMessage="form.errors.total_price" :rules="[]" />
                </q-tab-panel>
              </q-tab-panels>
              <q-card-section class="q-gutter-sm">
                <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
                <q-btn icon="cancel" label="Batal" :disable="form.processing"
                  @click="router.get(route('admin.wash-order.index'))" />
              </q-card-section>
            </q-card>
          </div>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>
