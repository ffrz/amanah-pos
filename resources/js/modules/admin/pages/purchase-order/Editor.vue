<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref, computed, nextTick, onMounted, onUnmounted, reactive } from "vue";
import { Dialog, useQuasar } from "quasar";
import axios from "axios";

import ItemListTable from "./editor/ItemsListTable.vue";
import PaymentDialog from "./editor/PaymentDialog.vue";
import ProductBrowserDialog from "@/components/ProductBrowserDialog.vue";
import CheckBox from "@/components/CheckBox.vue";
import ItemEditorDialog from "./editor/ItemEditorDialog.vue";
import DigitalClock from "@/components/DigitalClock.vue";
import SupplierAutocomplete from "@/components/SupplierAutocomplete.vue";
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
import BarcodeInputEditor from "@/components/BarcodeInputEditor.vue";
import PartyInfo from "@/components/PartyInfo.vue";

const $q = useQuasar();
const page = usePage();
const mergeItem = ref(true);
const userInputRef = ref(null);
const itemEditorRef = ref(null);
const supplierAutocompleteRef = ref(null);
const showHelpDialog = ref(false);
const authLayoutRef = ref(null);
const targetDiv = ref(null);
const { isFullscreen, toggleFullscreen } = useFullscreen(targetDiv);
const title = page.props.data.code;
const supplier = ref(page.props.data.supplier);
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
  code: page.props.data.code,
  supplier_id: page.props.data.supplier_id,
  datetime: new Date(page.props.data.datetime),
  status: page.props.data.status,
  payment_status: page.props.data.payment_status,
  delivery_status: page.props.data.delivery_status,
  notes: page.props.data.notes,
  items: page.props.data.details ?? [],
});

const total = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + item.cost * item.quantity;
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

const validateCost = (cost) => {
  if (isNaN(cost) || cost < 0) {
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
  if (userInput.value?.endsWith("*")) {
    userInput.value += product.name;
  } else {
    userInput.value = product.name;
  }
};

const addItem = async () => {
  if (isProcessing.value) {
    // prevent duplicate event
    return;
  }

  const input = userInput.value.trim();
  if (input.length === 0) {
    showProductBrowserDialog.value = true;
    return;
  }

  const parts = input.split("*");

  let inputQuantity = 1;
  let inputBarcode = "";
  let inputCost = null;

  if (parts.length === 1) {
    inputBarcode = parts[0];
  } else if (parts.length <= 3) {
    inputQuantity = parseInt(parts[0]);
    inputBarcode = parts[1];
    if (parts.length === 3) {
      inputCost = parseFloat(parts[2]);
    }
  } else {
    showWarning("Input tidak valid.", "top");
    return;
  }

  if (inputBarcode.length === 0) {
    showProductBrowserDialog.value = true;
    return;
  }

  if (
    !validateBarcode(inputBarcode) &&
    !validateQuantity(inputQuantity) &&
    !validateCost(inputCost)
  ) {
    return;
  }

  isProcessing.value = true;
  await axios
    .post(route("admin.purchase-order.add-item"), {
      order_id: form.id,
      product_code: inputBarcode,
      qty: inputQuantity,
      cost: inputCost,
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
      if (inputCost !== null && inputCost !== parseFloat(currentItem.cost)) {
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
      .post(route("admin.purchase-order.remove-item"), { id: item.id })
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
    cost: item.cost,
  };

  if (item.notes) {
    data.notes = item.notes;
  }

  axios
    .post(route("admin.purchase-order.update-item"), data)
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
      supplierAutocompleteRef.value.focus();
    } else if (e.key === "F3") {
      e.preventDefault();
      userInputRef.value?.focus();
    } else if (e.key === "F4") {
      e.preventDefault();
      mergeItem.value = !mergeItem.value;
    } else if (e.key === "F10") {
      e.preventDefault();
      showPaymentDialog.value = true;
    } else if (e.key === "F11") {
      e.preventDefault();
      handleFullScreenClicked();
    } else if (
      e.key === "F5" ||
      e.key === "F6" ||
      e.key === "F7" ||
      e.key === "F12"
    ) {
      e.preventDefault();
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

const handleSupplierSelected = async (data) => {
  supplier.value = data;
  form.supplier_id = data?.id;
  await updateOrder();

  if (data?.id) {
    userInputRef.value?.focus();
  } else {
    supplierAutocompleteRef.value.focus();
  }
};

const updateOrder = async () => {
  isProcessing.value = true;

  const data = {
    id: form.id,
    supplier_id: form.supplier_id,
    datetime: formatDateTimeForEditing(form.datetime),
    notes: form.notes,
  };

  await axios
    .post(route("admin.purchase-order.update"), data)
    .then((response) => {
      const updated = response.data.data.id;
      form.supplier_id = updated.supplier_id;
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
    .post(route("admin.purchase-order.close"), payload)
    .then((response) => {
      showInfo("Transaksi selesai");
      router.visit(route("admin.purchase-order.detail", { id: form.id }));
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
        route("admin.purchase-order.cancel", {
          id: form.id,
        }),
        { id: form.id }
      )
      .then(() => {
        showInfo("Transaksi telah dibatalkan.");
        router.visit(route("admin.purchase-order.detail", { id: form.id }));
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
    route("admin.purchase-order.detail", { id: form.id }) + "?preview=1",
    "_blank"
  );
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
          @click="router.get(route('admin.purchase-order.index'))"
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
          v-if="false"
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
    <q-page class="bg-grey-2 column fit">
      <q-card square flat class="full-width col column">
        <div class="row q-col-gutter-none full-width">
          <div class="col-sm-6 col-12 col q-pa-sm">
            <div class="row full-width">
              <SupplierAutocomplete
                ref="supplierAutocompleteRef"
                class="custom-select full-width col col-12 bg-white"
                v-model="supplier"
                label="Pemasok"
                :disable="isProcessing"
                @supplier-selected="handleSupplierSelected"
                :min-length="1"
                outlined
                autofocus
              />
              <PartyInfo
                v-if="supplier"
                :party="supplier"
                :is-valid-wallet-balance="true"
              />
            </div>
          </div>
          <div class="col-sm-6 col-12 q-pa-sm">
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
              class="col col-12 bg-white"
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
        <div class="row q-px-sm q-col-gutter-sm q-py-xs">
          <div class="col">
            <q-btn
              class="full-width"
              label="Bayar"
              color="primary"
              icon="payment"
              @click="showPaymentDialog = true"
              :disable="
                !$can('admin.purchase-order.close') ||
                isProcessing ||
                form.items.length === 0 ||
                form.status !== 'draft'
              "
              :loading="isProcessing"
            />
          </div>
          <div>
            <q-btn
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
        :supplier="supplier"
        :total="total"
        :accounts="page.props.accounts"
      />
      <ProductBrowserDialog
        v-model="showProductBrowserDialog"
        :show-cost="true"
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
        :supplier="supplier"
        :total="total"
        :payment="payment"
      />
    </q-page>
  </authenticated-layout>
</template>
