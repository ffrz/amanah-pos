<script setup>
import { router, usePage } from "@inertiajs/vue3";
import {
  ref,
  computed,
  nextTick,
  onMounted,
  onUnmounted,
  reactive,
  toRaw,
} from "vue";
import { useQuasar } from "quasar";
import axios from "axios";

import ItemListTable from "./editor/ItemsListTable.vue";
import PaymentDialog from "./editor/PaymentDialog.vue";
import ProductBrowserDialog from "@/components/ProductBrowserDialog.vue";
import CheckBox from "@/components/CheckBox.vue";
import ItemEditorDialog from "./editor/ItemEditorDialog.vue";
import DigitalClock from "@/components/DigitalClock.vue";
import CustomerAutocomplete from "@/components/CustomerAutocomplete.vue";
import { formatNumber } from "@/helpers/formatter";
import HelpDialog from "./editor/HelpDialog.vue";
import { useFullscreen } from "@/composables/useFullscreen";

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

const form = reactive({
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
const showPaymentDialog = ref(false);
const showProductBrowserDialog = ref(false);
const showItemEditorDialog = ref(false);
const itemToEdit = ref(null);

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
    showWarning("Input tidak valid.");
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
        showWarning("Harga tidak dapat diubah!");
      }
    })
    .catch((error) => {
      notify(error.response?.data?.message, "negative");
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

  const data = { ...toRaw(form) };
  delete data["items"];
  axios
    .post(route("admin.sales-order.update"), data)
    .then((response) => {
      const updated = response.data.data.id;
      form.customer_id = updated.customer_id;
    })
    .catch((error) => {
      notify("Terdapat kesalahan saat menyimpan!", "negative");
      console.error(error);
    })
    .finally(() => {
      isProcessing.value = false;
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
      <div class="row items-center" v-if="$q.screen.gt.sm">
        <div class="text-weight-bold">
          {{ page.props.auth.user.username }}
        </div>
        <div class="q-mx-sm">|</div>
        <div>
          <DigitalClock />
        </div>
        <q-btn
          class="q-ml-sm"
          v-if="$q.screen.gt.sm"
          :icon="isFullscreen ? 'fullscreen_exit' : 'fullscreen'"
          dense
          color="grey-7"
          flat
          rounded
          @click="handleFullScreenClicked()"
        />
      </div>
    </template>
    <q-page class="bg-grey-2 q-pa-sm column fit">
      <q-card square flat bordered class="full-width col column">
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
              <div v-if="customer" class="text-grey q-mt-xs q-ml-sm">
                Saldo: Rp.
                {{ formatNumber(customer ? customer.balance : 0) }}
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
            <div class="q-ml-sm">
              <CheckBox
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

        <div class="row q-px-sm q-pb-sm">
          <div class="col" v-if="$q.screen.gt.sm">
            <div class="text-caption text-grey-6 q-mt-xs">
              {{ form.items.length }} item(s)
            </div>
          </div>
          <div class="col">
            <div class="row justify-end items-center q-gutter-sm">
              <div
                class="col"
                style="
                  background: #eee;
                  padding: 10px;
                  text-align: right;
                  border: 1px solid #ddd;
                "
              >
                <span class="text-grey-8 text-subtitle-2" style="float: left">
                  Total
                </span>
                <span class="text-h4 text-weight-bold">
                  <sup style="font-size: 13px">Rp.</sup>
                  {{ formatNumber(total) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="false" class="row">
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
        </div>
      </q-card>

      <div class="row q-mt-sm">
        <q-btn
          class="full-width"
          label="Bayar"
          color="primary"
          icon="payment"
          @click="handlePayment()"
          :disable="isProcessing || form.items.length === 0"
          :loading="isProcessing"
        />
      </div>

      <ItemEditorDialog
        ref="itemEditorRef"
        v-model="showItemEditorDialog"
        :item="itemToEdit"
        @save="updateItem"
        :is-processing="isProcessing"
      />
      <PaymentDialog v-model="showPaymentDialog" />
      <ProductBrowserDialog
        v-model="showProductBrowserDialog"
        @product-selected="handleProductSelection"
      />
      <HelpDialog v-model="showHelpDialog" />
    </q-page>
  </authenticated-layout>
</template>
