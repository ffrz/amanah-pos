<script setup>
import {
  formatDateTime,
  formatNumber,
  formatNumberWithSymbol,
} from "@/helpers/formatter";
import { ref } from "vue";
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
      route("admin.purchase-order.add-payment"),
      postData
    );

    $q.notify({
      message: "Pembayaran berhasil diproses!",
      color: "positive",
      icon: "check_circle",
    });
    showPaymentDialog.value = false;

    router.visit(
      route("admin.purchase-order.detail", { id: props.data.id }) +
        "?tab=payment"
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
        route("admin.purchase-order.delete-payment", { id: payment.id })
      );

      $q.notify({
        message: "Pembayaran berhasil dihapus!",
        color: "positive",
        icon: "check_circle",
      });

      router.visit(
        route("admin.purchase-order.detail", { id: props.data.id }) +
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
          v-if="$can('admin.purchase-order.add-payment')"
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
          <tr v-for="item in props.data.payments" :key="item.id">
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
                <i-link
                  class="text-caption"
                  :href="
                    route('admin.finance-account.detail', {
                      id: item.account.id,
                    })
                  "
                >
                  {{ item.account.name }}
                </i-link>
              </div>
              <div class="q-mt-xs">
                <q-badge
                  :label="$CONSTANTS.PURCHASE_ORDER_PAYMENT_TYPES[item.type]"
                  color="secondary"
                  class="q-py-xs"
                />
                <q-badge
                  :label="item.amount > 0 ? 'Pembayaran' : 'Refund'"
                  :color="item.amount > 0 ? 'green' : 'red'"
                  class="q-py-xs q-ml-sm"
                />
              </div>
            </td>
            <td
              class="text-right q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              {{ formatNumberWithSymbol(item.amount) }}
            </td>
            <td
              class="text-right q-pa-sm"
              style="border-bottom: 1px solid #eee"
            >
              <q-btn
                v-if="
                  $can('admin.purchase-order.delete-payment') &&
                  props.data.supplier_id &&
                  item.amount > 0
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
            <th class="text-right q-pa-sm" colspan="1">Saldo</th>
            <th class="text-right q-pa-sm">
              {{ formatNumberWithSymbol(props.data.balance) }}
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
