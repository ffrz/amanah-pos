<script setup>
import { formatNumber } from "@/helpers/formatter";
import { ref, computed, nextTick, reactive } from "vue";
import { usePage } from "@inertiajs/vue3";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { QSelect, QBtnToggle } from "quasar";
import DatePicker from "@/components/DatePicker.vue";
import { date as QuasarDate } from "quasar";

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  form: {
    type: Object,
    required: true,
  },
  supplier: {
    type: Object,
    required: false,
  },
  total: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "accepted"]);

const page = usePage();
const isProcessing = ref(false);
const paymentMode = ref("payment");
const debtDueDate = ref(new Date());
const firstPaymentInputRef = ref(null);
const payments = reactive([]);

const arePaymentsValid = computed(() => {
  // Memastikan setiap pembayaran memiliki akun dan jumlah yang valid
  return payments.every(
    (payment) =>
      payment.id && typeof payment.amount === "number" && payment.amount > 0
  );
});

const paymentOptions = computed(() =>
  page.props.accounts.map((a) => ({
    label: a.name,
    value: a.id,
  }))
);

const totalPayment = computed(() => {
  return payments.reduce((sum, p) => sum + (p.amount || 0.0), 0.0);
});

const remainingTotal = computed(() => {
  return props.total - totalPayment.value;
});

const isAmountValid = computed(() => {
  if (!props.supplier) return true;
  const walletPayment = payments.find((p) => p.id === "wallet");
  if (!walletPayment) return true;
  return walletPayment.amount <= props.supplier.balance;
});

const today = new Date();
const futureDate = QuasarDate.addToDate(new Date(), { days: 30 });

const debtDateRules = computed(() => [
  (val) => !!val || "Tanggal jatuh tempo harus diisi",
  (val) => {
    if (
      !QuasarDate.isBetweenDates(val, today, futureDate, {
        inclusiveFrom: true,
        inclusiveTo: true,
      })
    ) {
      return `Tanggal tidak boleh kurang dari hari ini dan lebih dari 30 hari dari sekarang.`;
    }
    return true;
  },
]);

const isValid = computed(() => {
  if (paymentMode.value === "debt") {
    return (
      !!props.supplier &&
      debtDateRules.value.every((rule) => rule(debtDueDate.value) === true)
    );
  }

  const hasPaymentAmount = payments.some((p) => (p.amount || 0) > 0);
  const noNegativePayment = payments.every((p) => (p.amount || 0) >= 0);
  const isFullyPaid = remainingTotal.value <= 0;

  // Validasi pembayaran: setiap item harus punya akun & jumlah valid
  return (
    arePaymentsValid.value &&
    hasPaymentAmount &&
    noNegativePayment &&
    isFullyPaid
  );
});

const addPayment = () => {
  if (payments.length < 3) {
    payments.push({ id: null, amount: 0.0 });
  }
};

const removePayment = (id) => {
  if (payments.length > 1) {
    const index = payments.findIndex((p) => p.id === id);
    if (index !== -1) {
      payments.splice(index, 1);
    }
  }
};

const changePaymentMode = (mode) => {
  paymentMode.value = mode;
  debtDueDate.value = new Date();
  payments.splice(0, payments.length);

  if (mode === "payment") {
    if (paymentOptions.value.length > 0) {
      payments.splice(0, payments.length, {
        id: paymentOptions.value[0].value,
        amount: props.total,
      });
      nextTick(() => {
        if (
          firstPaymentInputRef.value &&
          firstPaymentInputRef.value.length > 0
        ) {
          firstPaymentInputRef.value[0].focus();
          firstPaymentInputRef.value[0].select();
        }
      });
    }
  }
};

const handleFinalizePayment = () => {
  if (!isValid.value) {
    return;
  }

  isProcessing.value = true;
  let payload = {
    total: props.total,
    is_debt: paymentMode.value === "debt",
  };

  if (paymentMode.value === "debt") {
    payload.due_date = QuasarDate.formatDate(debtDueDate.value, "YYYY-MM-DD");
    payload.payments = [];
  } else {
    const totalPaid = totalPayment.value;
    const change = totalPaid > props.total ? totalPaid - props.total : 0;
    const remainingDebt = remainingTotal.value > 0 ? remainingTotal.value : 0;
    payload.payments = payments;
    payload.remaining_debt = remainingDebt;
    payload.change = change;
  }

  emit("accepted", payload);
  isProcessing.value = false;
};

const onBeforeShow = () => {
  if (paymentOptions.value.length === 0) {
    // Menggunakan konsol.log sebagai pengganti alert, karena alert tidak muncul di lingkungan tertentu
    console.log("Tidak ada akun yang dapat digunakan untuk pembayaran!");
    return;
  }
  changePaymentMode("payment");
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
            Nama: {{ supplier.name }}<br />
            Utang / Piutang: Rp. {{ formatNumber(supplier.balance) }}
          </div>
        </div>
        <div class="text-h5 text-center text-primary q-pb-md">
          Total: Rp. {{ formatNumber(total) }}
        </div>
        <div class="q-mb-md flex flex-center">
          <q-btn-toggle
            v-model="paymentMode"
            @update:model-value="changePaymentMode"
            :options="[
              {
                label: 'Pembayaran',
                value: 'payment',
                slot: 'payment',
                color: 'green',
              },
              {
                label: 'Tempo',
                value: 'debt',
                slot: 'debt',
                disable: !supplier,
                color: 'red',
              },
            ]"
            flat
            spread
            class="bg-grey-2"
          />
        </div>
        <div v-if="paymentMode === 'payment'">
          <div
            v-for="(payment, index) in payments"
            :key="index"
            class="row q-col-gutter-sm q-mb-sm"
          >
            <div class="col-6">
              <q-select
                v-model="payment.id"
                :options="paymentOptions"
                label="Akun"
                :outlined="true"
                emit-value
                map-options
                dense
                :disable="isProcessing"
                hide-bottom-space
                :rules="[(val) => !!val || 'Akun harus dipilih']"
                :error="!payment.id"
                error-message="Akun tidak valid"
                class="custom-select"
              />
            </div>
            <div class="col-6">
              <LocaleNumberInput
                v-model="payment.amount"
                label="Jumlah"
                :outlined="true"
                dense
                :disable="isProcessing"
                :error="!isAmountValid"
                :error-message="!isAmountValid ? 'Jumlah Tidak valid!' : ''"
                hide-bottom-space
                :ref="index === 0 ? 'firstPaymentInputRef' : null"
              >
                <template v-slot:append v-if="payments.length > 1">
                  <q-icon
                    size="xs"
                    name="close"
                    class="cursor-pointer"
                    @click="removePayment(index)"
                  />
                </template>
              </LocaleNumberInput>
            </div>
          </div>
          <div class="row">
            <q-btn
              :disable="!(payments.length < 3 && remainingTotal > 0)"
              flat
              dense
              icon="add"
              label="Tambah Pembayaran"
              @click="addPayment"
              size="sm"
            />
          </div>
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
        </div>
        <div v-else-if="paymentMode === 'debt' && supplier">
          <DatePicker
            outlined
            v-model="debtDueDate"
            label="Tanggal Jatuh Tempo"
            hint="Pilih tanggal jatuh tempo pembayaran utang"
            class="q-mb-md"
            :min-date="today"
            :max-date="futureDate"
            :rules="debtDateRules"
          />
          <div class="text-h6 text-negative text-center">
            Total Utang: Rp. {{ formatNumber(total) }}
          </div>
        </div>
        <div v-else-if="paymentMode === 'debt' && !supplier">
          <div class="text-center text-negative text-h6">
            Tidak dapat mencatat utang tanpa memilih pelanggan.
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
          :label="paymentMode === 'debt' ? 'Catat Utang' : 'Bayar'"
          color="positive"
          @click="handleFinalizePayment"
          :disable="isProcessing || !isValid"
          :loading="isProcessing"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
