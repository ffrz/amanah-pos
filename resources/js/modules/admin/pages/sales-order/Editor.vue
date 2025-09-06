<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed, nextTick } from "vue";
import { useQuasar } from "quasar";
import TransactionHeader from "./editor/TransactionHeader.vue";
import ItemListTable from "./editor/ItemsListTable.vue";
import TransactionSummary from "./editor/TransactionSummary.vue";
import CustomerEditorDialog from "./editor/CustomerEditorDialog.vue";
import PaymentDialog from "./editor/PaymentDialog.vue";
import ConfirmDeleteDialog from "./editor/ConfirmDeleteDialog.vue";
import { handleSubmit } from "@/helpers/client-req-handler";
import { formatDateTimeForEditing, formatNumber } from "@/helpers/formatter";
import axios from "axios";

const title = "Penjualan";
const $q = useQuasar();
const page = usePage();

const transactionSummaryRef = ref(null);

const form = useForm({
  id: page.props.data.id,
  formatted_id: page.props.data.formatted_id,
  customer_id: page.props.data.customer_id,
  datetime: page.props.data.datetime,
  status: page.props.data.status,
  payment_status: page.props.data.payment_status,
  delivery_status: page.props.data.delivery_status,
  subtotal: page.props.data.total_price,
  tax: page.props.data.tax,
  total: page.props.data.total,
  notes: page.props.data.notes,
  items: page.props.data.details ?? [],
});

// State untuk kasir
const barcode = ref("");
const isProcessing = ref(false);
const showDeleteDialog = ref(false);
const itemToDelete = ref(null);
const showCustomerEditor = ref(false);
const showPaymentDialog = ref(false);

const subtotal = computed(() => {
  const total = form.items.reduce((sum, item) => {
    return sum + item.price * item.quantity;
  }, 0);
  form.subtotal = total;
  form.total = total + form.tax;
  return total;
});

const addItem = () => {
  if (!barcode.value.trim()) {
    $q.notify({
      message: "Silakan masukkan barcode",
      color: "warning",
      position: "top",
    });
    return;
  }

  let inputQuantity = 1;
  let inputBarcode = barcode.value.trim();
  let inputPrice = null;

  const parts = inputBarcode.split("*");

  if (parts.length === 3) {
    const parsedQty = parseInt(parts[0]);
    const parsedPrice = parseFloat(parts[2]);

    if (!isNaN(parsedQty) && parsedQty > 0) {
      inputQuantity = parsedQty;
    } else {
      $q.notify({
        message: "Kuantitas tidak valid. Menggunakan default 1.",
        color: "warning",
        position: "top",
      });
    }

    if (!isNaN(parsedPrice) && parsedPrice >= 0) {
      inputPrice = parsedPrice;
    } else {
      $q.notify({
        message: "Harga tidak valid. Menggunakan harga produk default.",
        color: "warning",
        position: "top",
      });
    }

    inputBarcode = parts[1];
  } else if (parts.length === 2) {
    const parsedQty = parseInt(parts[0]);
    if (!isNaN(parsedQty) && parsedQty > 0) {
      inputQuantity = parsedQty;
    } else {
      $q.notify({
        message: "Kuantitas tidak valid. Menggunakan default 1.",
        color: "warning",
        position: "top",
      });
    }
    inputBarcode = parts[1];
  }

  // TODO: harus ada checkbox untuk add atau tambah item baru
  // const existingItemIndex = form.items.findIndex(
  //   (item) => item.barcode === inputBarcode
  // );

  // if (existingItemIndex !== -1) {
  //   form.items[existingItemIndex].quantity += inputQuantity;
  //   $q.notify({
  //     message: `Quantity ${form.items[existingItemIndex].name} bertambah ${inputQuantity}`,
  //     color: "positive",
  //     position: "top",
  //   });
  // } else {
  isProcessing.value = true;
  axios
    .post(route("admin.sales-order.add-item"), {
      order_id: form.id,
      product_code: inputBarcode,
      qty: inputQuantity,
      price: inputPrice,
    })
    .then((response) => {
      form.items.push(response.data);
    })
    .catch((error) => {
      console.error("Gagal mengambil data produk:", error);
      update(() => {
        options.value = [];
      });
    })
    .finally(() => {
      isProcessing.value = false;
    });

  barcode.value = "";
  transactionSummaryRef.value?.focusOnBarcodeInput();
};

const confirmRemoveItem = (item) => {
  $q.dialog({
    title: "Hapus Item",
    message: `Apakah Anda yakin akan menghapus item${item.barcode}?`,
    cancel: {
      label: "Batal",
      color: "grey",
    },
    ok: {
      label: "Hapus",
      color: "negative",
    },
    persistent: true,
  }).onOk(() => {
    const index = form.items.findIndex((data) => data.id === item.id);
    if (index !== -1) {
      const removedItem = form.items.splice(index, 1)[0];
      $q.notify({
        message: `${removedItem.name} dihapus dari keranjang`,
        color: "info",
        position: "top",
        icon: "remove_shopping_cart",
      });
    }
  });
};

const updateQuantity = (id, newQuantity) => {
  const item = form.items.find((item) => item.id === id);
  if (item && newQuantity > 0) {
    item.quantity = parseInt(newQuantity) || 1;
  }
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.sales-order.index'))"
        />
      </div>
    </template>
    <q-page class="bg-grey-2 q-pa-sm column fit">
      <q-card square flat bordered class="full-width col column">
        <TransactionHeader
          :user="page.props.auth.user"
          :company="page.props.company"
          @edit-customer="showCustomerEditor = true"
        />

        <div class="row col grow">
          <div class="col-12 q-pa-sm column">
            <ItemListTable
              :items="form.items"
              @update-quantity="({ id, value }) => updateQuantity(id, value)"
              @remove-item="confirmRemoveItem"
            />
          </div>
        </div>

        <div class="row">
          <div class="col-xs-12 col-md-8 q-pa-sm">
            <table>
              <tr>
                <td>#</td>
                <td>:</td>
                <td>{{ page.props.data.formatted_id }}</td>
              </tr>
              <tr>
                <td>Status</td>
                <td>:</td>
                <td>{{ page.props.data.status_label }}</td>
              </tr>
              <tr>
                <td>Pembayaran</td>
                <td>:</td>
                <td>{{ page.props.data.payment_status_label }}</td>
              </tr>
              <tr>
                <td>Pengiriman</td>
                <td>:</td>
                <td>{{ page.props.data.delivery_status_label }}</td>
              </tr>
              <tr>
                <td>Catatan</td>
                <td>:</td>
                <td>
                  <q-btn
                    color="grey"
                    flat
                    size="sm"
                    dense
                    rounded
                    small
                    icon="notes"
                  />
                </td>
              </tr>
            </table>
          </div>
          <div class="col-md-4 col-xs-12 q-pa-sm">
            <TransactionSummary
              ref="transactionSummaryRef"
              v-model:barcode="barcode"
              :subtotal="subtotal"
              :item-count="form.items.length"
              :is-processing="isProcessing"
              :form-processing="form.processing"
              @add-item="addItem(barcode)"
              @process-payment="processPayment"
            />
          </div>
        </div>
      </q-card>

      <ConfirmDeleteDialog
        v-model="showDeleteDialog"
        :item="itemToDelete"
        @confirm="removeItem"
      />

      <CustomerEditorDialog v-model="showCustomerEditor" />
      <PaymentDialog v-model="showPaymentDialog" />
    </q-page>
  </authenticated-layout>
</template>

<style scoped>
.pos-table .q-table__top,
.pos-table .q-table__bottom,
.pos-table thead tr:first-child th {
  background-color: #f5f5f5;
}

.hover-highlight:hover {
  background-color: #f8f9fa;
}

.q-table tbody td {
  padding: 0px 8px;
}

.q-table thead th {
  padding: 0px 8px;
  font-weight: 600;
}
</style>
