<script setup>
import LongTextView from "@/components/LongTextView.vue";
import {
  formatDateTime,
  formatNumber,
  plusMinusSymbol,
} from "@/helpers/formatter";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
</script>

<template>
  <q-card bordered square class="no-shadow q-pa-sm">
    <q-card-section class="q-pa-none">
      <div class="text-subtitle1 text-bold text-grey-8">
        Transaksi Wallet Terkini
      </div>
      <template v-if="page.props.data.recent_wallet_transactions.length > 0">
        <q-list>
          <q-item
            v-for="item in page.props.data.recent_wallet_transactions"
            :key="item.id"
            clickable
            @click.stop="
              router.get(
                route('customer.wallet-transaction.detail', {
                  id: item.id,
                })
              )
            "
          >
            <q-item-section avatar>
              <q-icon
                :color="item.amount > 0 ? 'green' : 'red'"
                size="xs"
                :name="item.amount > 0 ? 'download' : 'upload'"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label>
                <div>
                  <q-icon class="inline-icon" name="tag" />
                  {{ item.formatted_id }}
                </div>
                <div>
                  <q-icon class="inline-icon" name="calendar_clock" />
                  {{ formatDateTime(item.datetime) }}
                </div>
                <LongTextView icon="notes" :text="item.notes" />
                <div>
                  <q-badge :color="item.amount > 0 ? 'positive' : 'negative'">
                    {{
                      $CONSTANTS.CUSTOMER_WALLET_TRANSACTION_TYPES[item.type]
                    }}
                  </q-badge>
                </div>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-item-label
                class="ext-bold"
                :class="item.amount > 0 ? 'text-green-8' : 'text-red-8'"
              >
                {{ plusMinusSymbol(item.amount) }}Rp.
                {{ formatNumber(Math.abs(item.amount)) }}
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
