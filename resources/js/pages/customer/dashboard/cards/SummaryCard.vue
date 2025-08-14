<script setup>
import { defineProps } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { formatNumber } from "@/helpers/utils";

const props = defineProps({
  summaryData: {
    type: Object,
    required: true,
    default: () => ({
      balance: 0,
      total_in: 0,
      total_out: 0,
    }),
  },
});
</script>

<template>
  <div class="row">
    <q-card class="bg-transparent no-shadow no-border col" bordered>
      <q-card-section class="q-pa-none">
        <div class="row q-col-gutter-sm">
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <q-item :style="`background-color: #fd8e24`" class="q-pa-none">
              <q-item-section
                side
                :style="`background-color: #fd7e14`"
                class="q-pa-lg q-mr-none text-white"
              >
                <q-icon
                  class="material-filled"
                  name="account_balance_wallet"
                  color="white"
                  size="24px"
                />
              </q-item-section>
              <q-item-section class="q-pa-md q-ml-none text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  Rp. {{ formatNumber(props.summaryData.balance) }}
                </q-item-label>
                <q-item-label>Saldo Aktual</q-item-label>
              </q-item-section>
            </q-item>
          </div>

          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <q-item :style="`background-color: #28a745`" class="q-pa-none">
              <q-item-section
                side
                :style="`background-color: #218838`"
                class="q-pa-lg q-mr-none text-white"
              >
                <q-icon
                  class="material-filled"
                  name="add_circle"
                  color="white"
                  size="24px"
                />
              </q-item-section>
              <q-item-section class="q-pa-md q-ml-none text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  Rp. {{ formatNumber(props.summaryData.total_in) }}
                </q-item-label>
                <q-item-label>Total Pemasukan</q-item-label>
              </q-item-section>
            </q-item>
          </div>

          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <q-item :style="`background-color: #dc3545`" class="q-pa-none">
              <q-item-section
                side
                :style="`background-color: #c82333`"
                class="q-pa-lg q-mr-none text-white"
              >
                <q-icon
                  class="material-filled"
                  name="remove_circle"
                  color="white"
                  size="24px"
                />
              </q-item-section>
              <q-item-section class="q-pa-md q-ml-none text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  Rp. {{ formatNumber(props.summaryData.total_out) }}
                </q-item-label>
                <q-item-label>Total Pengeluaran</q-item-label>
              </q-item-section>
            </q-item>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>
