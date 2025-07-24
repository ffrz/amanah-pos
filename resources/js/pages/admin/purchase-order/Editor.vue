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
const barcode = ref("");
const currentDateTime = ref(new Date());
const isProcessing = ref(false);
const showDeleteDialog = ref(false);
const itemToDelete = ref(null);
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

  // Check if item already exists
  const existingItemIndex = form.items.findIndex(
    (item) => item.barcode === barcode.value.trim()
  );

  if (existingItemIndex !== -1) {
    form.items[existingItemIndex].quantity += 1;
    $q.notify({
      message: `Quantity ${form.items[existingItemIndex].name} bertambah`,
      color: "positive",
      position: "top",
    });
  } else {
    isProcessing.value = true;

    try {
      // Simulate API call untuk get product by barcode
      await new Promise((resolve) => setTimeout(resolve, 300));

      const newItem = {
        id: Date.now(),
        name: `Item ${barcode.value}`,
        barcode: barcode.value.trim(),
        price: Math.floor(Math.random() * 50000) + 10000,
        quantity: 1,
      };

      form.items.push(newItem);

      $q.notify({
        message: `${newItem.name} berhasil ditambahkan`,
        color: "positive",
        position: "top",
        icon: "add_shopping_cart",
      });
    } catch (error) {
      $q.notify({
        message: "Gagal menambahkan item",
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
  const barcodeInput = document.querySelector(
    'input[placeholder="Input Barcode"]'
  );
  if (barcodeInput) {
    barcodeInput.focus();
  }
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
    const barcodeInput = document.querySelector(
      'input[placeholder="Input Barcode"]'
    );
    if (barcodeInput) {
      barcodeInput.focus();
    }
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
    <q-page class="bg-grey-2 q-pa-md">
      <q-card class="full-width shadow-4">
        <!-- Header -->
        <q-card-section
          class="row items-center justify-between q-pa-md bg-white"
        >
          <div class="row items-center q-gutter-md">
            <q-chip color="grey-4" text-color="black" icon="check">
              Info Pelanggan
            </q-chip>
            <div class="text-caption text-grey-6">
              <div>Jenis Transaksi: Umum</div>
              <div>Kasir: {{ $page.props.auth.user.name }}</div>
              <div>Jam: {{ currentTime }}</div>
            </div>
          </div>

          <div class="text-center">
            <div class="text-h4 text-weight-bold text-grey-8">AMANAH</div>
          </div>

          <div class="text-right text-body2">
            <div class="text-weight-bold">Nama: Kasir</div>
            <div class="text-grey-6">{{ currentDate }}</div>
            <div class="text-grey-6">{{ currentTime }}</div>
          </div>
        </q-card-section>

        <q-separator />

        <!-- Main Content -->
        <div class="row no-wrap">
          <!-- Left Side - Items Table -->
          <div class="col q-pa-md">
            <q-table
              :rows="form.items"
              :columns="columns"
              row-key="id"
              flat
              bordered
              class="bg-grey-1 pos-table"
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
                      Rp. {{ formatCurrency(props.row.price) }}
                    </div>
                  </q-td>
                  <q-td key="quantity" :props="props" class="text-center">
                    <q-input
                      :model-value="props.row.quantity"
                      type="number"
                      min="1"
                      dense
                      outlined
                      style="width: 80px"
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

          <!-- Right Side - Promo Information -->
          <div class="col-4 bg-grey-1 q-pa-md">
            <q-card flat>
              <q-card-section
                class="text-center text-weight-bold bg-grey-4 text-grey-8"
              >
                <q-icon name="local_offer" class="q-mr-sm" />
                Promo Information
              </q-card-section>
              <q-card-section class="bg-white" style="min-height: 400px">
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

        <q-separator />

        <!-- Bottom Section -->
        <q-card-section class="bg-grey-1">
          <div class="row items-end q-gutter-md">
            <!-- Left - Note/Comment -->
            <div class="col">
              <q-input
                v-model="form.notes"
                type="textarea"
                placeholder="Tambahkan catatan transaksi..."
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

            <!-- Right - Subtotal and Controls -->
            <div class="col-auto">
              <div class="column q-gutter-sm" style="min-width: 280px">
                <!-- Subtotal Display -->
                <q-card flat class="bg-white q-pa-md text-right shadow-2">
                  <div class="row items-center justify-end q-gutter-sm">
                    <q-icon name="receipt" color="primary" />
                    <span class="text-weight-bold text-grey-8">SUBTOTAL:</span>
                    <span class="text-h6 text-weight-bold text-primary">
                      Rp. {{ formatCurrency(subtotal) }}
                    </span>
                  </div>
                  <div class="text-caption text-grey-6 q-mt-xs">
                    {{ form.items.length }} item(s)
                  </div>
                </q-card>

                <!-- Barcode Input -->
                <div class="row q-gutter-sm">
                  <q-input
                    v-model="barcode"
                    placeholder="Input Barcode"
                    outlined
                    class="col bg-white"
                    @keyup.enter="addItem"
                    :loading="isProcessing"
                    clearable
                  >
                    <template v-slot:prepend>
                      <q-icon name="qr_code_scanner" />
                    </template>
                  </q-input>
                  <q-btn
                    label="Add"
                    color="primary"
                    icon="add"
                    @click="addItem"
                    :loading="isProcessing"
                    :disable="!barcode.trim()"
                  />
                </div>

                <!-- Action Buttons -->
                <div class="row q-gutter-sm">
                  <q-btn
                    label="Bayar"
                    color="positive"
                    icon="payment"
                    class="col"
                    @click="processPayment"
                    :disable="form.items.length === 0"
                    :loading="form.processing"
                  />
                  <q-btn
                    label="Clear"
                    color="warning"
                    icon="clear_all"
                    outline
                    @click="clearTransaction"
                    :disable="form.items.length === 0"
                  />
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Confirmation Dialog -->
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
  padding: 12px 8px;
}

.q-table thead th {
  padding: 12px 8px;
  font-weight: 600;
}
</style>
