<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed, nextTick } from "vue";
import { useQuasar } from "quasar";
import axios from "axios";

import TransactionHeader from "./editor/TransactionHeader.vue";
import ItemListTable from "./editor/ItemsListTable.vue";
import TransactionSummary from "./editor/TransactionSummary.vue";
import CustomerEditorDialog from "./editor/CustomerEditorDialog.vue";
import PaymentDialog from "./editor/PaymentDialog.vue";
import ProductBrowserDialog from "@/components/ProductBrowserDialog.vue";
import CheckBox from "@/components/CheckBox.vue";
import ItemEditorDialog from "./editor/ItemEditorDialog.vue";
import DigitalClock from "@/components/DigitalClock.vue";

const $q = useQuasar();
const page = usePage();
const mergeItem = ref(true);
const inputRef = ref(null);
const transactionSummaryRef = ref(null);
const itemEditorRef = ref(null);
const title = page.props.company.name;

const form = useForm({
  id: page.props.data.id,
  formatted_id: page.props.data.formatted_id,
  customer_id: page.props.data.customer_id,
  datetime: page.props.data.datetime,
  status: page.props.data.status,
  payment_status: page.props.data.payment_status,
  delivery_status: page.props.data.delivery_status,
  notes: page.props.data.notes,
  items: page.props.data.details ?? [],
});

const userInput = ref("");
const isProcessing = ref(false);
const showCustomerEditor = ref(false);
const showPaymentDialog = ref(false);
const showProductBrowserDialog = ref(false);
const showItemEditorDialog = ref(false);
const itemToEdit = ref(null);

const total = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + item.price * item.quantity;
  }, 0);
});

// helpers
const notify = (msg, color = null, pos = "top", icon = null) => {
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

// validations
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

// -----
const handleProductSelection = (product) => {
  userInput.value += product.name;
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
  nextTick(() => {
    inputRef.value?.focus();
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
          notify("Item telah dihapus");
        }
      })
      .catch((error) => {
        notify("Gagal menghapus item", "negative");
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
        notify("Item telah diperbarui");
      }
    })
    .catch((error) => {
      notify("Gagal memperbarui item", "negative");
      console.error(error);
    })
    .finally(() => {
      isProcessing.value = false;
    });
};

const handlePayment = () => {
  if (form.items.length === 0) {
    notify("Item masih kosong");
    return;
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
      <div class="row items-center" v-if="$q.screen.gt.sm">
        <div class="text-weight-bold">
          {{ page.props.auth.user.username }}
        </div>
        <div class="q-mx-sm">|</div>
        <div>
          <DigitalClock />
        </div>
      </div>
    </template>
    <q-page class="bg-grey-2 q-pa-sm column fit">
      <q-card square flat bordered class="full-width col column">
        <TransactionHeader
          :user="page.props.auth.user"
          :company="page.props.company"
          @edit-customer="showCustomerEditor = true"
        />
        <div class="row">
          <q-input
            ref="inputRef"
            :model-value="userInput"
            @update:model-value="(val) => $emit('update:barcode', val)"
            @keyup.enter.prevent="addItem()"
            :loading="isProcessing"
            :disable="isProcessing"
            placeholder="Qty * Kode / Barcode * Harga"
            class="col col-12 q-pa-sm bg-white"
            outlined
            clearable
            autofocus
            dense
          />
        </div>
        <div class="row col grow">
          <div class="col-12 q-pa-sm column">
            <ItemListTable
              :items="form.items"
              @update-quantity="({ id, value }) => updateQuantity(id, value)"
              @remove-item="removeItem"
              @edit-item="showItemEditor"
              :is-processing="isProcessing"
              :merge-item="mergeItem"
            />
            <div class="row">
              <div class="col">
                <CheckBox
                  v-model="mergeItem"
                  label="Gabungkan item"
                  :disable="isProcessing"
                />
              </div>
              <div class="col">
                <div class="text-caption text-grey-6 q-mt-xs text-right">
                  {{ form.items.length }} item(s)
                </div>
              </div>
            </div>
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
          <div class="col-12 col-xs-12 q-pa-sm">
            <TransactionSummary
              ref="transactionSummaryRef"
              v-model:barcode="userInput"
              :total="total"
              :item-count="form.items.length"
              :is-processing="isProcessing"
              :form-processing="form.processing"
              @add-item="addItem(userInput)"
              @process-payment="handlePayment"
              :is-product-browser-open="showProductBrowserDialog"
            />
          </div>
        </div>
      </q-card>

      <ItemEditorDialog
        ref="itemEditorRef"
        v-model="showItemEditorDialog"
        :item="itemToEdit"
        @save="updateItem"
        :is-processing="isProcessing"
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
