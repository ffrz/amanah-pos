<script setup>
import { ref } from "vue";

const latestTransactions = ref([
  {
    id: 1,
    date: "14-08-2025",
    amount: 15000,
    description: "Jajan Kantin",
    type: "keluar",
  },
  {
    id: 2,
    date: "13-08-2025",
    amount: 5000,
    description: "Beli Buku Tulis",
    type: "keluar",
  },
  {
    id: 3,
    date: "12-08-2025",
    amount: 10000,
    description: "Fotokopi Materi",
    type: "keluar",
  },
  {
    id: 4,
    date: "11-08-2025",
    amount: 20000,
    description: "Makan di Koperasi",
    type: "keluar",
  },
]);
</script>

<template>
  <q-card bordered square class="no-shadow q-pa-sm">
    <q-card-section class="q-pa-none">
      <div class="text-subtitle2 text-bold text-grey-8">Transaksi Terkini</div>
      <q-list>
        <q-item v-for="transaction in latestTransactions" :key="transaction.id">
          <q-item-section avatar>
            <q-icon color="red" name="arrow_upward" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ transaction.description }}</q-item-label>
            <q-item-label caption>{{ transaction.date }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-item-label class="text-red-8 text-bold">
              -Rp {{ transaction.amount.toLocaleString("id-ID") }}
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
