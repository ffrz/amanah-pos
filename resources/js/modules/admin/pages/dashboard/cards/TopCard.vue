<script setup>
import { defineProps } from "vue";
import { formatNumber } from "@/helpers/formatter";

const props = defineProps({
  dashboardData: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div class="row q-col-gutter-sm q-pb-sm">
    <div class="col-md-6 col-12">
      <q-card square bordered flat class="full-width full-height q-pa-md">
        <div class="text-h6 q-mb-sm">Top 5 Pembelian Pelanggan</div>
        <q-list separator>
          <q-item
            v-for="(item, index) in dashboardData.top_customers_sales"
            :key="index"
          >
            <q-item-section avatar>
              <q-avatar color="primary" text-color="white" size="sm">{{
                index + 1
              }}</q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ item.name }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-item-label class="text-primary">{{
                formatNumber(item.value)
              }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card>
    </div>
    <div class="col-md-6 col-12">
      <q-card square bordered flat class="full-width full-height q-pa-md">
        <div class="text-h6 q-mb-sm">Top 5 Top Up Pelanggan</div>
        <q-list separator>
          <q-item
            v-for="(item, index) in dashboardData.top_customers_topup"
            :key="index"
          >
            <q-item-section avatar>
              <q-avatar color="green-7" text-color="white" size="sm">{{
                index + 1
              }}</q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ item.name }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-item-label class="text-green-7">{{
                formatNumber(item.value)
              }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card>
    </div>
  </div>
</template>
