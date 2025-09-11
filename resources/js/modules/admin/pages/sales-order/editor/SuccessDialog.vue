<script setup>
import { formatNumber } from "@/helpers/formatter";
import { ref } from "vue";

defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  order: {
    type: Object,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
  customer: {
    type: [Object],
    required: false,
  },
  payment: {
    type: [Object],
    required: false,
  },
});

const emit = defineEmits(["update:modelValue"]);
const printButtonRef = ref(null);
const focusOnButton = () => {
  printButtonRef.value?.focus();
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    v-on:before-show="focusOnButton"
    persistent
  >
    <q-card style="min-width: 300px">
      <q-card-section class="q-py-none q-pt-md">
        <div class="text-bold text-grey-8 text-h6" align="center">Sukses</div>
      </q-card-section>
      <q-card-section align="center">
        <div class="q-mb-md">Transaksi Berhasil</div>
        <table>
          <tbody>
            <tr>
              <td>ID</td>
              <td>:</td>
              <td>{{ order.formatted_id }}</td>
            </tr>
            <tr v-if="customer">
              <td style="vertical-align: top">Pelanggan</td>
              <td style="vertical-align: top">:</td>
              <td>{{ customer.name }} <br />{{ customer.username }}</td>
            </tr>
            <tr>
              <td>Total</td>
              <td>:</td>
              <td>
                Rp.
                <div style="float: right">
                  {{ formatNumber(payment.total) }}
                </div>
              </td>
            </tr>
            <tr v-if="payment.cash_amount">
              <td>Tunai</td>
              <td>:</td>
              <td>
                Rp.
                <div style="float: right">
                  {{ formatNumber(payment.cash_amount) }}
                </div>
              </td>
            </tr>
            <tr v-if="payment.wallet_amount">
              <td>Wallet</td>
              <td>:</td>
              <td>
                Rp.
                <div style="float: right">
                  {{ formatNumber(payment.wallet_amount) }}
                </div>
              </td>
            </tr>
            <tr v-if="payment.remaining_debt">
              <td>Piutang</td>
              <td>:</td>
              <td>
                Rp.
                <span style="float: right">{{
                  formatNumber(payment.remaining_debt)
                }}</span>
              </td>
            </tr>
            <tr>
              <td>Kembalian</td>
              <td>:</td>
              <td>
                Rp.
                <div style="float: right">
                  {{ formatNumber(payment.change) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </q-card-section>
      <q-card-actions align="center">
        <div class="q-gutter-sm">
          <q-btn
            autofocus
            label="Cetak Struk"
            icon="print"
            ref="printButtonRef"
            color="green"
          />
          <q-btn label="Transaksi Baru" icon="edit" color="primary" />
        </div>
        <div class="q-my-md">
          <q-btn label="Kembali ke daftar transaksi" icon="arrow_back" />
        </div>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<style scoped>
.info-table {
  border-collapse: collapse;
}
.info-table td,
.info-table th {
  border: 1px solid #aaa;
  padding: 0 8px;
}
</style>
