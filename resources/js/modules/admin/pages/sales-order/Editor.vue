<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { useQuasar } from "quasar";
import axios from "axios";

import TransactionHeader from "./editor/TransactionHeader.vue";
import ItemListTable from "./editor/ItemsListTable.vue";
import TransactionSummary from "./editor/TransactionSummary.vue";
import CustomerEditorDialog from "./editor/CustomerEditorDialog.vue";
import PaymentDialog from "./editor/PaymentDialog.vue";
import ConfirmDeleteDialog from "./editor/ConfirmDeleteDialog.vue";
import ProductBrowserDialog from "@/components/ProductBrowserDialog.vue";
import CheckBox from "@/components/CheckBox.vue";
import ItemEditorDialog from "./editor/ItemEditorDialog.vue";

const title = "Penjualan";
const $q = useQuasar();
const page = usePage();
const mergeItem = ref(true);
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
const userInput = ref("");
const isProcessing = ref(false);
const showDeleteDialog = ref(false);
const itemToDelete = ref(null);
const showCustomerEditor = ref(false);
const showPaymentDialog = ref(false);
const showProductBrowserDialog = ref(false);
const showItemEditorDialog = ref(false);
const itemToEdit = ref(null);

const handleProductSelection = (product) => {
  userInput.value += product.name;
};

const subtotal = computed(() => {
  const total = form.items.reduce((sum, item) => {
    return sum + item.price * item.quantity;
  }, 0);
  form.subtotal = total;
  form.total = total + form.tax;
  return total;
});

const notify = (msg, color = "info", pos = "top", icon = null) => {
  $q.notify({
    message: msg,
    color: color,
    position: pos,
    icon: icon,
  });
};

const showWarning = (msg) => {
  notify(msg, "warning");
};

const validateQuantity = (qty) => {
  if (isNaN(qty) || qty <= 0) {
    showWarning("Kuantitas tidak valid.");
    return false;
  }

  return true;
};

const validatePrice = (price) => {
  if (isNaN(price) || price < 0) {
    showWarning("Harga tidak valid.");
    return false;
  }

  return true;
};

const validateBarcode = (code) => {
  if (!code || code.length == 0) {
    showWarning("Barcode tidak valid.");
    return false;
  }

  return true;
};

const addItem = async () => {
  const input = userInput.value.trim();
  if (input.length === 0) {
    showProductBrowserDialog.value = true;
    return;
  }

  const parts = input.split("*");

  let inputQuantity = 1;
  let inputBarcode = "";
  let inputPrice = null;

  if (parts.length === 1) {
    inputBarcode = parts[0];
  } else if (parts.length <= 3) {
    inputQuantity = parseInt(parts[0]);
    inputBarcode = parts[1];
    if (parts.length === 3) {
      inputPrice = parseFloat(parts[2]);
    }
  } else {
    showWarning("Input tidak valid.");
    return;
  }

  if (inputBarcode.length === 0) {
    showProductBrowserDialog.value = true;
    return;
  }

  if (
    !validateBarcode(inputBarcode) &&
    !validateQuantity(inputQuantity) &&
    !validatePrice(inputPrice)
  ) {
    return;
  }

  isProcessing.value = true;
  await axios
    .post(route("admin.sales-order.add-item"), {
      order_id: form.id,
      product_code: inputBarcode,
      qty: inputQuantity,
      price: inputPrice,
      merge: mergeItem.value,
    })
    .then((response) => {
      const currentItem = response.data.data.item;

      let existingItemIndex = -1;
      if (response.data.data.mergeItem) {
        existingItemIndex = form.items.findIndex(
          (item) => item.id === currentItem.id
        );
      }

      if (existingItemIndex === -1) {
        form.items.push(currentItem);
      } else {
        form.items[existingItemIndex] = currentItem;
      }

      userInput.value = "";
      if (inputPrice !== null && inputPrice !== parseFloat(currentItem.price)) {
        showWarning("Harga tidak dapat diubah!");
      }
    })
    .catch((error) => {
      notify(error.response?.data?.message, "negative");
      console.error("Gagal mengambil data produk:", error);
    });

  isProcessing.value = false;
  transactionSummaryRef.value?.focusOnBarcodeInput();
};

const editItem = (item) => {
  itemToEdit.value = Object.create(item);
  showItemEditorDialog.value = true;
};

const confirmRemoveItem = (item) => {
  $q.dialog({
    title: "Hapus Item",
    message: `Apakah Anda yakin akan menghapus item${item.product_barcode}?`,
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
    isProcessing.value = true;
    axios
      .post(route("admin.sales-order.remove-item"), { id: item.id })
      .then(() => {
        const index = form.items.findIndex((data) => data.id === item.id);
        if (index !== -1) {
          form.items.splice(index, 1)[0];
          $q.notify({
            message: `Item no ${index + 1} telah dihapus.`,
            color: "info",
            position: "top",
          });
        }
      })
      .catch((error) => {
        $q.notify({
          message: `Gagal menghapus item.`,
          color: "negative",
          position: "top",
        });
        console.error(error);
      })
      .finally(() => {
        isProcessing.value = false;
      });
  });
};

const handleUpdate = () => {};
// const updateQuantity = (id, newQuantity) => {
//   const item = form.items.find((item) => item.id === id);
//   if (item && newQuantity > 0) {
//     item.quantity = parseInt(newQuantity) || 1;
//   }
// };
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
              @edit-item="editItem"
            />
          </div>
        </div>

        <div class="row">
          <div v-if="false" class="col-xs-12 col-md-8 q-pa-sm">
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
              v-model:barcode="userInput"
              :subtotal="subtotal"
              :item-count="form.items.length"
              :is-processing="isProcessing"
              :form-processing="form.processing"
              @add-item="addItem(userInput)"
              @process-payment="processPayment"
              :is-product-browser-open="showProductBrowserDialog"
            />
            <CheckBox v-model="mergeItem" label="Gabungkan item" />
          </div>
        </div>
      </q-card>

      <ConfirmDeleteDialog
        v-model="showDeleteDialog"
        :item="itemToDelete"
        @confirm="removeItem"
      />

      <ItemEditorDialog
        v-model="showItemEditorDialog"
        :item="itemToEdit"
        @save="handleUpdate"
      />

      <CustomerEditorDialog v-model="showCustomerEditor" />
      <PaymentDialog v-model="showPaymentDialog" />
      <ProductBrowserDialog
        v-model="showProductBrowserDialog"
        @product-selected="handleProductSelection"
      />
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
