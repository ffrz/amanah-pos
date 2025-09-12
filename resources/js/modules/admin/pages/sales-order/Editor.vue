<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed, nextTick, onMounted, onUnmounted, reactive } from "vue";
import { debounce, Dialog, useQuasar } from "quasar";
import axios from "axios";

import ItemListTable from "./editor/ItemsListTable.vue";
import PaymentDialog from "./editor/PaymentDialog.vue";
import ProductBrowserDialog from "@/components/ProductBrowserDialog.vue";
import CheckBox from "@/components/CheckBox.vue";
import ItemEditorDialog from "./editor/ItemEditorDialog.vue";
import DigitalClock from "@/components/DigitalClock.vue";
import CustomerAutocomplete from "@/components/CustomerAutocomplete.vue";
import {
  formatDateTime,
  formatDateTimeForEditing,
  formatNumber,
} from "@/helpers/formatter";
import HelpDialog from "./editor/HelpDialog.vue";
import { useFullscreen } from "@/composables/useFullscreen";
import { showError, showWarning, showInfo } from "@/composables/useNotify";
import OrderInfoDialog from "./editor/OrderInfoDialog.vue";
import LongTextView from "@/components/LongTextView.vue";
import SuccessDialog from "./editor/SuccessDialog.vue";

const $q = useQuasar();
const page = usePage();
const mergeItem = ref(true);
const userInputRef = ref(null);
const itemEditorRef = ref(null);
const customerAutocompleteRef = ref(null);
const showHelpDialog = ref(false);
const authLayoutRef = ref(null);
const targetDiv = ref(null);
const { isFullscreen, toggleFullscreen } = useFullscreen(targetDiv);
const title = page.props.company.name;
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

const form = reactive({
  id: page.props.data.id,
  formatted_id: page.props.data.formatted_id,
  customer_id: page.props.data.customer_id,
  datetime: new Date(page.props.data.datetime),
  status: page.props.data.status,
  payment_status: page.props.data.payment_status,
  delivery_status: page.props.data.delivery_status,
  notes: page.props.data.notes,
  items: page.props.data.details ?? [],
});

const total = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + item.price * item.quantity;
  }, 0);
});

const handleFullScreenClicked = () => {
  toggleFullscreen();
  if (!isFullscreen.value) {
    authLayoutRef?.value?.hideDrawer();
  }
};

// validations
const validateQuantity = (qty) => {
  if (isNaN(qty) || qty <= 0) {
    showWarning("Kuantitas tidak valid.", "top");
    return false;
  }

  return true;
};

const validatePrice = (price) => {
  if (isNaN(price) || price < 0) {
    showWarning("Harga tidak valid.", "top");
    return false;
  }

  return true;
};

const validateBarcode = (code) => {
  if (!code || code.length == 0) {
    showWarning("Barcode tidak valid.", "top");
    return false;
  }

  return true;
};

// -----
const handleProductSelection = (product) => {
  if (userInput.value.endsWith("*")) {
    userInput.value += product.name;
  } else {
    userInput.value = product.name;
  }
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
    showWarning("Input tidak valid.", "top");
    return;
  }

  if (inputBarcode.length === 0) {
    alert("di kedua");
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
        showWarning("Harga tidak dapat diubah!", "top");
      }
    })
    .catch((error) => {
      showError(error.response?.data?.message, "top");
      console.error("Gagal mengambil data produk:", error);
    });

  isProcessing.value = false;
  nextTick(() => {
    userInputRef.value?.focus();
  });
};

const showItemEditor = (item) => {
  itemToEdit.value = Object.create(item);
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
      });
  });
};

const updateItem = () => {
  isProcessing.value = true;
  const item = itemEditorRef.value.getCurrentItem();
  const data = {
    id: item.id,
    qty: item.quantity,
  };

  if (item.price_editable) {
    data.price = item.price;
  }

  if (item.notes) {
    data.notes = item.notes;
  }

  axios
    .post(route("admin.sales-order.update-item"), data)
    .then((response) => {
      const item = response.data.data;
      const index = form.items.findIndex((data) => data.id === item.id);
      if (index !== -1) {
        form.items[index] = item;
        showInfo("Item telah diperbarui", "top");
      }
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

onMounted(() => {
  const handler = (e) => {
    if (e.key === "F1") {
      e.preventDefault();
      showHelpDialog.value = true;
    } else if (e.key === "F2") {
      e.preventDefault();
      userInputRef.value.focus();
    } else if (e.key === "F3") {
      e.preventDefault();
      customerAutocompleteRef.value.focus();
    } else if (e.key === "F4") {
      e.preventDefault();
      mergeItem.value = !mergeItem.value;
    } else if (e.key === "F10") {
      e.preventDefault();
      showPaymentDialog.value = true;
    } else if (e.key === "F11") {
      e.preventDefault();
      handleFullScreenClicked();
    }
  };
  document.addEventListener("keydown", handler);
  onUnmounted(() => {
    document.removeEventListener("keydown", handler);
  });

  nextTick(() => {
    authLayoutRef?.value?.hideDrawer();
  });
});

const handleCustomerSelected = (data) => {
  customer.value = data;
  form.customer_id = data?.id;
  updateOrder();
};

const updateOrder = () => {
  isProcessing.value = true;

  const data = {
    id: form.id,
    customer_id: form.customer_id,
    datetime: formatDateTimeForEditing(form.datetime),
    notes: form.notes,
  };

  axios
    .post(route("admin.sales-order.update"), data)
    .then((response) => {
      const updated = response.data.data.id;
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

  console.log(data);
  isProcessing.value = true;
  axios
    .post(route("admin.sales-order.close"), payload)
    .then((response) => {
      showInfo("Transaksi selesai");
      router.visit(route("admin.sales-order.detail", { id: form.id }));
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
    message: `Batalkan transaksi #${form.formatted_id}?`,
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
  router.visit(route("admin.sales-order.detail", { id: form.id }), {
    target: "_blank",
  });
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout ref="authLayoutRef">
    <template #title>{{ title }}</template>

    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          v-if="$q.screen.gt.sm"
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
      <div class="row items-center">
        <template v-if="$q.screen.gt.sm">
          <div class="text-weight-bold">
            {{ page.props.auth.user.username }}
          </div>
          <div class="q-mx-sm">|</div>
          <div>
            <DigitalClock />
          </div>
        </template>
        <q-btn
          class="q-ml-sm"
          :icon="isFullscreen ? 'fullscreen_exit' : 'fullscreen'"
          dense
          color="grey-7"
          flat
          rounded
          @click="handleFullScreenClicked()"
        />
      </div>
    </template>
    <q-page class="bg-grey-2 q-pa-none column fit">
      <q-card square flat class="full-width col column q-pb-none">
        <div class="row q-col-gutter-none full-width">
          <div class="col-sm-6 col-12 col">
            <div class="row full-width">
              <CustomerAutocomplete
                ref="customerAutocompleteRef"
                class="custom-select full-width col col-12 bg-white q-pa-sm"
                v-model="customer"
                label="Pelanggan"
                :disable="isProcessing"
                @customer-selected="handleCustomerSelected"
                :min-length="1"
                outlined
              />
              <div class="text-grey q-mt-xs q-ml-sm">
                Saldo:
                {{
                  customer
                    ? "Rp. " + formatNumber(customer ? customer.balance : 0)
                    : "Tidak tersedia"
                }}
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-12">
            <q-input
              ref="userInputRef"
              v-model="userInput"
              @keyup.enter.prevent="addItem()"
              :loading="isProcessing"
              :disable="isProcessing"
              placeholder="Qty * Kode / Barcode * Harga"
              class="col col-12 q-pa-sm bg-white"
              outlined
              clearable
              autofocus
            >
              <template #prepend>
                <q-icon
                  name="search"
                  @click="showProductBrowserDialog = true"
                  class="cursor-pointer"
                />
              </template>
              <template #append>
                <q-icon
                  v-if="userInput?.length > 0"
                  name="send"
                  @click="addItem()"
                  class="cursor-pointer q-ml-md"
                />
              </template>
            </q-input>

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
          <div class="col-12 q-pa-sm column">
            <ItemListTable
              :items="form.items"
              @update-quantity="({ id, value }) => updateQuantity(id, value)"
              @remove-item="removeItem"
              @edit-item="showItemEditor"
              :is-processing="isProcessing"
            />
          </div>
        </div>

        <div
          class="row items-start q-col-gutter-none q-px-sm"
          style="max-height: 80px"
        >
          <div class="col" v-if="$q.screen.gt.sm">
            <div class="text-caption text-grey-6 q-mt-xs">
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
            <div class="q-pa-sm q-pb-none text-grey-8">
              <div>#: {{ form.formatted_id }}</div>
              <div>{{ formatDateTime(form.datetime) }}</div>
            </div>
          </div>

          <div class="col">
            <div
              class="row no-wrap items-start justify-between"
              style="background: #eee; padding: 10px; border: 1px solid #ddd"
            >
              <span class="text-grey-8 text-subtitle-2 text-bold">TOTAL</span>
              <span class="text-h4 text-weight-bold">
                <sup style="font-size: 13px">Rp.</sup>
                {{ formatNumber(total) }}
              </span>
            </div>
          </div>
        </div>
        <div class="row q-px-sm q-pb-none q-py-sm q-col-gutter-sm">
          <div class="col q-py-sm">
            <q-btn
              class="full-width q-py-none"
              label="Bayar"
              color="primary"
              icon="payment"
              @click="showPaymentDialog = true"
              :disable="
                isProcessing ||
                form.items.length === 0 ||
                form.status !== 'draft'
              "
              :loading="isProcessing"
            />
          </div>
          <div class="q-py-sm">
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

      <ItemEditorDialog
        ref="itemEditorRef"
        v-model="showItemEditorDialog"
        :item="itemToEdit"
        @save="updateItem()"
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
