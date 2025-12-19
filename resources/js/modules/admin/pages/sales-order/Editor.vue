<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed, nextTick, onMounted, onUnmounted, reactive } from "vue";
import { Dialog, useQuasar } from "quasar";
import axios from "axios";

import ItemListTable from "./editor/ItemsListTable.vue";
import PaymentDialog from "./editor/PaymentDialog.vue";
import ProductBrowserDialog from "@/components/ProductBrowserDialog.vue";
import CheckBox from "@/components/CheckBox.vue";
import CustomerAutocomplete from "@/components/CustomerAutocomplete.vue";
import {
  formatDateTime,
  formatDateTimeForEditing,
  formatNumber,
} from "@/helpers/formatter";
import HelpDialog from "./editor/HelpDialog.vue";
import { showError, showWarning, showInfo } from "@/composables/useNotify";
import OrderInfoDialog from "./editor/OrderInfoDialog.vue";
import LongTextView from "@/components/LongTextView.vue";
import SuccessDialog from "./editor/SuccessDialog.vue";
import { getCurrentInstance } from "vue";
import BarcodeInputEditor from "@/components/BarcodeInputEditor.vue";
import PartyInfo from "@/components/PartyInfo.vue";
import UserSessionInfo from "@/components/UserSessionInfo.vue";
import SalesOrderItemEditorDialog from "@/components/SalesOrderItemEditorDialog.vue";

const $q = useQuasar();
const page = usePage();
const mergeItem = ref(true);
const userInputRef = ref(null);
const itemEditorRef = ref(null);
const customerAutocompleteRef = ref(null);
const showHelpDialog = ref(false);
const title = page.props.data.code;
const customer = ref(page.props.data.customer);
const payment = ref(null);
const userInput = ref("");
const isProcessing = ref(false);
const showPaymentDialog = ref(false);
const showProductBrowserDialog = ref(false);
const showItemEditorDialog = ref(false);
const showOrderInfoDialog = ref(false);
const showSuccessDialog = ref(false);
const itemToEdit = ref(null);
const selectedPriceType = ref("price_1");

const form = reactive({
  id: page.props.data.id,
  code: page.props.data.code,
  customer_id: page.props.data.customer_id,
  datetime: new Date(page.props.data.datetime),
  status: page.props.data.status,
  payment_status: page.props.data.payment_status,
  delivery_status: page.props.data.delivery_status,
  notes: page.props.data.notes,
  items: page.props.data.details ?? [],
  total_discount: parseFloat(page.props.data.total_discount) || 0,
});

const total = computed(() => {
  return (
    form.items.reduce((sum, item) => {
      return sum + item.price * item.quantity;
    }, 0) - form.total_discount
  );
});

const validateBarcode = (code) => {
  if (!code || code.length == 0) {
    showWarning("Barcode tidak valid.", "top");
    return false;
  }

  return true;
};

const handleProductSelection = (product) => {
  if (userInput.value?.endsWith("*")) {
    userInput.value += product.name;
  } else {
    userInput.value = product.name;
  }

  addItem();
};

const addItem = async () => {
  if (isProcessing.value) {
    // prevent duplicate event
    return;
  }

  const input = userInput.value?.trim();
  if (!input || input.length === 0) {
    showProductBrowserDialog.value = true;
    return;
  }

  // Split dan bersihkan spasi di kiri/kanan setiap bagian
  const parts = input.split("*").map((p) => p.trim());

  let rawQuantity = "1"; // Default string, nanti diparse
  let inputBarcode = "";
  let inputPrice = null;

  if (parts.length === 1) {
    // KASUS 1: Cuma Barcode
    inputBarcode = parts[0];
  } else if (parts.length === 2) {
    if (input.endsWith("*")) {
      showProductBrowserDialog.value = true;
      return;
    }
    // KASUS 2: QTY*ITEM
    rawQuantity = parts[0];
    inputBarcode = parts[1];
  } else if (parts.length === 3) {
    // KASUS 3: QTY * ITEM * HARGA
    rawQuantity = parts[0];
    inputBarcode = parts[1];
    inputPrice = parseFloat(parts[2]);
  } else {
    showWarning("Format input tidak valid.", "top");
    return;
  }

  if (inputBarcode.length === 0) {
    showProductBrowserDialog.value = true;
    return;
  }

  // 1. Validasi Barcode
  if (!validateBarcode(inputBarcode)) {
    // showWarning("Barcode tidak valid", "top"); // aktifkan jika validateBarcode tidak memunculkan alert
    return;
  }

  // Parsing Quantity (Support "1roll") boleh ada spasi ataupun tidak
  let cleanQty = 0;
  let parsedUom = null;

  // ^([0-9\.]+) -> Cari angka/titik di awal
  // \s* -> Boleh ada spasi atau tidak
  // .*)$      -> Ambil sisanya sebagai teks (satuan)
  const match = rawQuantity.match(/^([0-9]+(?:\.[0-9]+)?)\s*(.*)$/);

  if (match) {
    // Grup 1 adalah Angka
    cleanQty = parseFloat(match[1]);

    // Grup 2 adalah Teks (Satuan). Kita trim() agar spasi hilang.
    const unitStr = match[2] ? match[2].trim() : "";

    // Jika ada teksnya, simpan ke parsedUom
    if (unitStr.length > 0) {
      parsedUom = unitStr;
    }
  } else {
    // Fallback jika input tidak sesuai pola (misal ".5" atau error lain)
    cleanQty = parseFloat(rawQuantity);
  }

  // Validasi hasil parse quantity
  if (isNaN(cleanQty) || cleanQty <= 0) {
    showWarning("Kuantitas tidak valid.", "top");
    return;
  }

  // Validasi Price (Jika ada input)
  if (inputPrice !== null) {
    if (isNaN(inputPrice) || inputPrice < 0) {
      showWarning("Harga tidak valid.", "top");
      return;
    }
  }

  isProcessing.value = true;
  await axios
    .post(route("admin.sales-order.add-item"), {
      order_id: form.id,
      product_code: inputBarcode,
      qty: cleanQty, // Kirim angka bersih (bukan string "1roll")
      uom: parsedUom,
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

      // Cek apakah harga dari server berbeda (misal karena tidak boleh edit harga)
      if (
        inputPrice !== null &&
        Math.abs(inputPrice - parseFloat(currentItem.price)) > 1
      ) {
        showWarning("Harga disesuaikan oleh sistem.", "top");
      }

      // FIXME: deteksi lebih lanjut apakah input barcode atau nama produk
      // jika input barcode dan sudah multi satuan, kita bisa gunakan mode barcode input
      // agar transaksi lebih cepat, tapi bertentangan dengan prinsip input QTY*BARCODE
      // yang sudah ada sekarang. Jadi untuk sekarang kita tampilkan editor item saja
      // jika hanya ada 1 bagian input (hanya barcode saja)
      if (parts.length == 1) {
        showItemEditor(currentItem);
      }
    })
    .catch((error) => {
      showError(error.response?.data?.message, "top");
      console.error("Gagal mengambil data produk:", error);
    })
    .finally(() => {
      isProcessing.value = false;
      focusToUserInput();
    });
};

const showItemEditor = (item) => {
  itemToEdit.value = { ...item };
  showItemEditorDialog.value = true;
};

const removeItem = (item) => {
  $q.dialog({
    title: "Hapus Item",
    message: `Apakah Anda yakin akan menghapus item ${item.product_name}?`,
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
          showInfo("Item telah dihapus", "top");
        }
      })
      .catch((error) => {
        showError(
          error?.response?.data?.message ??
            "Terdapat kesalahan saat menghapus item!",
          "top"
        );
        console.error(error);
      })
      .finally(() => {
        isProcessing.value = false;
        focusToUserInput();
      });
  });
};

const updateItem = async () => {
  isProcessing.value = true;
  const item = itemEditorRef.value.getCurrentItem();
  const data = {
    id: item.id,
    qty: item.quantity,
    uom: item.product_uom,
  };

  if (item.product.price_editable) {
    data.price = item.price;
  }

  if (item.notes) {
    data.notes = item.notes;
  }

  await axios
    .post(route("admin.sales-order.update-item"), data)
    .then((response) => {
      const item = response.data.data;
      const index = form.items.findIndex((data) => data.id === item.id);
      if (index !== -1) {
        form.items[index] = item;
        showInfo("Item telah diperbarui", "top");
      }
      showItemEditorDialog.value = false;
    })
    .catch((error) => {
      showError(
        error?.response?.data?.message ?? "Terdapat kesalahan saat menyimpan!",
        "top"
      );
      console.error(error);
    })
    .finally(() => {
      isProcessing.value = false;
      focusToUserInput();
    });
};

onMounted(() => {
  const handler = (e) => {
    // abaikan jika ada dialog yang sedang terbuka
    if (
      showPaymentDialog.value ||
      showHelpDialog.value ||
      showItemEditorDialog.value ||
      showProductBrowserDialog.value ||
      showOrderInfoDialog.value
    ) {
      return;
    }

    if (e.key === "F1") {
      e.preventDefault();
      showHelpDialog.value = true;
    } else if (e.key === "F2") {
      e.preventDefault();
      customerAutocompleteRef.value.focus();
    } else if (e.key === "F3") {
      e.preventDefault();
      userInputRef.value.focus();
    } else if (e.key === "F4") {
      e.preventDefault();
      mergeItem.value = !mergeItem.value;
    } else if (e.key === "F12" || (e.ctrlKey && e.key === "Enter")) {
      e.preventDefault();
      if (isValidOrder.value) {
        showPaymentDialog.value = true;
      }
    } else if (e.key === "F5" || e.key === "F6" || e.key === "F7") {
      e.preventDefault();
    }
  };
  document.addEventListener("keydown", handler);
  onUnmounted(() => {
    document.removeEventListener("keydown", handler);
  });
});

const handleCustomerSelected = async (data) => {
  customer.value = data;
  form.customer_id = data?.id;
  await updateOrder();
  if (data?.id) {
    selectedPriceType.value = customer.value.default_price_type ?? "price_1";
    userInputRef.value.focus();
  } else {
    selectedPriceType.value = "price_1";
    customerAutocompleteRef.value.focus();
  }
};

const updateOrder = async () => {
  isProcessing.value = true;

  const data = {
    id: form.id,
    customer_id: form.customer_id ?? null,
    datetime: formatDateTimeForEditing(form.datetime),
    total_discount: form.total_discount,
    notes: form.notes,
  };

  await axios
    .post(route("admin.sales-order.update"), data)
    .then((response) => {
      const updated = response.data.data;
      form.customer_id = updated.customer_id;
    })
    .catch((error) => {
      showError(
        error?.response?.data?.message ?? "Terdapat kesalahan saat menyimpan!",
        "top"
      );
      console.error(error);
    })
    .finally(() => {
      isProcessing.value = false;
    });
};

const handlePayment = (data) => {
  if (form.items.length === 0) {
    showInfo("Item masih kosong", "top");
    return;
  }

  const payload = {
    id: form.id,
    ...data,
  };

  isProcessing.value = true;
  axios
    .post(route("admin.sales-order.close"), payload)
    .then((response) => {
      showInfo("Transaksi selesai");
      if (payload.after_payment_action === "print") {
        window.open(
          route("admin.sales-order.print", {
            id: form.id,
            size: page.props.settings.default_print_size,
          }),
          "_self"
        );
      } else if (payload.after_payment_action === "detail") {
        router.get(
          route("admin.sales-order.detail", {
            id: form.id,
          })
        );
      } else if (payload.after_payment_action === "new-order") {
        router.get(route("admin.sales-order.add"));
      }
      return;
    })
    .catch((error) => {
      showError(error.response?.data?.message);
      console.error(error);
    })
    .finally(() => {
      isProcessing.value = false;
    });
};

const cancelOrder = () => {
  Dialog.create({
    title: "Konfirmasi Pembatalan",
    icon: "question",
    message: `Batalkan transaksi #${form.code}?`,
    focus: "cancel",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    axios
      .post(
        route("admin.sales-order.cancel", {
          id: form.id,
        }),
        { id: form.id }
      )
      .then(() => {
        showInfo("Transaksi telah dibatalkan.");
        router.visit(route("admin.sales-order.detail", { id: form.id }));
      })
      .catch((error) => {
        const errorMessage =
          error.response?.data?.message ||
          "Terjadi kesalahan saat membatalkan transaksi.";
        $q.notify({
          message: errorMessage,
          color: "warning",
          position: "bottom",
        });
        console.error(error);
      });
  });
};

const invoicePreview = () => {
  window.open(
    route("admin.sales-order.detail", { id: form.id }) + "?preview=1",
    "_blank"
  );
};

const isValidOrder = computed(() => {
  return (
    getCurrentInstance().appContext.config.globalProperties.$can(
      "admin.sales-order.close"
    ) &&
    !isProcessing.value &&
    form.items.length !== 0 &&
    form.status === "draft"
  );
});

const focusToUserInput = () => {
  nextTick(() => {
    userInputRef.value?.focus();
  });
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout ref="authLayoutRef" :show-drawer-button="true">
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
    <template #right-button>
      <UserSessionInfo v-if="$q.screen.gt.sm" />
    </template>
    <q-page class="bg-grey-2 column fit">
      <q-card square flat class="full-width col column">
        <div class="row q-col-gutter-none full-width">
          <div class="col-sm-6 col-12 col q-pa-sm">
            <div class="row full-width">
              <CustomerAutocomplete
                ref="customerAutocompleteRef"
                class="custom-select full-width col col-12 bg-white"
                v-model="customer"
                label="Pelanggan"
                :disable="isProcessing"
                @customer-selected="handleCustomerSelected"
                :min-length="1"
                outlined
                autofocus
              />
              <PartyInfo
                v-if="customer"
                :party="customer"
                :is-valid-wallet-balance="true"
              />
            </div>
          </div>
          <div class="col-sm-6 col-12">
            <BarcodeInputEditor
              ref="userInputRef"
              v-model="userInput"
              @keyup.enter.prevent="addItem()"
              @scan-success="addItem()"
              @send="addItem()"
              @search="showProductBrowserDialog = true"
              :loading="isProcessing"
              :disable="isProcessing"
              placeholder="Qty * Kode / Barcode * Harga"
              class="col col-12 q-pa-xs bg-white"
              outlined
              clearable
            />

            <div class="row q-ml-sm items-end">
              <CheckBox
                class="col"
                v-model="mergeItem"
                label="Gabungkan item"
                :disable="isProcessing"
              />
            </div>
          </div>
        </div>
        <div class="row col grow">
          <div class="col-12 q-px-sm column">
            <ItemListTable
              :items="form.items"
              @update-quantity="({ id, value }) => updateQuantity(id, value)"
              @remove-item="removeItem"
              @edit-item="showItemEditor"
              :is-processing="isProcessing"
            />
          </div>
        </div>

        <div class="row items-start q-col-gutter-none q-px-sm">
          <div class="col" v-if="$q.screen.gt.sm">
            <div class="text-caption text-grey-6">
              {{ form.items.length }} item(s)
            </div>

            <div class="text-caption text-grey-8 q-mt-xs" v-if="form.notes">
              <div class="text-bold">Catatan:</div>
              <div class="text-italic">
                <LongTextView :text="form.notes" />
              </div>
            </div>
          </div>

          <div class="col" v-if="$q.screen.gt.sm">
            <div class="text-grey-8">
              <div>
                <q-icon class="inline-icon" name="tag" />{{ form.code }}
              </div>
              <div>
                <q-icon class="inline-icon" name="calendar_today" />{{
                  formatDateTime(form.datetime)
                }}
              </div>
            </div>
          </div>

          <div class="col">
            <div
              style="background: #eee; padding: 10px; border: 1px solid #ddd"
            >
              <div
                v-if="form.total_discount > 0"
                class="row no-wrap items-start justify-between"
              >
                <span class="text-grey-8 text-subtitle-2">DISKON AKHIR</span>
                <span class="text-subtitle2">
                  Rp. {{ formatNumber(form.total_discount) }}
                </span>
              </div>
              <div class="row no-wrap items-start justify-between">
                <span class="text-grey-8 text-subtitle-2 text-bold">TOTAL</span>
                <span class="text-h4 text-weight-bold">
                  <sup style="font-size: 13px">Rp.</sup>
                  {{ formatNumber(total) }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="row q-col-gutter-sm q-py-xs q-px-sm">
          <div class="col">
            <q-btn
              class="full-width q-py-none"
              label="Bayar"
              color="primary"
              icon="payment"
              @click="showPaymentDialog = true"
              :disable="!isValidOrder"
              :loading="isProcessing"
            />
          </div>
          <div>
            <q-btn
              class="q-py-none"
              icon="more_vert"
              color="grey"
              @click.stop
              style="width: 10px"
            >
              <q-menu
                anchor="bottom right"
                self="top right"
                transition-show="scale"
                transition-hide="scale"
              >
                <q-list style="width: 200px">
                  <q-item
                    clickable
                    v-ripple
                    v-close-popup
                    @click.stop="showOrderInfoDialog = true"
                  >
                    <q-item-section avatar>
                      <q-icon name="edit" />
                    </q-item-section>
                    <q-item-section>Edit Rincian</q-item-section>
                  </q-item>
                  <q-item
                    clickable
                    v-ripple
                    v-close-popup
                    @click.stop="invoicePreview()"
                  >
                    <q-item-section avatar>
                      <q-icon name="preview" />
                    </q-item-section>
                    <q-item-section>Pratinjau</q-item-section>
                  </q-item>
                  <q-item
                    clickable
                    v-ripple
                    v-close-popup
                    @click.stop="cancelOrder()"
                  >
                    <q-item-section avatar>
                      <q-icon name="cancel" color="negative" />
                    </q-item-section>
                    <q-item-section class="text-negative">
                      Batalkan
                    </q-item-section>
                  </q-item>
                  <q-separator />
                </q-list>
              </q-menu>
            </q-btn>
          </div>
        </div>
      </q-card>

      <SalesOrderItemEditorDialog
        ref="itemEditorRef"
        v-model="showItemEditorDialog"
        :item="itemToEdit"
        :customer="customer"
        @save="updateItem()"
        @hide="focusToUserInput"
        :is-processing="isProcessing"
      />
      <PaymentDialog
        v-model="showPaymentDialog"
        @accepted="handlePayment"
        :form="form"
        :customer="customer"
        :total="total"
        :accounts="page.props.accounts"
      />
      <ProductBrowserDialog
        v-model="showProductBrowserDialog"
        @product-selected="handleProductSelection"
        :priceType="selectedPriceType"
        :show-cost="$can('admin.product:view-cost')"
      />
      <OrderInfoDialog
        v-model="showOrderInfoDialog"
        :data="form"
        @save="updateOrder()"
        :is-processing="isProcessing"
      />
      <HelpDialog v-model="showHelpDialog" />
      <SuccessDialog
        v-model="showSuccessDialog"
        :order="form"
        :customer="customer"
        :total="total"
        :payment="payment"
      />
    </q-page>
  </authenticated-layout>
</template>
