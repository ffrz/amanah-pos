<script setup>
import LongTextView from "@/components/LongTextView.vue";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
</script>

<template>
  <q-card bordered square class="no-shadow q-pa-sm">
    <q-card-section class="q-pa-none">
      <div class="text-subtitle2 text-bold text-grey-9 text-center q-mb-md">
        Pembelian Terkini
      </div>
      <template v-if="page.props.data.recent_purchase_orders.length > 0">
        <q-list>
          <q-item
            v-for="item in page.props.data.recent_purchase_orders"
            :key="item.id"
            clickable
            @click.stop="
              router.get(
                route('customer.purchasing-history.detail', { id: item.id })
              )
            "
          >
            <q-item-section>
              <q-item-label class="text-subtitle2">
                <div>
                  <q-icon class="inline-icon" name="tag" />{{
                    item.formatted_id
                  }}
                </div>
                <div>
                  <q-icon class="inline-icon" name="calendar_clock" />{{
                    formatDateTime(item.datetime)
                  }}
                </div>
              </q-item-label>
              <q-item-label>
                <LongTextView icon="notes" :text="item.notes" />
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-item-label class="text-red-8 text-bold">
                Rp {{ formatNumber(item.grand_total) }}
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </template>
      <template v-else>
        <p class="text-italic text-grey-8">Belum ada transaksi</p>
      </template>
    </q-card-section>
  </q-card>
</template>
<style scoped>
.q-card {
  width: 100%;
}
</style>
