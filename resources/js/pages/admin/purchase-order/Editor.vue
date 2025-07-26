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
import SupplierEditorDialog from "./editor/SupplierEditorDialog.vue";

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
const showSupplierEditor = ref(false);
const columns = [
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
    name: "price",
    label: "Harga",
    align: "center",
    field: "price",
    sortable: false,
    style: "width: 120px",
  },
  {
    name: "quantity",
    label: "Quantity",
    align: "center",
    field: "quantity",
    sortable: false,
    style: "width: 100px",
  },
  {
    name: "subtotal",
    label: "SubTotal",
    align: "center",
    sortable: false,
    style: "width: 140px",
  },
  {
    name: "action",
    label: "Action",
    align: "center",
    sortable: false,
    style: "width: 80px",
  },
];
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
      position: "bottom",
    });
    return;
  }

  let inputQuantity = 1;
  let inputBarcode = barcode.value.trim();
  let inputPrice = null; // Akan digunakan jika harga disediakan dalam input

  const parts = inputBarcode.split("*");

  if (parts.length === 3) {
    // Format: QTY*KODE*HARGA
    const parsedQty = parseInt(parts[0]);
    const parsedPrice = parseFloat(parts[2]);

    if (!isNaN(parsedQty) && parsedQty > 0) {
      inputQuantity = parsedQty;
    } else {
      $q.notify({
        message: "Kuantitas tidak valid. Menggunakan default 1.",
        color: "warning",
        position: "bottom",
      });
    }

    if (!isNaN(parsedPrice) && parsedPrice >= 0) {
      // Harga bisa 0
      inputPrice = parsedPrice;
    } else {
      $q.notify({
        message: "Harga tidak valid. Menggunakan harga produk default.",
        color: "warning",
        position: "bottom",
      });
    }

    inputBarcode = parts[1]; // Kode produk ada di bagian tengah
  } else if (parts.length === 2) {
    // Format: QTY*KODE
    const parsedQty = parseInt(parts[0]);
    if (!isNaN(parsedQty) && parsedQty > 0) {
      inputQuantity = parsedQty;
    } else {
      $q.notify({
        message: "Kuantitas tidak valid. Menggunakan default 1.",
        color: "warning",
        position: "bottom",
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
      position: "bottom",
    });
  } else {
    isProcessing.value = true;

    try {
      // Simulate API call untuk get product by barcode (gunakan inputBarcode)
      await new Promise((resolve) => setTimeout(resolve, 300));

      // Asumsi API mengembalikan objek produk.
      // Jika inputPrice disediakan, kita bisa menggunakannya, jika tidak, pakai harga dari API/default.
      const fetchedProduct = {
        id: Date.now(), // Ini biasanya dari backend
        name: `Item ${inputBarcode}`, // Nama dari API
        barcode: inputBarcode,
        price:
          inputPrice !== null
            ? inputPrice
            : Math.floor(Math.random() * 50000) + 10000, // Gunakan inputPrice jika ada, else random/default
        // Tambahan: Jika API mengembalikan harga, Anda bisa menggunakannya di sini jika inputPrice null.
      };

      const newItem = {
        ...fetchedProduct,
        quantity: inputQuantity,
      };

      form.items.push(newItem);

      $q.notify({
        message: `${newItem.name} berhasil ditambahkan`,
        color: "positive",
        position: "bottom",
        icon: "add_shopping_cart",
      });
    } catch (error) {
      console.error("Error fetching item:", error);
      $q.notify({
        message: "Item tidak ditemukan atau gagal menambahkan item",
        color: "negative",
        position: "bottom",
      });
    } finally {
      isProcessing.value = false;
    }
  }

  barcode.value = "";
  await nextTick();

  // TODO: fokus kembali ke input barcode
  // gimana caranya manggil focusOnBarcodeInput dari komponen TransactionSummary
  transactionSummaryRef.value.focusOnBarcodeInput();
};

const confirmRemoveItem = (item) => {
  itemToDelete.value = item;
  showDeleteDialog.value = true;
};

const removeItem = () => {
  if (itemToDelete.value) {
    const index = form.items.findIndex(
      (item) => item.id === itemToDelete.value.id
    );
    if (index !== -1) {
      const removedItem = form.items.splice(index, 1)[0];
      $q.notify({
        message: `${removedItem.name} dihapus dari keranjang`,
        color: "info",
        position: "bottom",
        icon: "remove_shopping_cart",
      });
    }
    itemToDelete.value = null;
  }
};

const updateQuantity = (id, newQuantity) => {
  const item = form.items.find((item) => item.id === id);
  if (item && newQuantity > 0) {
    item.quantity = parseInt(newQuantity) || 1;
  }
};

const processPayment = () => {
  if (form.items.length === 0) {
    $q.notify({
      message: "Tidak ada item dalam keranjang",
      color: "warning",
      position: "bottom",
    });
    return;
  }

  form.datetime = dayjs().format("YYYY-MM-DD HH:mm:ss");
  form.status = "completed";
  form.payment_status = "paid";

  handleSubmit({
    form,
    url: route("admin.transaction.save"),
    onSuccess: () => {
      $q.notify({
        message: "Transaksi berhasil disimpan",
        color: "positive",
        position: "bottom",
        icon: "check_circle",
      });
      form.reset();
      form.items = [];
    },
  });
};
</script>

<template>
  <i-head title="Order Pembelian" />
  <authenticated-layout>
    <template #title>Order Pembelian</template>

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
          @edit-supplier="showSupplierEditor = true"
        />

        <div class="row col **grow**">
          <div class="col-12 q-pa-sm column">
            <ItemListTable
              :items="form.items"
              :columns="columns"
              @update-quantity="({ id, value }) => updateQuantity(id, value)"
              @remove-item="confirmRemoveItem"
            />
          </div>

          <!-- <div class="col-4 q-pa-sm column">
            <q-card flat square bordered class="full-width col">
              <q-card-section
                class="text-center text-weight-bold bg-grey-4 text-grey-8"
              >
                <q-icon name="local_offer" class="q-mr-sm" />
                Informasi Promosi
              </q-card-section>
              <q-card-section class="bg-white col flex-center">
                <div class="text-center text-grey-5 q-pt-xl">
                  <q-icon name="info" size="2em" class="q-mb-md" />
                  <div>Tidak ada promo aktif</div>
                  <div class="text-caption q-mt-sm">
                    Promo akan ditampilkan di sini
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div> -->
        </div>

        <div class="row">
          <div class="col-8 q-pa-sm">
            <q-input
              v-model="form.notes"
              type="textarea"
              placeholder="Tambahkan catatan transaksi..."
              square
              outlined
              class="bg-white"
              :rows="3"
              counter
              maxlength="200"
              :error="!!form.errors.notes"
              :error-message="form.errors.notes"
              style="margin-bottom: 16px"
            >
              <template v-slot:prepend>
                <q-icon name="note" />
              </template>
            </q-input>
          </div>

          <div class="col-4 q-pa-sm">
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

      <SupplierEditorDialog v-model="showSupplierEditor" />
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
