<script setup>
import { formatNumber } from "@/helpers/formatter";
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { QSelect } from "quasar";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "accepted"]);

const page = usePage();
const isProcessing = ref(false);

const paymentForm = ref({
  method: "cash",
  amount: props.total,
  notes: null,
});

const supplier = page.props.data.supplier;

const paymentOptions = computed(() => [
  { label: "Tunai (Laci Kasir)", value: "cash" },
  // Opsi wallet hanya tersedia jika ada data supplier
  ...(supplier ? [{ label: "Wallet", value: "wallet" }] : []),
  ...page.props.accounts.map((a) => ({
    label: a.name,
    value: a.id,
  })),
]);

const remainingTotal = computed(() => {
  return props.total - (paymentForm.value.amount || 0);
});

// Menghitung pesan error berdasarkan validasi
const errorMessage = computed(() => {
  const amount = paymentForm.value.amount || 0;
  const total = props.total;

  if (amount <= 0) {
    return "Jumlah pembayaran harus lebih dari 0.";
  }

  // boleh dicicil
  // if (amount < total) {
  //   return "Jumlah pembayaran tidak boleh kurang dari total tagihan.";
  // }

  if (
    paymentForm.value.method === "wallet" &&
    supplier &&
    amount > supplier.wallet_balance
  ) {
    return "Saldo wallet tidak mencukupi.";
  }
  return null;
});

// Mengecek apakah form valid
const isValid = computed(() => {
  return errorMessage.value === null;
});

const handleFinalizePayment = () => {
  if (!isValid.value) {
    return;
  }

  isProcessing.value = true;

  // Asumsikan kita hanya memiliki satu pembayaran tunggal
  const payload = {
    total_paid: paymentForm.value.amount,
    payments: [
      {
        id: paymentForm.value.method,
        amount: paymentForm.value.amount,
      },
    ],
    notes: paymentForm.value.notes,
    change: Math.max(0, (paymentForm.value.amount || 0) - props.total),
    remaining_debt: Math.max(0, props.total - (paymentForm.value.amount || 0)),
  };

  emit("accepted", payload);
  isProcessing.value = false;
};

const onBeforeShow = () => {
  paymentForm.value.amount = props.total;
  paymentForm.value.method = "cash";
  paymentForm.value.notes = null;
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    @before-show="onBeforeShow"
  >
    <q-card style="min-width: 300px">
      <q-card-section>
        <div class="text-subtitle1 text-bold text-center">
          Rincian Pembayaran
        </div>
      </q-card-section>
      <q-card-section class="q-pt-none">
        <div v-if="supplier" class="text-center q-mb-md text-grey-8">
          <div class="text-subtitle2 text-weight-medium">
            <div>
              <my-link
                :href="route('admin.supplier.detail', { id: supplier.id })"
                target="_blank"
              >
                {{ supplier.code }} - {{ supplier.name }}
              </my-link>
            </div>
            <div
              :class="supplier.wallet_balance > 0 ? 'text-green' : 'text-red'"
            >
              Saldo Wallet: Rp. {{ formatNumber(supplier.wallet_balance) }}
            </div>
            <div :class="supplier.balance > 0 ? 'text-green' : 'text-red'">
              {{ supplier.balance > 0 ? "Piutang" : "Utang" }}:
              {{ formatNumber(supplier.balance) }}
            </div>
          </div>
        </div>
        <div class="text-h5 text-center text-primary q-pb-md">
          Total: Rp. {{ formatNumber(total) }}
        </div>
        <q-select
          v-model="paymentForm.method"
          :options="paymentOptions"
          label="Metode Pembayaran"
          :outlined="true"
          emit-value
          map-options
          dense
          class="q-mb-md"
          :disable="isProcessing"
        />
        <LocaleNumberInput
          v-model="paymentForm.amount"
          label="Jumlah Uang"
          :outlined="true"
          dense
          :disable="isProcessing"
          class="q-mb-md"
          :error="!isValid"
          :error-message="errorMessage"
        />
        <q-input
          v-model="paymentForm.notes"
          label="Catatan"
          :outlined="true"
          type="textarea"
          rows="2"
          :disable="isProcessing"
          class="q-mb-md"
        />
        <div class="text-center q-mt-md">
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
      <q-card-actions align="center" class="q-mb-sm">
        <q-btn
          flat
          label="Batal"
          color="grey"
          @click="$emit('update:modelValue', false)"
        />
        <q-btn
          label="Bayar"
          color="positive"
          @click="handleFinalizePayment"
          :disable="isProcessing || !isValid"
          :loading="isProcessing"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
