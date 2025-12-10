<script setup>
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { computed, ref } from "vue";
import PaymentDialog from "./PaymentDialog.vue";
import { useQuasar } from "quasar";
import axios from "axios";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const props = defineProps({
  data: Object,
});

const $q = useQuasar();
const showPaymentDialog = ref(false);

const openPaymentDialog = () => {
  showPaymentDialog.value = true;
};
const remainingRefund = computed(() => {
  return props.data.remaining_refund;
});

const handleAcceptedPayment = async (payload) => {
  const postData = {
    order_id: props.data.id,
    ...payload,
  };

  $q.loading.show({
    message: "Memproses pembayaran...",
  });

  try {
    const response = await axios.post(
      route("admin.sales-order-return.add-refund"),
      postData
    );

    $q.notify({
      message: "Refund Pembayaran berhasil diproses!",
      color: "positive",
      icon: "check_circle",
    });
    showPaymentDialog.value = false;

    router.visit(
      route("admin.sales-order-return.detail", { id: props.data.id }) +
        "?tab=refund"
    );
  } catch (error) {
    let errorMessage = "Gagal memproses refund.";
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
    message: `Apakah Anda yakin ingin menghapus refund pembayaran ini?`,
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    $q.loading.show({
      message: "Menghapus pembayaran...",
    });

    try {
      await axios.post(
        route("admin.sales-order-return.delete-refund", { id: payment.id })
      );

      $q.notify({
        message: "Pembayaran berhasil dihapus!",
        color: "positive",
        icon: "check_circle",
      });

      router.visit(
        route("admin.sales-order-return.detail", { id: props.data.id }) +
          "?tab=refund"
      );
    } catch (error) {
      let errorMessage = "Gagal menghapus refund pembayaran.";
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
          v-if="$can('admin.sales-order-return.add-refund')"
          label="Tambah"
          color="primary"
          icon="add"
          dense
          class="custom-dense"
          size="sm"
          :disable="page.props.data.status != 'closed'"
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
          <tr v-for="item in props.data.refunds" :key="item.id">
            <td class="q-pa-sm" style="border-bottom: 1px solid #eee">
              <div class="row items-center q-gutter-x-sm">
                <q-icon name="tag" size="xs" color="grey-7" />
                <span class="text-weight-bold">{{ item.code }}</span>
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
                v-if="$can('admin.sales-order-return.delete-refund')"
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
          <tr v-if="props.data.refunds.length == 0">
            <td colspan="100%" class="text-center text-grey">Belum ada item</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th class="text-right q-pa-sm" colspan="1">Total Refund</th>
            <th class="text-right q-pa-sm">
              {{ formatNumber(parseFloat(-props.data.total_refunded)) }}
            </th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </q-card-section>
  </div>

  <PaymentDialog
    v-model="showPaymentDialog"
    :total="remainingRefund"
    :customer="props.data.customer"
    @accepted="handleAcceptedPayment"
  />
</template>
