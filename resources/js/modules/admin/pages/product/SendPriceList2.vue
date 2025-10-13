<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const title = "Kirim Daftar Harga";

const page = usePage();

// Ref untuk menampung pesan yang dihasilkan, terikat ke Q-Input
const generatedMessage = ref("");

// --- MAPPING DATA AWAL (Untuk Lookup Detail) ---

// Map Produk: Memetakan ID ke seluruh objek produk (termasuk harga dan unit)
const allProductsMap = computed(() => {
  const map = new Map();
  // ASUMSI: Setiap produk memiliki field price_1, price_2, price_3, dan unit.
  page.props.products?.forEach((p) => {
    map.set(p.id, p);
  });
  return map;
});

// Map Pelanggan: Memetakan ID ke seluruh objek pelanggan (termasuk phone_number)
const allCustomersMap = computed(() => {
  const map = new Map();
  // ASUMSI: Setiap pelanggan memiliki field phone_number.
  page.props.customers?.forEach((c) => {
    map.set(c.id, c);
  });
  return map;
});

// --- Data Options untuk Q-Select ---

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
      label: `${p.name} (${p.sku || p.code || "ID: " + p.id})`,
    })) || []
);

const prices = [
  {
    value: "price_1",
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

// --- Data Options yang Akan Difilter ---
const filteredProducts = ref(allProductsOptions.value);
const filteredCustomers = ref(allCustomersOptions.value);

const form = useForm({
  price_types: [],
  products: [],
  customers: [],
});

// Transform data sebelum dikirim ke backend (hanya kirim ID)
form.transform((data) => ({
  ...data,
  products: data.products.map((product) => product.value),
  customers: data.customers.map((customer) => customer.value),
}));

// --- Helper Functions ---

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

// --- Logika Pembuatan Konten Daftar Harga (Computed - PLAIN TEXT) ---

const plainTextContent = computed(() => {
  // Jika tidak ada produk atau jenis harga yang dipilih, kembalikan pesan placeholder
  if (form.products.length === 0 || form.price_types.length === 0) {
    return "Silakan pilih Produk dan Jenis Harga untuk membuat daftar harga.";
  }

  let textContent = "Daftar Harga Terbaru\n";
  textContent += `Tanggal: ${new Date().toLocaleDateString("id-ID")}\n`;
  textContent += "------------------------------\n\n"; // Pemisah

  // Iterasi produk yang dipilih (hanya objek {value, label})
  form.products.forEach((selectedProduct) => {
    // Cari detail produk lengkap dari Map
    const productDetail = allProductsMap.value.get(selectedProduct.value);

    if (productDetail) {
      // Menggunakan * untuk bold di WhatsApp
      textContent += `*${productDetail.name}*\n`;

      // Iterasi jenis harga yang dipilih
      form.price_types.forEach((priceKey) => {
        const priceLabel = getPriceLabel(priceKey);
        // Ambil nilai harga dari detail produk menggunakan priceKey
        const priceValue = productDetail[priceKey];
        const unit = productDetail.unit || "pcs"; // Default unit

        if (
          priceValue !== undefined &&
          priceValue !== null &&
          priceValue != 0
        ) {
          textContent += `- ${priceLabel}: ${formatRupiah(
            priceValue
          )} / ${unit}\n`;
        }
      });
      textContent += "\n";
    }
  });

  textContent += "------------------------------\n";
  textContent +=
    "Terima kasih atas perhatiannya. Harga dapat berubah sewaktu-waktu tanpa pemberitahuan.";

  return textContent;
});

// Sinkronisasi generatedMessage dengan plainTextContent setiap kali berubah
watch(
  plainTextContent,
  (newContent) => {
    generatedMessage.value = newContent;
  },
  { immediate: true }
);

// --- Logika WhatsApp ---

// Mengambil data pelanggan lengkap yang dipilih
const selectedCustomers = computed(() => {
  // Mapping ID yang dipilih kembali ke detail pelanggan
  return (
    form.customers
      .map((c) => allCustomersMap.value.get(c.value))
      // Filter pelanggan yang memiliki nomor telepon valid (asumsi field phone_number ada)
      .filter((c) => c && c.phone_number)
  );
});

// Fungsi untuk membuat link WhatsApp
const getWhatsAppLink = (phoneNumber) => {
  // Pesan sudah dalam bentuk plain text
  const plainText = generatedMessage.value;

  // Mengubah format nomor telepon (misal: dari 0812 ke 62812) dan membersihkan karakter non-digit
  let formattedPhone = phoneNumber.startsWith("0")
    ? "62" + phoneNumber.substring(1)
    : phoneNumber;
  formattedPhone = formattedPhone.replace(/[^0-9]/g, "");

  const encodedText = encodeURIComponent(plainText);
  return `https://wa.me/${formattedPhone}?text=${encodedText}`;
};

// --- Logic Filter `use-input` (tetap sama) ---
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

const scrollToFirstErrorField = () => {
  console.log("Scrolling to first error field...");
};

const submit = () => {
  if (
    form.price_types.length === 0 ||
    form.products.length === 0 ||
    form.customers.length === 0
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
    <div class="q-pa-xs">
      <q-form
        class="row q-col-gutter-md"
        @submit.prevent="submit"
        @validation-error="scrollToFirstErrorField"
      >
        <div class="col-12">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <div class="text-subtitle1 q-pt-md q-pb-sm">
                Pilih Data Kiriman
              </div>
              <div class="text-caption text-grey-7 q-pb-lg">
                Pilih produk, jenis harga, dan pelanggan tujuan.
              </div>

              <!-- Select Produk -->
              <q-select
                v-model="form.products"
                :options="filteredProducts"
                label="Pilih Produk"
                hint="Ketik nama atau SKU produk untuk mencari"
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

              <!-- Select Jenis Harga -->
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
              />

              <!-- Select Pelanggan -->
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
                label="Kirim ke Backend"
                color="primary"
                :disable="
                  form.processing ||
                  form.products.length === 0 ||
                  form.customers.length === 0 ||
                  form.price_types.length === 0
                "
                :loading="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="$goBack()"
              />
            </q-card-section>

            <q-banner
              v-if="$page.props.flash.success"
              class="bg-positive text-white q-mt-md"
            >
              {{ $page.props.flash.success }}
            </q-banner>
            <q-banner
              v-if="$page.props.flash.error"
              class="bg-negative text-white q-mt-md"
            >
              {{ $page.props.flash.error }}
            </q-banner>
          </q-card>
        </div>
        <div class="col-12">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 q-mb-sm">
                Pratinjau Pesan (Teks Biasa)
              </div>
              <!-- Q-Input Teks Area untuk menampilkan dan mengedit pesan -->
              <q-input
                v-model="generatedMessage"
                type="textarea"
                label="Pesan Daftar Harga"
                autogrow
                outlined
                dense
                :rows="10"
              />

              <div class="text-caption text-grey-7 q-mt-sm">
                Anda dapat mengedit teks di atas sebelum dikirim melalui
                WhatsApp.
              </div>
            </q-card-section>

            <q-card-section v-if="selectedCustomers.length > 0">
              <div class="text-subtitle2 q-mb-sm">
                Kirim ke Pelanggan (via WA)
              </div>

              <!-- Tombol WhatsApp untuk setiap pelanggan terpilih -->
              <div class="q-gutter-xs">
                <q-btn
                  v-for="customer in selectedCustomers"
                  :key="customer.id"
                  :label="`Kirim ke ${customer.label}`"
                  :href="getWhatsAppLink(customer.phone_number)"
                  target="_blank"
                  icon="lab la-whatsapp"
                  color="green-8"
                  dense
                >
                  <q-tooltip v-if="!customer.phone_number">
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
  </authenticated-layout>
</template>
