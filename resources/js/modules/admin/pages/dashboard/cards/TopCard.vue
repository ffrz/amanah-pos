<script setup>
import { formatNumber } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";
const page = usePage();
</script>

<template>
  <div class="row q-col-gutter-sm q-pb-sm">
    <div class="col-md-6 col-12">
      <q-card square bordered flat class="full-width full-height q-pa-md">
        <div class="text-subtitle1 text-center q-mb-sm">
          Top 5 Pelanggan (Penjualan)
        </div>
        <template v-if="page.props.data.top_customers_revenue.length > 0">
          <q-list>
            <q-item
              v-for="(item, index) in page.props.data.top_customers_revenue"
              :key="index"
            >
              <q-item-section avatar>
                <q-avatar
                  :color="'purple-' + (9 - index)"
                  text-color="white"
                  size="sm"
                  >{{ index + 1 }}</q-avatar
                >
              </q-item-section>
              <q-item-section>
                <q-item-label>
                  <my-link
                    :href="
                      route('admin.customer.detail', { id: item.customer_id })
                    "
                    class="text-purple"
                  >
                    {{ item.customer_name }}
                  </my-link>
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-item-label class="text-purple">{{
                  formatNumber(item.total_revenue)
                }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </template>
        <template v-else>
          <div class="text-grey text-italic">Data tidak tersedia</div>
        </template>
      </q-card>
    </div>
    <div class="col-md-6 col-12">
      <q-card square bordered flat class="full-width full-height q-pa-md">
        <div class="text-subtitle1 text-center q-mb-sm">
          Top 5 Deposit Pelanggan
        </div>
        <template v-if="page.props.data.top_customers_wallet.length > 0">
          <q-list>
            <q-item
              v-for="(item, index) in page.props.data.top_customers_wallet"
              :key="index"
            >
              <q-item-section avatar>
                <q-avatar
                  :color="'orange-' + (9 - index)"
                  text-color="white"
                  size="sm"
                  >{{ index + 1 }}</q-avatar
                >
              </q-item-section>
              <q-item-section>
                <q-item-label>
                  <my-link
                    :href="route('admin.customer.detail', { id: item.id })"
                    class="text-orange-9"
                  >
                    {{ item.name }}
                  </my-link>
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-item-label class="text-orange-9">{{
                  formatNumber(item.wallet_balance)
                }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </template>
        <template v-else>
          <div class="text-grey text-italic">Data tidak tersedia</div>
        </template>
      </q-card>
    </div>
  </div>
</template>
