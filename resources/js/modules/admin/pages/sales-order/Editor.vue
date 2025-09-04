<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";

import { ref, computed, nextTick } from "vue";
import { useQuasar } from "quasar";
import dayjs from "dayjs";

import TransactionHeader from "./editor/TransactionHeader.vue";
import ItemListTable from "./editor/ItemsListTable.vue";
import TransactionSummary from "./editor/TransactionSummary.vue";
import ConfirmDeleteDialog from "./editor/ConfirmDeleteDialog.vue";
import CustomerEditorDialog from "./editor/CustomerEditorDialog.vue";
import { formatNumber } from "@/helpers/formatter";

const title = "Penjualan";
const $q = useQuasar();
const page = usePage();

const transactionSummaryRef = ref(null);

const form = useForm({
  id: null,
  formatted_id: "Diisi Otomatis",
  customer_name: "Pelanggan Umum",
  datetime: dayjs().format("YYYY-MM-DD HH:mm:ss"),
  status: "pending",
  payment_status: "unpaid",
  items: [],
  subtotal: 0,
  tax: 0,
  total: 0,
  notes: "",
});

// State untuk kasir
const barcode = ref("");
const isProcessing = ref(false);
const showDeleteDialog = ref(false);
const itemToDelete = ref(null);
const showCustomerEditor = ref(false);
const showPaymentDialog = ref(false);

// Tambahkan state baru untuk data santri
const studentName = ref(null);
const walletBalance = ref(null);

const tableColumns = computed(() => {
  if ($q.screen.gt.sm) {
    return [
      {
        name: "name",
        required: true,
        label: "Item Information",
        align: "left",
        field: "name",
        sortable: false,
        style: "width: 300px",
      },
      {
        name: "subtotal",
        label: "Subtotal",
        align: "right",
        field: "subtotal",
        sortable: false,
        style: "width: 120px",
      },

      {
        name: "action",
        label: "Action",
        align: "center",
        sortable: false,
        style: "width: 80px",
      },
    ];
  } else {
    return [
      {
        name: "name",
        required: true,
        label: "Item",
        align: "left",
        field: "name",
        sortable: false,
      },
      {
        name: "subtotal",
        label: "Total",
        align: "right",
        sortable: false,
      },
      {
        name: "action",
        label: "Aksi",
        align: "center",
        sortable: false,
      },
    ];
  }
});

const subtotal = computed(() => {
  const total = form.items.reduce((sum, item) => {
    return sum + item.price * item.quantity;
  }, 0);
  form.subtotal = total;
  form.total = total + form.tax;
  return total;
});

const addItem = async () => {
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

  const existingItemIndex = form.items.findIndex(
    (item) => item.barcode === inputBarcode
  );

  if (existingItemIndex !== -1) {
    form.items[existingItemIndex].quantity += inputQuantity;
    $q.notify({
      message: `Quantity ${form.items[existingItemIndex].name} bertambah ${inputQuantity}`,
      color: "positive",
      position: "top",
    });
  } else {
    isProcessing.value = true;
    try {
      await new Promise((resolve) => setTimeout(resolve, 300));
      const fetchedProduct = {
        id: Date.now(),
        name: `Item ${inputBarcode}`,
        barcode: inputBarcode,
        price:
          inputPrice !== null
            ? inputPrice
            : Math.floor(Math.random() * 50000) + 10000,
      };
      const newItem = {
        ...fetchedProduct,
        quantity: inputQuantity,
      };
      form.items.push(newItem);

      $q.notify({
        message: `${newItem.name} berhasil ditambahkan`,
        color: "positive",
        position: "top",
        icon: "add_shopping_cart",
      });
    } catch (error) {
      console.error("Error fetching item:", error);
      $q.notify({
        message: "Item tidak ditemukan atau gagal menambahkan item",
        color: "negative",
        position: "top",
      });
    } finally {
      isProcessing.value = false;
    }
  }

  barcode.value = "";
  await nextTick();
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

const processPayment = async () => {
  if (form.items.length === 0) {
    $q.notify({
      message: "Tidak ada item dalam keranjang",
      color: "warning",
      position: "top",
    });
    return;
  }
  await fetchStudentData(); // Panggil fungsi untuk mengambil data santri
  showPaymentDialog.value = true;
};

const fetchStudentData = async () => {
  // Simulasi pengambilan data santri dari backend
  // Dalam implementasi nyata, Anda akan menggunakan ID pelanggan untuk mencari data
  try {
    // Misalnya, ambil data dari API:
    // const response = await api.get(`/students/${form.customer_id}`);
    // studentName.value = response.data.name;
    // walletBalance.value = response.data.wallet_balance;

    // Untuk demo, kita gunakan data dummy:
    studentName.value =
      form.customer_name === "Pelanggan Umum"
        ? "Pelanggan Umum"
        : "Nama Santri Demo";
    walletBalance.value = Math.floor(Math.random() * 500000) + 10000;
  } catch (error) {
    console.error("Gagal mengambil data santri:", error);
    studentName.value = "Data tidak ditemukan";
    walletBalance.value = 0;
  }
};

const handlePayment = (method) => {
  if (form.items.length === 0) return;

  form.datetime = dayjs().format("YYYY-MM-DD HH:mm:ss");
  form.status = "completed";
  form.payment_status = method === "wallet" ? "paid_wallet" : "paid_cash";

  handleSubmit({
    form,
    url: route("admin.transaction.save"),
    onSuccess: () => {
      $q.notify({
        message: "Transaksi berhasil disimpan",
        color: "positive",
        position: "top",
        icon: "check_circle",
      });
      form.reset();
      form.items = [];
    },
    onFinish: () => {
      showPaymentDialog.value = false;
    },
  });
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
          @click="router.get(route('admin.purchase-order.index'))"
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
              :columns="tableColumns"
              @update-quantity="({ id, value }) => updateQuantity(id, value)"
              @remove-item="confirmRemoveItem"
            />
          </div>
        </div>

        <div class="row">
          <div class="col-xs-12 col-md-8 q-pa-sm">
            <q-btn color="grey" flat size="sm" small icon="notes" />
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

      <q-dialog v-model="showPaymentDialog" persistent>
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
                  label="Dompet Santri"
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
