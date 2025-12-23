<script setup>
import { formatNumber } from "@/helpers/formatter";
import { ref, computed, nextTick, reactive, onMounted, onUnmounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { QSelect, QBtnToggle } from "quasar";
import DatePicker from "@/components/DatePicker.vue";
import { date as QuasarDate } from "quasar";

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  form: { type: Object, required: true },
  customer: { type: Object, required: false },
  total: { type: Number, required: true },
});

const emit = defineEmits(["update:modelValue", "accepted"]);

const page = usePage();
const isProcessing = ref(false);
const debtDueDate = ref(new Date());
const nextAction = ref(page.props.settings.after_payment_action ?? "print");
const showOtherPayments = ref(false);
const paymentInputRefs = ref([]);

const payments = reactive([]);
const defaultMode = computed(
  () => page.props.settings.default_payment_mode || "cash"
);

const nextActionOptions = [
  { value: "print", label: "Cetak" },
  { value: "detail", label: "Rincian" },
  { value: "new-order", label: "Penjualan Baru" },
];

const totalPayment = computed(() => {
  return payments.reduce((sum, p) => sum + (p.amount || 0.0), 0.0);
});

const remainingTotal = computed(() => props.total - totalPayment.value);
const isDebtNeeded = computed(() => remainingTotal.value > 0);

const isCreditLimitExceeded = computed(() => {
  if (!props.customer || !isDebtNeeded.value) return false;
  if (parseInt(page.props.settings.allow_credit_limit)) return false;
  const currentBalance = Math.abs(parseFloat(props.customer.balance)) || 0;
  return (
    currentBalance + remainingTotal.value > (props.customer.credit_limit || 0)
  );
});

const isWalletAmountValid = computed(() => {
  if (!props.customer) return true;
  const wallet = payments.find((p) => p.id === "wallet");
  return wallet ? wallet.amount <= props.customer.wallet_balance : true;
});

// Logika Smart Visibility
const isPaymentVisible = (payment, index) => {
  // Selalu tampilkan yang pertama (default), jika menu dibuka, atau jika ada isinya
  return index === 0 || showOtherPayments.value || payment.amount > 0;
};

const isValid = computed(() => {
  const walletValid = isWalletAmountValid.value;
  const noNegative = payments.every((p) => (p.amount || 0) >= 0);
  if (isDebtNeeded.value) {
    if (!props.customer || isCreditLimitExceeded.value) return false;
  }
  return walletValid && noNegative;
});

const handleFinalizePayment = () => {
  if (!isValid.value) return;
  isProcessing.value = true;
  const activePayments = payments
    .filter((p) => p.amount > 0)
    .map((p) => ({ id: p.id, amount: p.amount }));

  emit("accepted", {
    id: props.form.id,
    total: props.total,
    is_debt: remainingTotal.value > 0,
    after_payment_action: nextAction.value,
    payments: activePayments,
    remaining_debt: remainingTotal.value > 0 ? remainingTotal.value : 0,
    change: remainingTotal.value < 0 ? Math.abs(remainingTotal.value) : 0,
    due_date: isDebtNeeded.value
      ? QuasarDate.formatDate(debtDueDate.value, "YYYY-MM-DD")
      : null,
  });
  isProcessing.value = false;
};

const onShow = () => {
  showOtherPayments.value = false;
  debtDueDate.value = new Date();

  // 1. Kumpulkan semua opsi
  let list = [{ id: "cash", label: "Laci Kasir (Tunai)", amount: 0 }];
  if (props.customer) {
    list.push({
      id: "wallet",
      label: `Wallet (Sisa: ${formatNumber(props.customer.wallet_balance)})`,
      amount: 0,
    });
  }
  page.props.accounts.forEach((acc) => {
    list.push({ id: acc.id, label: acc.name, amount: 0 });
  });

  // 2. Re-order berdasarkan Default Payment Mode
  const defaultIdx = list.findIndex((item) => item.id === defaultMode.value);
  if (defaultIdx > -1) {
    const defaultItem = list.splice(defaultIdx, 1)[0];
    list.unshift(defaultItem); // Pindah ke paling atas
  }

  // 3. Set nilai awal pada item teratas
  list[0].amount = props.total;
  payments.splice(0, payments.length, ...list);

  nextTick(() => {
    if (paymentInputRefs.value[0]) {
      paymentInputRefs.value[0].focus();
      paymentInputRefs.value[0].select();
    }
  });
};

const handleKeyDown = (e) => {
  if (props.modelValue && e.key === "Escape") emit("update:modelValue", false);
};
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    @show="onShow"
  >
    <q-card style="min-width: 380px" @keyDown="handleKeyDown">
      <q-card-section class="q-pb-none text-center">
        <div class="text-subtitle2 text-bold">Pembayaran</div>
        <div v-if="customer" class="q-mt-xs">
          <div class="text-bold text-primary" style="font-size: 0.8rem">
            {{ customer.name }}
          </div>
          <div class="text-caption text-grey-7" style="font-size: 0.7rem">
            Hutang: Rp {{ formatNumber(customer.balance) }}
          </div>
        </div>
      </q-card-section>

      <q-card-section>
        <div
          class="bg-primary text-white q-pa-xs rounded-borders text-center q-mb-md"
        >
          <div class="text-h6 text-bold">Rp {{ formatNumber(total) }}</div>
        </div>

        <div class="q-gutter-y-xs">
          <template v-for="(payment, index) in payments" :key="payment.id">
            <LocaleNumberInput
              v-show="isPaymentVisible(payment, index)"
              v-model="payment.amount"
              :label="payment.label"
              dense
              outlined
              :disable="isProcessing"
              :error="payment.id === 'wallet' && !isWalletAmountValid"
              hide-bottom-space
              :ref="(el) => (paymentInputRefs[index] = el)"
            />
          </template>

          <div class="text-center q-py-xs">
            <q-btn
              flat
              dense
              no-caps
              color="grey-7"
              size="sm"
              :icon="showOtherPayments ? 'expand_less' : 'expand_more'"
              :label="
                showOtherPayments ? 'Sembunyikan Opsi' : 'Metode Bayar Lainnya'
              "
              @click="showOtherPayments = !showOtherPayments"
            />
          </div>
        </div>

        <div class="q-mt-md text-center">
          <div v-if="remainingTotal > 0" class="text-negative text-bold">
            Sisa Kurang: Rp {{ formatNumber(remainingTotal) }}
          </div>
          <div v-else-if="remainingTotal < 0" class="text-positive text-bold">
            Kembali: Rp {{ formatNumber(Math.abs(remainingTotal)) }}
          </div>
          <div v-else class="text-positive text-bold">LUNAS</div>
        </div>

        <div
          v-if="isCreditLimitExceeded"
          class="q-pa-xs q-mt-sm bg-red-1 text-red-9 rounded-borders text-center text-caption"
        >
          <q-icon name="warning" /> <strong>Limit Terlampaui!</strong>
        </div>

        <q-slide-transition>
          <div v-if="isDebtNeeded && customer" class="q-mt-sm">
            <DatePicker
              outlined
              dense
              v-model="debtDueDate"
              label="Jatuh Tempo Hutang"
              :min-date="today"
            />
          </div>
        </q-slide-transition>
      </q-card-section>

      <q-card-actions align="center" class="q-px-md q-pb-md">
        <div class="row full-width q-col-gutter-sm">
          <div class="col-4">
            <q-btn flat label="Batal" v-close-popup class="full-width" />
          </div>
          <div class="col-8">
            <q-btn
              label="Proses (Ctrl+Enter)"
              color="primary"
              unelevated
              @click="handleFinalizePayment"
              :disable="isProcessing || !isValid"
              :loading="isProcessing"
              class="full-width"
            />
          </div>
        </div>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
