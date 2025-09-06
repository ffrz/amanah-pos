<script setup>
import { ref } from "vue";
import axios from "axios";
import { formatNumber } from "@/helpers/formatter";

const selectedCustomer = ref(null);
const options = ref([]);
let timeoutId = null;

const filterFn = (val, update) => {
  if (val.length < 2) {
    update(() => {
      options.value = [];
    });
    return;
  }

  if (timeoutId) {
    clearTimeout(timeoutId);
  }

  timeoutId = setTimeout(() => {
    axios
      .get(`/web-api/customers?q=${val}`)
      .then((response) => {
        const transformedData = response.data.data.map((customer) => {
          return {
            label: `${customer.username} - ${customer.name}`,
            value: customer.id,
            data: customer,
          };
        });

        update(() => {
          options.value = transformedData;
        });
      })
      .catch((error) => {
        console.error("Gagal mengambil data pelanggan:", error);
        update(() => {
          options.value = [];
        });
      });
  });
};
</script>
<template>
  <guest-layout>
    <div class="q-ma-md">
      <q-select
        class="full-width"
        v-model="selectedCustomer"
        use-input
        label="Cari Pelanggan"
        :options="options"
        @filter="filterFn"
        hint="Ketik minimal 2 karakter untuk mencari"
        clearable
      />
      <div v-if="selectedCustomer">
        Saldo: Rp. {{ formatNumber(selectedCustomer.data.balance) }}
      </div>
    </div>
  </guest-layout>
</template>
