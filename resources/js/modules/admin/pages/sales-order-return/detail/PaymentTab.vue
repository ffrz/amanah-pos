<script setup>
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { computed, ref } from "vue";
import PaymentDialog from "./PaymentDialog.vue";
import { useQuasar } from "quasar";
import axios from "axios";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  data: Object,
});

const remainingPayment = computed(() => {
  return props.data.grand_total - props.data.total_paid;
});

const $q = useQuasar();
const showPaymentDialog = ref(false);

const openPaymentDialog = () => {
  showPaymentDialog.value = true;
};
const handleAcceptedPayment = async (payload) => {
  const postData = {
    order_id: props.data.id,
    payments: payload.payments,
    notes: payload.notes,
  };

  $q.loading.show({
    message: "Memproses pembayaran...",
  });

  try {
    const response = await axios.post(
      route("admin.sales-order.add-payment"),
      postData
    );

    $q.notify({
      message: "Pembayaran berhasil diproses!",
      color: "positive",
      icon: "check_circle",
    });
    showPaymentDialog.value = false;

    router.visit(
      route("admin.sales-order.detail", { id: props.data.id }) + "?tab=payment"
    );
  } catch (error) {
    let errorMessage = "Gagal memproses pembayaran.";
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }

    $q.notify({
      message: errorMessage,
      color: "negative",
      icon: "error",
    });
  } finally {
    $q.loading.hide();
  }
};

const deletePayment = (payment) => {
  $q.dialog({
    title: "Konfirmasi",
    message: `Apakah Anda yakin ingin menghapus pembayaran ini?`,
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    $q.loading.show({
      message: "Menghapus pembayaran...",
    });

    try {
      await axios.post(
        route("admin.sales-order.delete-payment", { id: payment.id })
      );

      $q.notify({
        message: "Pembayaran berhasil dihapus!",
        color: "positive",
        icon: "check_circle",
      });

      router.visit(
        route("admin.sales-order.detail", { id: props.data.id }) +
          "?tab=payment"
      );
    } catch (error) {
      let errorMessage = "Gagal menghapus pembayaran.";
      if (
        error.response &&
        error.response.data &&
        error.response.data.message
      ) {
        errorMessage = error.response.data.message;
      } else if (error.message) {
        errorMessage = error.message;
      }

      $q.notify({
        message: errorMessage,
        color: "negative",
        icon: "error",
      });
    } finally {
      $q.loading.hide();
    }
  });
};
</script>

<template>
  <div class="column">
    <q-separator />
    <q-card-section class="q-pa-sm">
      <div class="row">
        <q-btn
          v-if="$can('admin.sales-order.add-payment') && remainingPayment > 0"
          label="Tambah"
          color="primary"
          icon="add"
          dense
          class="custom-dense"
          size="sm"
          @click.stop="openPaymentDialog()"
        />
      </div>
      <table class="full-width" style="border-collapse: collapse">
        <thead class="text-grey-8">
          <tr>
            <th class="text-left q-pa-sm" style="border-bottom: 2px solid #ddd">
              Detail Transaksi
            </th>
            <th
              class="text-right q-pa-sm"
              style="border-bottom: 2px solid #ddd"
            >
              Jumlah (Rp)
            </th>
            <th
              class="text-center q-pa-sm"
              style="border-bottom: 2px solid #ddd"
            ></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in props.data.payments" :key="item.id">
            <td class="q-pa-sm" style="border-bottom: 1px solid #eee">
              <div class="row items-center q-gutter-x-sm">
                <q-icon name="tag" size="xs" color="grey-7" />
                <span class="text-weight-bold">{{ item.formatted_id }}</span>
              </div>
              <div class="row items-center q-gutter-x-sm q-mt-xs">
                <q-icon name="calendar_today" size="xs" color="grey-7" />
                <span class="text-caption text-grey-6">{{
                  formatDateTime(item.created_at)
                }}</span>
              </div>
              <div
                v-if="
                  (item.type === 'transfer' || item.type === 'cash') &&
                  item.account
                "
                class="row items-center q-gutter-x-sm q-mt-xs"
              >
                <q-icon name="wallet" size="xs" color="grey-7" />
                <span class="text-caption text-grey-6">{{
                  item.account.name
                }}</span>
              </div>
              <div class="q-mt-xs">
                <q-badge
                  :label="$CONSTANTS.SALES_ORDER_PAYMENT_TYPES[item.type]"
                  color="secondary"
                  class="q-py-xs"
                />
              </div>
            </td>
            <td
              class="text-right q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              {{ formatNumber(item.amount) }}
            </td>
            <td
              class="text-right q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              <q-btn
                v-if="
                  $can('admin.sales-order.delete-payment') &&
                  props.data.customer_id
                "
                icon="delete"
                color="negative"
                dense
                flat
                rounded
                size="sm"
                @click="deletePayment(item)"
              />
            </td>
          </tr>
          <tr v-if="props.data.payments.length == 0">
            <td colspan="100%" class="text-center text-grey">Belum ada item</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th class="text-right q-pa-sm" colspan="1">Total Dibayar</th>
            <th class="text-right q-pa-sm">
              {{ formatNumber(props.data.total_paid) }}
            </th>
            <th></th>
          </tr>
          <tr>
            <th class="text-right q-pa-sm" colspan="1">Sisa Tagihan</th>
            <th class="text-right q-pa-sm">
              {{ formatNumber(remainingPayment) }}
            </th>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </q-card-section>
  </div>

  <PaymentDialog
    v-model="showPaymentDialog"
    :total="remainingPayment"
    :customer="props.data.customer"
    @accepted="handleAcceptedPayment"
  />
</template>
