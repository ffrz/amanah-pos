<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { useSupplierFilter } from '@/helpers/useSupplierFilter';
import { create_options, scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";
const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Order Pembelian";

const { filteredSuppliers, filterSupplierFn } = useSupplierFilter(page.props.suppliers, false);

const statuses = create_options(window.CONSTANTS.PURCHASE_ORDER_STATUSES);
const payment_statuses = create_options(window.CONSTANTS.PURCHASE_ORDER_PAYMENT_STATUSES);
const delivery_statuses = create_options(window.CONSTANTS.PURCHASE_ORDER_DELIVERY_STATUSES);

const form = useForm({
  id: page.props.data.id,
  formatted_id: page.props.data.formatted_id ?? 'Diisi Otomatis',
  supplier_id: page.props.data.supplier_id,
  datetime: dayjs(page.props.data.datetime).format('YYYY-MM-DD HH:mm:ss'),
  status: page.props.data.status,
  payment_status: page.props.data.payment_status,
  delivery_status: page.props.data.delivery_status,
  total: parseFloat(page.props.data.total),
  total_paid: parseFloat(page.props.data.total_paid),
  notes: page.props.data.notes,
});

const submit = () => handleSubmit({ form, url: route('admin.purchase-order.save') });

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input v-model.trim="form.formatted_id" label="ID Order" readonly />
              <date-time-picker v-model="form.datetime" label="Waktu" :error="!!form.errors.datetime"
                :disable="form.processing" />
              <q-select v-model="form.status" label="Status Order" :options="statuses" map-options emit-value
                :error="!!form.errors.status" :disable="form.processing" :errorMessage="form.errors.status">
              </q-select>
              <q-select v-model="form.payment_status" label="Status Pembayaran" :options="payment_statuses" map-options
                emit-value :error="!!form.errors.payment_status" :disable="form.processing"
                :errorMessage="form.errors.payment_status">
              </q-select>
              <q-select v-model="form.delivery_status" label="Status Pengiriman" :options="delivery_statuses"
                map-options emit-value :error="!!form.errors.delivery_status" :disable="form.processing"
                :errorMessage="form.errors.delivery_status">
              </q-select>
              <!-- <div class="full-width"> -->
              <!-- <div class="row items-center no-wrap q-gutter-sm"> -->
              <q-select v-model="form.supplier_id" label="Pemasok" use-input input-debounce="300" clearable
                :options="filteredSuppliers" map-options emit-value :errorMessage="form.errors.supplier_id"
                @filter="filterSupplierFn" :error="!!form.errors.supplier_id" :disable="form.processing">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Pemasok tidak ditemukan.</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <q-btn dense icon="add" type="button" color="primary" :disable="form.processing" label="Pemasok Baru"/>
              <!-- </div> -->
              <!-- </div> -->
              <!-- <LocaleNumberInput v-model:modelValue="form.amount" label="Jumlah" lazyRules :disable="form.processing"
                :error="!!form.errors.amount" :errorMessage="form.errors.amount" :rules="[]" /> -->
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="255" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes"
                :rules="[
                  (val) => (val && val.length > 0) || 'Catatan harus diisi.',
                ]" />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="create" type="submit" label="Buat Order" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.purchase-order.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>
