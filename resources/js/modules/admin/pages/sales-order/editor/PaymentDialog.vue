<script setup>
import { formatNumber } from "@/helpers/formatter";
import { ref } from "vue";

defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "accepted"]);

const handlePayment = (method) => {
  emit("accepted", {
    method: method,
  });
};
</script>
<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    persistent
  >
    <q-card style="min-width: 350px">
      <q-card-section>
        <div class="text-h6 text-center">Pilih Metode Pembayaran</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <div v-if="studentName" class="text-center q-mb-md text-grey-8">
          <div class="text-subtitle1 text-weight-medium">
            {{ studentName }}
          </div>
          <div v-if="walletBalance !== null" class="text-body1">
            Saldo: Rp. {{ formatNumber(walletBalance) }}
          </div>
        </div>

        <div class="text-h4 text-center text-primary q-pb-md">
          Rp. {{ formatNumber(form.total) }}
        </div>
        <div class="row q-col-gutter-sm justify-center">
          <div class="col-12">
            <q-btn
              label="Tunai"
              icon="attach_money"
              color="primary"
              class="full-width"
              @click="handlePayment('cash')"
              :loading="form.processing"
              :disable="form.processing"
            />
          </div>
          <div class="col-12">
            <q-btn
              label="Dompet Pelanggan"
              icon="wallet"
              color="green-6"
              class="full-width"
              @click="handlePayment('wallet')"
              :loading="form.processing"
              :disable="form.processing"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-actions align="right" class="q-py-md">
        <q-btn
          flat
          label="Batal"
          color="grey"
          @click="showPaymentDialog = false"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
