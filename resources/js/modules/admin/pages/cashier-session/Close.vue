<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { formatMoney } from "@/helpers/formatter";

const page = usePage();
const title = "Tutup Sesi Kasir";
const form = useForm({
  closing_notes: page.props.data.closing_notes,
  closing_balance: parseFloat(page.props.data.closing_balance),
});

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.cashier-session.close", { id: page.props.data.id }),
  });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.cashier-session.index'))"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <input type="hidden" name="id" v-model="form.id" />
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <table class="detail q-mt-md">
                <tbody>
                  <tr>
                    <td style="width: 130px">Session ID</td>
                    <td style="width: 1px">:</td>
                    <td>{{ page.props.data.id }}</td>
                  </tr>
                  <tr>
                    <td>Terminal</td>
                    <td>:</td>
                    <td>{{ page.props.data.cashier_terminal.name }}</td>
                  </tr>
                  <tr>
                    <td>Kasir</td>
                    <td>:</td>
                    <td>
                      {{ page.props.data.user.username }} -
                      {{ page.props.data.user.name }}
                    </td>
                  </tr>
                  <tr>
                    <td>Saldo Awal</td>
                    <td>:</td>
                    <td>
                      {{ formatMoney(page.props.data.opening_balance) }}
                    </td>
                  </tr>
                  <tr v-if="page.props.data.opening_notes">
                    <td>Catatan Buka</td>
                    <td>:</td>
                    <td>{{ page.props.data.opening_notes }}</td>
                  </tr>
                  <tr>
                    <td>Total Penjualan</td>
                    <td>:</td>
                    <td>Rp. 0</td>
                  </tr>
                  <tr>
                    <td>Total Pemasukan</td>
                    <td>:</td>
                    <td>Rp. 0</td>
                  </tr>
                  <tr>
                    <td>Total Pengeluaran</td>
                    <td>:</td>
                    <td>Rp. 0</td>
                  </tr>
                  <tr>
                    <td>Saldo Akhir</td>
                    <td>:</td>
                    <td>
                      <div class="text-bold">
                        {{ formatMoney(page.props.data.closing_balance) }}
                      </div>
                      <div class="text-red">
                        Pastikan saldo akhir sesuai dengan jumlah uang di laci!
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <q-input
                v-model.trim="form.closing_notes"
                type="textarea"
                maxlength="200"
                label="Catatan"
                :disable="form.processing"
                :error="!!form.errors.closing_notes"
                :error-message="form.errors.closing_notes"
                :rules="[]"
                autogrow
                counter
                lazy-rules
                hide-bottom-space
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                type="submit"
                label="Tutup"
                icon="logout"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                label="Batal"
                icon="cancel"
                :disable="form.processing"
                @click="router.get(route('admin.cashier-session.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
