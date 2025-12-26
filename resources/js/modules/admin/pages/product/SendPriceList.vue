<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch, onMounted } from "vue";
import { useQuasar } from "quasar";
import WaPriceListDialog from "./components/WaPriceListDialog.vue";

const title = "Kirim Daftar Harga";
const page = usePage();

// --- STATE ---
const generatedMessage = ref("");
const selectedCategory = ref(null);
const selectedBrand = ref(null);
const showWaDialog = ref(false);
const filteredProducts = ref([]);
const filteredCustomers = ref([]);

const form = useForm({
  price_types: ["price_1"],
  products: [],
  customers: [],
});

// --- DATA MAPPING ---
const categoryOptions = computed(() => {
  const categories =
    page.props.categories?.map((c) => ({
      value: c.id,
      label: c.name,
    })) || [];
  return [{ value: null, label: "Semua Kategori" }, ...categories];
});

const allProductsMap = computed(() => {
  const map = new Map();
  page.props.products?.forEach((p) => map.set(p.id, p));
  return map;
});

const allCustomersOptions = computed(
  () =>
    page.props.customers?.map((c) => ({
      value: c.id,
      label: c.name,
      phone: c.phone,
    })) || []
);

const baseProductOptions = computed(() => {
  let products = page.props.products || [];
  if (selectedCategory.value) {
    products = products.filter((p) => p.category_id === selectedCategory.value);
  }
  if (selectedBrand.value) {
    products = products.filter((p) => p.brand_id === selectedBrand.value);
  }
  return products.map((p) => ({ value: p.id, label: p.name }));
});

const prices = [
  { value: "price_1", label: "Harga Eceran" },
  { value: "price_2", label: "Harga Partai" },
  { value: "price_3", label: "Harga Grosir" },
];

const brandOptions = computed(() => {
  const brands =
    page.props.brands?.map((b) => ({
      value: b.id,
      label: b.name,
    })) || [];
  return [{ value: null, label: "Semua Merk" }, ...brands];
});

// --- FORMATTERS ---
const getShortPriceLabel = (value) => {
  const map = {
    price_1: "Eceran",
    price_2: "Partai",
    price_3: "Grosir",
  };
  return map[value] || value;
};

const formatSimpleNumber = (number) => {
  if (!number) return "0";
  return new Intl.NumberFormat("id-ID").format(number);
};

const plainTextContent = computed(() => {
  if (form.products?.length === 0 || form.price_types?.length === 0) {
    return "Silakan pilih Produk dan Jenis Harga untuk membuat daftar harga.";
  }

  let textContent = "*DAFTAR HARGA TERBARU*\n";
  textContent += `Tgl: ${new Date().toLocaleDateString("id-ID")}\n`;
  textContent += "------------------------------\n\n";

  form.products.forEach((selected) => {
    const p = allProductsMap.value.get(selected.value);
    if (!p) return;

    textContent += `*${p.name.toUpperCase()}*\n`;

    const baseUomName = p.uom ? p.uom.toLowerCase().trim() : "";

    form.price_types.forEach((type) => {
      const priceLines = [];

      const baseVal = parseFloat(p[type]);
      if (baseVal > 0) {
        priceLines.push(`${formatSimpleNumber(baseVal)} / ${p.uom}`);
      }

      if (p.product_units?.length > 0) {
        p.product_units.forEach((unit) => {
          const unitUomName = unit.name ? unit.name.toLowerCase().trim() : "";
          if (unitUomName === baseUomName) return;

          const unitVal = parseFloat(unit[type]);
          if (unitVal > 0) {
            priceLines.push(`${formatSimpleNumber(unitVal)} / ${unit.name}`);
          }
        });
      }

      if (priceLines.length > 0) {
        textContent += `${getShortPriceLabel(type)}: ${priceLines.join(
          ", "
        )}\n`;
      }
    });

    textContent += "\n";
  });

  textContent += "------------------------------\n";
  textContent += "Harga dapat berubah sewaktu-waktu.";

  return textContent;
});

watch(
  plainTextContent,
  (newVal) => {
    generatedMessage.value = newVal;
  },
  { immediate: true }
);

// AREA UBAHAN: Menghilangkan item yang sudah terpilih saat filter kategori/brand berubah
watch([selectedCategory, selectedBrand], () => {
  const selectedIds = form.products.map((p) => p.value);
  filteredProducts.value = baseProductOptions.value.filter(
    (v) => !selectedIds.includes(v.value)
  );
});

// AREA UBAHAN: Watch form.products agar daftar dropdown terupdate saat item dihapus dari chip
watch(
  () => form.products,
  (newVal) => {
    const selectedIds = newVal.map((p) => p.value);
    filteredProducts.value = baseProductOptions.value.filter(
      (v) => !selectedIds.includes(v.value)
    );
  },
  { deep: true }
);

// --- FILTER & SUBMIT ---
const filterProducts = (val, update) => {
  update(() => {
    const needle = val.toLowerCase();
    const selectedIds = form.products.map((p) => p.value); // Ambil ID yang sudah terpilih

    // AREA UBAHAN: Tambahkan kondisi !selectedIds.includes(v.value)
    filteredProducts.value = baseProductOptions.value.filter(
      (v) =>
        v.label.toLowerCase().includes(needle) && !selectedIds.includes(v.value)
    );
  });
};

const filterCustomers = (val, update) => {
  update(() => {
    const needle = val.toLowerCase();
    filteredCustomers.value = allCustomersOptions.value.filter((v) =>
      v.label.toLowerCase().includes(needle)
    );
  });
};

const submit = () => {
  showWaDialog.value = true;
};

onMounted(() => {
  // AREA UBAHAN: Inisialisasi awal dengan membuang item yang mungkin sudah ada di form.products
  const selectedIds = form.products.map((p) => p.value);
  filteredProducts.value = baseProductOptions.value.filter(
    (v) => !selectedIds.includes(v.value)
  );
  filteredCustomers.value = allCustomersOptions.value.filter((v) => v);
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <q-btn
        icon="arrow_back"
        dense
        color="grey-7"
        flat
        rounded
        @click="router.get(route('admin.product.index'))"
      />
    </template>

    <q-page class="row justify-center bg-grey-2">
      <div class="col col-md-8 q-pa-xs">
        <q-form class="row q-col-gutter-xs" @submit.prevent="submit">
          <div class="col-12 col-md-6">
            <q-card square flat bordered class="col full-width">
              <q-card-section class="q-pt-none">
                <div
                  class="text-subtitle1 q-pt-md q-pb-sm text-bold text-primary"
                >
                  Pilih Data Kiriman
                </div>
                <div class="text-caption text-grey-7 q-pb-lg">
                  Pilih produk, harga, dan pelanggan tujuan.
                </div>

                <div class="q-gutter-y-sm">
                  <q-select
                    v-model="selectedBrand"
                    :options="brandOptions"
                    label="Filter Merk"
                    outlined
                    dense
                    emit-value
                    map-options
                    hide-bottom-space
                  />

                  <q-select
                    v-model="selectedCategory"
                    :options="categoryOptions"
                    label="Filter Kategori"
                    outlined
                    dense
                    emit-value
                    map-options
                  />

                  <q-select
                    v-model="form.products"
                    :options="filteredProducts"
                    label="Pilih Produk"
                    multiple
                    use-chips
                    outlined
                    dense
                    use-input
                    @filter="filterProducts"
                    virtual-scroll
                  >
                    <template v-slot:option="scope">
                      <q-item
                        v-bind="scope.itemProps"
                        dense
                        class="q-py-none q-px-sm"
                      >
                        <q-item-section>
                          <q-item-label class="text-caption">
                            {{ scope.opt.label }}
                          </q-item-label>
                        </q-item-section>
                      </q-item>
                    </template>
                  </q-select>

                  <q-select
                    v-model="form.price_types"
                    :options="prices"
                    label="Jenis Harga"
                    multiple
                    use-chips
                    outlined
                    dense
                    emit-value
                    map-options
                  >
                    <template v-slot:option="scope">
                      <q-item
                        v-bind="scope.itemProps"
                        dense
                        class="q-py-none q-px-sm"
                      >
                        <q-item-section side>
                          <q-checkbox
                            dense
                            :model-value="scope.selected"
                            @update:model-value="scope.toggleOption(scope.opt)"
                          />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label class="text-caption">
                            {{ scope.opt.label }}
                          </q-item-label>
                        </q-item-section>
                      </q-item>
                    </template>
                  </q-select>

                  <q-select
                    v-model="form.customers"
                    :options="filteredCustomers"
                    label="Pilih Pelanggan"
                    multiple
                    use-chips
                    outlined
                    dense
                    use-input
                    @filter="filterCustomers"
                  >
                    <template v-slot:option="scope">
                      <q-item
                        v-bind="scope.itemProps"
                        dense
                        class="q-py-none q-px-sm"
                      >
                        <q-item-section side>
                          <q-checkbox
                            dense
                            :model-value="scope.selected"
                            @update:model-value="scope.toggleOption(scope.opt)"
                          />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label class="text-caption">
                            {{ scope.opt.label }}
                          </q-item-label>
                        </q-item-section>
                      </q-item>
                    </template>
                  </q-select>
                </div>
              </q-card-section>

              <q-card-section>
                <q-btn
                  icon="send"
                  type="submit"
                  label="Kirim Manual"
                  color="primary"
                  class="full-width"
                  unelevated
                  :loading="form.processing"
                  :disable="
                    form.products.length === 0 || form.customers.length === 0
                  "
                />
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-md-6">
            <q-card square flat bordered class="col full-width">
              <q-card-section>
                <div class="text-subtitle1 q-mb-sm text-bold">
                  Pratinjau Pesan
                </div>
                <q-input
                  v-model="generatedMessage"
                  type="textarea"
                  autogrow
                  outlined
                  dense
                  readonly
                  input-style="font-family: monospace; font-size: 12px; background: #fafafa"
                />
              </q-card-section>
              <q-card-section class="q-pt-none text-caption text-grey-7 italic">
                Pesan di atas akan muncul kembali di dialog konfirmasi sebelum
                dikirim.
              </q-card-section>
            </q-card>
          </div>
        </q-form>
      </div>
    </q-page>

    <WaPriceListDialog
      v-model="showWaDialog"
      :message="generatedMessage"
      :customers="form.customers"
    />
  </authenticated-layout>
</template>
