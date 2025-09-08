<script setup>
import { formatNumber } from "@/helpers/formatter";
import { ref, computed, nextTick, watch } from "vue";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  form: {
    type: Object,
    required: true,
  },
  customer: {
    type: Object,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "accepted"]);

// State untuk jumlah pembayaran tunai dan dompet
const cashAmount = ref(0);
const walletAmount = ref(0);

// Ref untuk input tunai agar bisa di-fokus
const cashInputRef = ref(null);

// Hitung total pembayaran
const totalPayment = computed(() => {
  return cashAmount.value + walletAmount.value;
});

// Hitung sisa saldo (atau kembalian jika lebih)
const remainingTotal = computed(() => {
  return props.total - totalPayment.value;
});

// Computed untuk validasi saldo dompet
const isWalletAmountValid = computed(() => {
  // Hanya validasi jika ada pelanggan dan jumlah dompet > 0
  if (!props.customer || walletAmount.value === 0) {
    return true;
  }
  return walletAmount.value <= props.customer.balance;
});

const handleFinalizePayment = () => {
  // PENTING: Lakukan validasi sebelum emit
  if (!isWalletAmountValid.value) {
    return;
  }

  const totalPaid = cashAmount.value + walletAmount.value;
  const change = totalPaid > props.total ? totalPaid - props.total : 0;

  // Emit event dengan rincian pembayaran
  emit("accepted", {
    total: props.total,
    cash_amount: cashAmount.value,
    wallet_amount: walletAmount.value,
    remaining_debt: remainingTotal.value > 0 ? remainingTotal.value : 0,
    change: change,
  });
};

const focusOnInput = () => {
  nextTick(() => {
    if (cashInputRef.value) {
      cashInputRef.value.focus();
      cashInputRef.value.select();
    }
  });
};

watch(cashAmount, (newValue) => {
  if (newValue >= props.total) {
    walletAmount.value = 0;
  }

  fixWalletAmount(walletAmount.value);
});

watch(walletAmount, (newValue) => {
  fixWalletAmount(newValue);
});

const fixWalletAmount = (newValue) => {
  const amountToPay = props.total - cashAmount.value;
  if (amountToPay < 0) {
    return;
  }

  if (newValue > amountToPay) {
    walletAmount.value = amountToPay;
  }
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    v-on:before-show="focusOnInput"
  >
    <q-card style="min-width: 350px">
      <q-card-section>
        <div class="text-subtitle1 text-bold text-center">
          Rincian Pembayaran
        </div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <div v-if="customer" class="text-center q-mb-md text-grey-8">
          <div class="text-subtitle2 text-weight-medium">
            Username: {{ customer.username }} <br />
            Nama: {{ customer.name }}<br />
            Saldo: Rp. {{ formatNumber(customer.balance) }}
          </div>
        </div>

        <div class="text-h5 text-center text-primary q-pb-md">
          Total: Rp. {{ formatNumber(total) }}
        </div>

        <div class="row q-col-gutter-sm justify-center">
          <div class="col-12">
            <LocaleNumberInput
              v-model="cashAmount"
              :label="'Jumlah Tunai'"
              :disable="form.processing"
              :outlined="true"
              ref="cashInputRef"
            />
          </div>

          <div class="col-12">
            <LocaleNumberInput
              v-model="walletAmount"
              :label="'Jumlah Dompet Pelanggan'"
              :disable="cashAmount >= total || form.processing"
              :outlined="true"
              :error="!isWalletAmountValid"
              :error-message="'Jumlah melebihi saldo dompet!'"
            />
          </div>
        </div>

        <div class="text-center">
          <div
            v-if="remainingTotal > 0"
            class="text-h6 text-negative text-weight-bold"
          >
            Sisa Tagihan: Rp. {{ formatNumber(remainingTotal) }}
          </div>
          <div
            v-else-if="remainingTotal < 0"
            class="text-h6 text-green-8 text-weight-bold"
          >
            Kembalian: Rp. {{ formatNumber(Math.abs(remainingTotal)) }}
          </div>
          <div v-else class="text-h6 text-positive text-weight-bold">
            Tagihan Terbayar
          </div>
        </div>
      </q-card-section>

      <q-card-actions align="center">
        <q-btn
          flat
          label="Batal"
          color="grey"
          @click="$emit('update:modelValue', false)"
        />
        <q-btn
          v-if="remainingTotal > 0"
          :label="remainingTotal > 0 ? 'Catat Utang' : 'Bayar'"
          :color="remainingTotal > 0 ? 'red' : 'positive'"
          @click="handleFinalizePayment"
          :disable="
            form.processing ||
            !isWalletAmountValid ||
            (remainingTotal > 0 && totalPayment === 0)
          "
          :loading="form.processing"
        />
        <q-btn
          v-else
          label="Bayar"
          color="positive"
          @click="handleFinalizePayment"
          :disable="form.processing || !isWalletAmountValid"
          :loading="form.processing"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
