<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { create_options, scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DateTimePicker from "@/components/DateTimePicker.vue";
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import { useQuasar } from "quasar";
import dayjs from "dayjs";

const $q = useQuasar();
const page = usePage();

// Form untuk transaksi kasir
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
const barcodeInputRef = ref();
const barcode = ref("");

const currentDateTime = ref(new Date());
const isProcessing = ref(false);
const showDeleteDialog = ref(false);
const itemToDelete = ref(null);
const showSupplierEditor = ref(false);

let timeInterval = null;

// Table columns untuk items
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

// Computed properties
const currentDate = computed(() => {
  return currentDateTime.value.toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
});

const currentTime = computed(() => {
  return currentDateTime.value.toLocaleTimeString("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  });
});

const subtotal = computed(() => {
  const total = form.items.reduce((sum, item) => {
    return sum + item.price * item.quantity;
  }, 0);
  form.subtotal = total;
  form.total = total + form.tax;
  return total;
});

// Methods
const formatCurrency = (number) => {
  return new Intl.NumberFormat("id-ID").format(number || 0);
};

const addItem = async () => {
  if (!barcode.value.trim()) {
    $q.notify({
      message: "Silakan masukkan barcode",
      color: "warning",
      position: "top",
    });
    return;
  }

  // TODO: cek format input, bolehkan dengan format QTY*KODE*HARGA atau QTY*KODE atau KODE saja
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
        position: "top",
      });
    }

    if (!isNaN(parsedPrice) && parsedPrice >= 0) {
      // Harga bisa 0
      inputPrice = parsedPrice;
    } else {
      $q.notify({
        message: "Harga tidak valid. Menggunakan harga produk default.",
        color: "warning",
        position: "top",
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
        position: "top",
      });
    }
    inputBarcode = parts[1]; // Kode produk ada di bagian akhir
  }
  // Jika parts.length === 1, itu adalah format KODE saja,
  // inputQuantity tetap 1 dan inputBarcode tetap nilai aslinya.

  // Check if item already exists
  const existingItemIndex = form.items.findIndex(
    (item) => item.barcode === inputBarcode // Gunakan inputBarcode yang sudah di-parse
  );

  if (existingItemIndex !== -1) {
    form.items[existingItemIndex].quantity += inputQuantity; // Tambahkan kuantitas yang di-parse
    $q.notify({
      message: `Quantity ${form.items[existingItemIndex].name} bertambah ${inputQuantity}`,
      color: "positive",
      position: "top",
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
        ...fetchedProduct, // Salin semua properti dari produk yang diambil
        quantity: inputQuantity, // Gunakan kuantitas yang di-parse
      };

      form.items.push(newItem);

      $q.notify({
        message: `${newItem.name} berhasil ditambahkan`,
        color: "positive",
        position: "top",
        icon: "add_shopping_cart",
      });
    } catch (error) {
      console.error("Error fetching item:", error); // Log error untuk debugging
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

  // Focus back to barcode input
  barcodeInputRef.value.focus();
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
        position: "top",
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
      position: "top",
    });
    return;
  }

  // Update form data
  form.datetime = dayjs().format("YYYY-MM-DD HH:mm:ss");
  form.status = "completed";
  form.payment_status = "paid";

  // Submit transaction
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
      // Reset form
      form.reset();
      form.items = [];
    },
  });
};

const clearTransaction = () => {
  $q.dialog({
    title: "Konfirmasi",
    message: "Hapus semua item dalam transaksi?",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    form.items = [];
    form.notes = "";
    barcode.value = "";

    $q.notify({
      message: "Transaksi dibersihkan",
      color: "info",
      position: "top",
    });
  });
};

// Lifecycle hooks
onMounted(() => {
  timeInterval = setInterval(() => {
    currentDateTime.value = new Date();
  }, 1000);

  nextTick(() => {
    barcodeInputRef.value.focus();
  });
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});
</script>

<template>
  <i-head title="Kasir POS - AMANAH" />
  <authenticated-layout>
    <template #title>Kasir POS - AMANAH</template>
    <q-page class="bg-grey-2 q-pa-sm column fit">
      <q-card square flat bordered class="full-width col column">
        <q-card-section class="row items-center q-pa-md">
          <div class="col-4">
            <div class="text-bold">
              Info Supplier
              <q-btn
                icon="edit"
                flat
                rounded
                dense
                color="grey"
                size="sm"
                @click="showSupplierEditor = true"
              />
            </div>
            <div class="text-grey-8">Supplier belum ditetapkan</div>
          </div>

          <div class="col-4">
            <div class="text-h6 text-weight-bold text-grey-8 text-center">
              {{ $config.APP_NAME }}
            </div>
          </div>

          <div class="col-4">
            <div class="text-right">
              <div class="text-weight-bold">
                {{ page.props.auth.user.username }} -
                {{ page.props.auth.user.name }}
              </div>
              <div class="text-grey-6">
                {{ currentDate }} - {{ currentTime }}
              </div>
            </div>
          </div>
        </q-card-section>

        <div class="row col **grow**">
          <div class="col-8 q-pa-sm column">
            <q-table
              :rows="form.items"
              :columns="columns"
              row-key="id"
              flat
              square
              bordered
              class="bg-grey-1 pos-table q-pa-none col"
              :rows-per-page-options="[0]"
              hide-pagination
              :no-data-label="'Belum ada item'"
              virtual-scroll
              :virtual-scroll-item-size="48"
              :virtual-scroll-sticky-size-start="48"
            >
              <template v-slot:header="props">
                <q-tr :props="props" class="bg-grey-4">
                  <q-th
                    v-for="col in props.cols"
                    :key="col.name"
                    :props="props"
                    class="text-weight-bold text-grey-8"
                  >
                    {{ col.label }}
                  </q-th>
                </q-tr>
              </template>

              <template v-slot:no-data="{ message }">
                <div
                  class="full-width row flex-center text-grey-5 q-gutter-sm"
                  style="min-height: 400px"
                >
                  <q-icon size="3em" name="shopping_cart" />
                  <div class="text-center">
                    <div class="text-subtitle1">{{ message }}</div>
                    <div class="text-caption">
                      Scan barcode untuk menambah item
                    </div>
                  </div>
                </div>
              </template>

              <template v-slot:body="props">
                <q-tr :props="props" class="hover-highlight">
                  <q-td key="name" :props="props" class="text-left">
                    <div class="text-weight-medium">{{ props.row.name }}</div>
                    <div class="text-caption text-grey-6">
                      Barcode: {{ props.row.barcode }}
                    </div>
                  </q-td>
                  <q-td key="price" :props="props" class="text-center">
                    <div class="text-weight-medium">
                      <LocaleNumberInput
                        :model-value="props.row.price"
                        dense
                        style="width: 80px; text-align: right"
                      />
                    </div>
                  </q-td>
                  <q-td key="quantity" :props="props" class="text-center">
                    <LocaleNumberInput
                      :model-value="props.row.quantity"
                      dense
                      style="width: 80px; text-align: right"
                      @update:model-value="
                        (val) => updateQuantity(props.row.id, val)
                      "
                      @keyup.enter="$event.target.blur()"
                    />
                  </q-td>
                  <q-td key="subtotal" :props="props" class="text-center">
                    <div class="text-weight-bold text-primary">
                      Rp.
                      {{ formatCurrency(props.row.price * props.row.quantity) }}
                    </div>
                  </q-td>
                  <q-td key="action" :props="props" class="text-center">
                    <q-btn
                      icon="delete"
                      color="negative"
                      flat
                      round
                      size="sm"
                      @click="confirmRemoveItem(props.row)"
                    >
                      <q-tooltip>Hapus Item</q-tooltip>
                    </q-btn>
                  </q-td>
                </q-tr>
              </template>
            </q-table>
          </div>

          <div class="col-4 q-pa-sm column">
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
          </div>
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
            >
              <template v-slot:prepend>
                <q-icon name="note" />
              </template>
            </q-input>
          </div>

          <div class="col-4 q-pa-sm">
            <div class="row items-center justify-end q-gutter-sm">
              <q-icon name="receipt" color="primary" />
              <span class="text-weight-bold text-grey-8">SUBTOTAL:</span>
              <span class="text-h6 text-weight-bold text-primary">
                Rp. {{ formatCurrency(subtotal) }}
              </span>
            </div>
            <div class="text-caption text-grey-6 q-mt-xs text-right">
              {{ form.items.length }} item(s)
            </div>

            <div class="q-py-sm">
              <q-input
                ref="barcodeInputRef"
                v-model="barcode"
                placeholder="<Input Barcode>"
                outlined
                square
                class="col bg-white"
                @keyup.enter="addItem"
                :loading="isProcessing"
                clearable
              >
                <template v-slot:prepend>
                  <q-icon name="qr_code_scanner" />
                </template>
              </q-input>
            </div>

            <div class="q-py-sm">
              <q-btn
                class="full-width"
                label="Bayar"
                color="primary"
                icon="payment"
                @click="processPayment"
                :disable="form.items.length === 0"
                :loading="form.processing"
              />
            </div>
          </div>
        </div>
      </q-card>

      <q-dialog v-model="showDeleteDialog" persistent>
        <q-card>
          <q-card-section class="row items-center">
            <q-avatar icon="delete" color="negative" text-color="white" />
            <span class="q-ml-sm">Hapus item "{{ itemToDelete?.name }}"?</span>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Batal" color="primary" v-close-popup />
            <q-btn
              flat
              label="Hapus"
              color="negative"
              @click="removeItem"
              v-close-popup
            />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <q-dialog v-model="showSupplierEditor" persistent>
        <q-card>
          <q-card-section class="q-py-none q-pt-lg">
            <div class="text-bold text-grey-8">Edit Info Supplier</div>
          </q-card-section>
          <q-card-section>
            <q-input label="Nama Supplier" />
            <q-input
              label="Alamat"
              type="textarea"
              autogrow
              counter
              maxlength="200"
            />
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Batal" color="primary" v-close-popup />
            <q-btn
              flat
              label="Simpan"
              color="primary"
              @click="removeItem"
              v-close-popup
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
