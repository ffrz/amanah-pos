<script setup>
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const title = "Kirim Daftar Harga";
const page = usePage();
const generatedMessage = ref("");
const messageInputRef = ref(null);

const allProductsMap = computed(() => {
  const map = new Map();
  page.props.products?.forEach((p) => {
    map.set(p.id, p);
  });
  return map;
});

// hanya customer yang punya field phone yang ditampilkan
const allCustomersMap = computed(() => {
  const map = new Map();
  page.props.customers
    ?.filter((c) => c.phone && c.phone.trim() !== "")
    .forEach((c) => {
      map.set(c.id, c);
    });
  return map;
});

const allCustomersOptions = computed(
  () =>
    page.props.customers?.map((c) => ({
      value: c.id,
      label: c.name,
    })) || []
);

const allProductsOptions = computed(
  () =>
    page.props.products?.map((p) => ({
      value: p.id,
      label: `${p.name}`,
    })) || []
);

const prices = [
  {
    value: "price",
    label: "Harga Eceran",
  },
  {
    value: "price_2",
    label: "Harga Partai",
  },
  {
    value: "price_3",
    label: "Harga Grosir",
  },
];

const filteredProducts = ref(allProductsOptions.value);
const filteredCustomers = ref(allCustomersOptions.value);

const form = useForm({
  price_types: [],
  products: [],
  customers: [],
});

form.transform((data) => ({
  // ...data,
  // products: data.products.map((product) => product.value),
  customers: data.customers.map((customer) => customer.value),
  message: generatedMessage.value,
}));

const formatRupiah = (number) => {
  if (!number) return "Rp. 0";
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(number);
};

const getPriceLabel = (value) => {
  const price = prices.find((p) => p.value === value);
  return price ? price.label : value;
};

const selectAllText = () => {
  if (messageInputRef.value) {
    const textarea = messageInputRef.value.$el.querySelector("textarea");
    if (textarea) {
      textarea.select();
      try {
        document.execCommand("copy");
        console.log("Teks berhasil disalin!");
      } catch (err) {
        console.error("Gagal menyalin teks ke clipboard:", err);
      }
    }
  }
};

const plainTextContent = computed(() => {
  if (form.products?.length === 0 || form.price_types?.length === 0) {
    return "Silakan pilih Produk dan Jenis Harga untuk membuat daftar harga.";
  }

  // TODO: pindahkan ke template
  let textContent = "*DAFTAR HARGA TERBARU*\n";
  textContent += `Tanggal: ${new Date().toLocaleDateString("id-ID")}\n`;
  textContent += "------------------------------\n\n";

  (form.products || []).forEach((selectedProduct) => {
    const productDetail = allProductsMap.value.get(selectedProduct.value);

    if (productDetail) {
      textContent += `*${productDetail.name}*\n`;

      (form.price_types || []).forEach((priceKey) => {
        const priceLabel = getPriceLabel(priceKey);
        const priceValue = productDetail[priceKey];
        const uom = productDetail.uom || "pcs";

        if (priceValue !== undefined && priceValue !== null && priceValue > 0) {
          textContent += `- ${priceLabel}: *${formatRupiah(
            priceValue
          )}* / ${uom}\n`;
        }
      });
      textContent += "\n";
    }
  });

  textContent += "------------------------------\n";
  // TODO: pindahkan ke template
  textContent +=
    "Terima kasih atas perhatiannya. Harga dapat berubah sewaktu-waktu tanpa pemberitahuan.";

  return textContent;
});

watch(
  plainTextContent,
  (newContent) => {
    generatedMessage.value = newContent;
  },
  { immediate: true }
);

const selectedCustomers = computed(() => {
  return (form.customers || [])
    .map((c) => allCustomersMap.value.get(c.value))
    .filter((c) => c && c.phone);
});

const getWhatsAppLink = (phoneNumber) => {
  const plainText = generatedMessage.value;

  let formattedPhone = phoneNumber.startsWith("0")
    ? "62" + phoneNumber.substring(1)
    : phoneNumber;
  formattedPhone = formattedPhone.replace(/[^0-9]/g, "");

  const encodedText = encodeURIComponent(plainText);
  return `https://wa.me/${formattedPhone}?text=${encodedText}`;
};

const filterProducts = (val, update) => {
  if (val === "") {
    update(() => {
      filteredProducts.value = allProductsOptions.value;
    });
    return;
  }

  update(() => {
    const needle = val.toLowerCase();
    filteredProducts.value = allProductsOptions.value.filter(
      (v) => v.label.toLowerCase().indexOf(needle) > -1
    );
  });
};

const filterCustomers = (val, update) => {
  if (val === "") {
    update(() => {
      filteredCustomers.value = allCustomersOptions.value;
    });
    return;
  }

  update(() => {
    const needle = val.toLowerCase();
    filteredCustomers.value = allCustomersOptions.value.filter(
      (v) => v.label.toLowerCase().indexOf(needle) > -1
    );
  });
};

const submit = () => {
  if (
    form.price_types?.length === 0 ||
    form.products?.length === 0 ||
    form.customers?.length === 0
  ) {
    console.error("Harap lengkapi Produk, Pelanggan, dan Jenis Harga.");
    return;
  }

  form.post(route("admin.product.send-price-list"), {
    onSuccess: () => {
      // Form tidak direset agar pesan yang sudah digenerate tetap terlihat
    },
    onError: () => {
      scrollToFirstErrorField();
    },
    onFinish: () => {
      form.clearErrors();
    },
  });
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$goBack()"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-8 q-pa-xs">
        <q-form
          class="row q-col-gutter-xs"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <div class="col-12 col-md-6">
            <q-card square flat bordered class="col full-width">
              <q-card-section class="q-pt-none">
                <div class="text-subtitle1 q-pt-md q-pb-sm">
                  Pilih Data Kiriman
                </div>
                <div class="text-caption text-grey-7 q-pb-lg">
                  Pilih produk, jenis harga, dan pelanggan tujuan.
                </div>

                <q-select
                  v-model="form.products"
                  :options="filteredProducts"
                  label="Pilih Produk"
                  hint="Ketik nama produk untuk mencari"
                  multiple
                  use-chips
                  clearable
                  outlined
                  dense
                  class="q-mb-md"
                  :error="!!form.errors.products"
                  :error-message="form.errors.products"
                  :loading="form.processing"
                  use-input
                  @filter="filterProducts"
                  @clear="form.products = []"
                  input-debounce="0"
                >
                  <template v-slot:no-option>
                    <q-item>
                      <q-item-section class="text-grey">
                        Tidak ada produk ditemukan
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>

                <q-select
                  v-model="form.price_types"
                  :options="prices"
                  label="Pilih Jenis Harga"
                  hint="Pilih satu atau lebih jenis harga"
                  emit-value
                  map-options
                  outlined
                  dense
                  class="q-mb-md"
                  :error="!!form.errors.price_types"
                  :error-message="form.errors.price_types"
                  :loading="form.processing"
                  multiple
                  use-chips
                  clearable
                  @clear="form.price_types = []"
                />

                <q-select
                  v-model="form.customers"
                  :options="filteredCustomers"
                  label="Pilih Pelanggan Tujuan"
                  hint="Ketik nama pelanggan untuk mencari"
                  multiple
                  use-chips
                  clearable
                  outlined
                  dense
                  class="q-mb-md"
                  :error="!!form.errors.customers"
                  :error-message="form.errors.customers"
                  :loading="form.processing"
                  use-input
                  @filter="filterCustomers"
                  @clear="form.customers = []"
                  input-debounce="0"
                >
                  <template v-slot:no-option>
                    <q-item>
                      <q-item-section class="text-grey">
                        Tidak ada pelanggan ditemukan
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
              </q-card-section>

              <q-card-section class="q-gutter-sm">
                <q-btn
                  icon="send"
                  type="submit"
                  label="Kirim via WA Gateway"
                  color="primary"
                  :disable="
                    form.processing ||
                    form.products?.length === 0 ||
                    form.customers?.length === 0 ||
                    form.price_types?.length === 0
                  "
                  :loading="form.processing"
                />
                <!-- <q-btn
                  icon="cancel"
                  label="Batal"
                  :disable="form.processing"
                  @click="$goBack()"
                /> -->
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-md-6">
            <q-card square flat bordered class="col full-width">
              <q-card-section>
                <div
                  class="text-subtitle1 q-mb-sm flex items-center justify-between"
                >
                  Pratinjau Pesan (Teks Biasa)
                  <q-btn
                    label="Salin & Pilih Semua"
                    icon="content_copy"
                    color="grey-7"
                    size="sm"
                    flat
                    dense
                    @click="selectAllText"
                    :disable="generatedMessage.length === 0"
                  />
                </div>

                <q-input
                  v-model="generatedMessage"
                  ref="messageInputRef"
                  type="textarea"
                  label="Pesan Daftar Harga"
                  autogrow
                  outlined
                  dense
                  :rows="10"
                />

                <!-- <div class="text-caption text-grey-7 q-mt-sm">
                  Pesan di atas akan dienkode dan dikirim melalui WhatsApp.
                </div> -->
              </q-card-section>

              <q-card-section
                v-if="selectedCustomers.length > 0"
                class="q-pt-none"
              >
                <div class="text-subtitle2 q-mb-sm">
                  Kirim ke Pelanggan (via WA)
                </div>

                <div class="q-gutter-sm">
                  <q-btn
                    v-for="customer in selectedCustomers"
                    :key="customer.id"
                    :label="`Kirim ke ${customer.name}`"
                    :href="getWhatsAppLink(customer.phone)"
                    target="_blank"
                    icon="lab la-whatsapp"
                    color="green-8"
                    class="full-width"
                    :disable="
                      !(form.products.length > 0 && form.price_types.length > 0)
                    "
                  >
                    <q-tooltip v-if="!customer.phone">
                      Nomor telepon tidak tersedia
                    </q-tooltip>
                  </q-btn>
                </div>
                <div class="text-caption text-grey-7 q-mt-sm">
                  Link WhatsApp akan terbuka di tab baru.
                </div>
              </q-card-section>
            </q-card>
          </div>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
