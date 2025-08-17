<script setup>
import { ref } from "vue";

const latestTopups = ref([
  {
    id: 1,
    date: "12-08-2025",
    amount: 100000,
    description: "Top Up Manual",
    status: "Berhasil",
  },
  {
    id: 2,
    date: "01-08-2025",
    amount: 50000,
    description: "Top Up Transfer Bank",
    status: "Berhasil",
  },
  {
    id: 3,
    date: "15-07-2025",
    amount: 50000,
    description: "Top Up Transfer Bank",
    status: "Berhasil",
  },
]);
</script>

<template>
  <q-card bordered square class="no-shadow q-pa-sm">
    <q-card-section class="q-pa-none">
      <div class="text-subtitle2 text-bold text-grey-8">
        Top Up Wallet Terkini
      </div>
      <q-list>
        <q-item v-for="topup in latestTopups" :key="topup.id">
          <q-item-section avatar>
            <q-icon color="green" name="arrow_downward" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ topup.description }}</q-item-label>
            <q-item-label caption>{{ topup.date }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-item-label class="text-green-8 text-bold">
              +Rp {{ topup.amount.toLocaleString("id-ID") }}
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-card-section>
  </q-card>
</template>
<style scoped>
.q-card {
  width: 100%;
}
</style>
