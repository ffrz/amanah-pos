<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { formatNumber } from "@/helpers/formatter";
import { getQueryParams } from "@/helpers/utils";
import { handleFetchItems } from "@/helpers/client-req-handler";
import LongTextView from "./LongTextView.vue";
import { useQuasar } from "quasar";
import { createOptions } from "@/helpers/options";

const $q = useQuasar();

// Prop untuk mengontrol dialog
const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  showCost: {
    type: Boolean,
    require: false,
    default: false,
  },
  priceType: {
    type: String,
    require: false,
    default: "price_1",
  },
});

const showCost = ref(false);
const showCostEnabled = ref(props.showCost);

// Emit event
const emit = defineEmits(["update:modelValue", "product-selected"]);

const tableRef = ref(null);
const rows = ref([]);
const loading = ref(true);

// Ref untuk melacak item yang dipilih
const selectedIndex = ref(-1);

const filter = reactive({
  search: "",
  status: "active",
  ...getQueryParams(),
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "name",
  descending: false,
});

const priceOptions = [...createOptions(window.CONSTANTS.PRODUCT_PRICE_TYPES)];

const columns = [
  { name: "name", label: "Nama", field: "name", align: "left", sortable: true },
  { name: "stock", label: "Stok", field: "stock", align: "right" },
  { name: "cost", label: "Modal (Rp)", field: "cost", align: "right" },
  {
    name: "price_1",
    label: window.CONSTANTS.PRODUCT_PRICE_TYPES["price_1"].replace(
      "Harga ",
      "H "
    ),
    field: "price_1",
    align: "right",
  },
  {
    name: "price_2",
    label: window.CONSTANTS.PRODUCT_PRICE_TYPES["price_2"].replace(
      "Harga ",
      "H "
    ),
    field: "price_2",
    align: "right",
  },
  {
    name: "price_3",
    label: window.CONSTANTS.PRODUCT_PRICE_TYPES["price_3"].replace(
      "Harga ",
      "H "
    ),
    field: "price_3",
    align: "right",
  },
];

const computedColumns = computed(() => {
  if (showCost.value) {
    return columns;
  }

  const cols = columns.filter((col) => col.name !== "cost");

  if ($q.screen.gt.sm) return cols;

  return cols.filter((col) => col.name === "name" || col.name === "stock");
});

onMounted(() => {
  fetchItemsWithoutProps();
});

// Fungsi untuk melakukan scroll ke baris yang dipilih
const scrollToSelectedIndex = () => {
  if (tableRef.value && selectedIndex.value !== -1) {
    // 1. Hitung Indeks Global Baris Pertama di Halaman Saat Ini
    const firstIndexOnPage =
      (pagination.value.page - 1) * pagination.value.rowsPerPage;

    // 2. Konversi Indeks Global (selectedIndex) ke Indeks Lokal (localIndex)
    const localIndex = selectedIndex.value - firstIndexOnPage;

    // 3. Pastikan aksi scrolling terjadi setelah DOM diupdate (menggunakan nextTick)
    nextTick(() => {
      // 4. Periksa apakah indeks lokal valid dan metode scrollTo tersedia
      if (
        localIndex >= 0 &&
        localIndex < rows.value.length &&
        tableRef.value.scrollTo
      ) {
        // Panggil scrollTo menggunakan Indeks Lokal (0, 1, 2, ...)
        //tableRef.value.scrollTo(localIndex, "center");

        // Opsional: Untuk memastikan baris terpilih terlihat, fokuskan elemennya
        const rowElement = tableRef.value.$el.querySelector(`.selected-row`);
        if (rowElement) {
          rowElement.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }
      }
    });
  }
};

const fetchItemsWithoutProps = () => {
  if (props.modelValue === false) return;
  fetchItems();
};

const fetchItems = (props = null) => {
  loading.value = true;
  // selectedIndex.value = -1; // Reset selected index on new fetch
  const initialIndex = selectedIndex.value;
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.product.data"),
    loading,
    tableRef,
    onSuccess: () => {
      if (rows.value.length > 0) {
        // Jika index awalnya diatur ke 0 (dari handleKeydown) atau -1 (dari fetch awal),
        // kita set ke 0
        if (initialIndex === 0 || initialIndex === -1) {
          selectedIndex.value = 0;
        }

        // Gunakan nextTick untuk memastikan DOM telah diperbarui (termasuk kelas 'selected-row')
        // sebelum mencoba melakukan scroll
        nextTick(() => {
          scrollToSelectedIndex();
        });
      } else {
        // Jika halaman baru tidak memiliki data
        selectedIndex.value = -1;
      }
    },
  });
};

const onFilterChange = () => {
  fetchItemsWithoutProps();
};

const onProductSelect = (product) => {
  emit("product-selected", product);

  // nextTick(() => {
  setTimeout(() => {
    emit("update:modelValue", false);
  }, 100);
  // });
};

const handleKeydown = (event) => {
  if (!props.modelValue) {
    alert("none");
    return;
  }

  const listCount = rows.value.length;
  if (listCount === 0) return;

  const firstIndexOnPage =
    (pagination.value.page - 1) * pagination.value.rowsPerPage;
  const lastIndexOnPage = firstIndexOnPage + listCount - 1;

  if (event.key === "ArrowDown") {
    event.preventDefault();

    // Cek apakah seleksi saat ini BUKAN baris terakhir di halaman
    if (selectedIndex.value < lastIndexOnPage) {
      selectedIndex.value = selectedIndex.value + 1;
      scrollToSelectedIndex();
    }
    // 🔥 Tambahan: Izinkan pindah halaman jika ArrowDown ditekan pada baris terakhir
    else if (selectedIndex.value === lastIndexOnPage) {
      // Jika sudah di baris terakhir, panggil logika pindah halaman (ArrowRight)
      // Ini akan memicu paginasi dan mengatur selectedIndex ke baris pertama halaman baru
      handleKeydown({ key: "ArrowRight", preventDefault: () => {} });
    }
  } else if (event.key === "ArrowUp") {
    event.preventDefault();

    // Cek apakah seleksi saat ini BUKAN baris pertama di halaman
    if (selectedIndex.value > firstIndexOnPage) {
      selectedIndex.value = selectedIndex.value - 1;
      scrollToSelectedIndex();
    }
    // 🔥 Tambahan: Izinkan pindah halaman jika ArrowUp ditekan pada baris pertama
    else if (selectedIndex.value === firstIndexOnPage && firstIndexOnPage > 0) {
      // Jika sudah di baris pertama, panggil logika pindah halaman (ArrowLeft)
      // Ini akan memicu paginasi dan mengatur selectedIndex ke baris pertama halaman sebelumnya
      handleKeydown({ key: "ArrowLeft", preventDefault: () => {} });
    }
  } else if (event.key === "Enter" && selectedIndex.value !== -1) {
    event.preventDefault();
    event.preventDefault();

    // 1. Hitung Indeks Global Baris Pertama di Halaman Saat Ini
    const firstIndexOnPage =
      (pagination.value.page - 1) * pagination.value.rowsPerPage;

    // 2. Konversi Indeks Global ke Indeks Lokal (0-based)
    const localIndex = selectedIndex.value - firstIndexOnPage;

    // 3. Validasi dan Pilih Produk
    if (localIndex >= 0 && localIndex < listCount) {
      onProductSelect(rows.value[localIndex]);
    } else {
      console.warn(
        "Kesalahan: selectedIndex global di luar batas halaman saat ini."
      );
      // Opsi: Anda mungkin ingin memilih baris terdekat (baris 0) jika terjadi anomali.
      // onProductSelect(rows.value[0]);
    }
  } else if (selectedIndex.value !== -1) {
    if (event.key === "ArrowRight") {
      event.preventDefault();
      const nextPage = pagination.value.page + 1;
      if (
        nextPage <=
        Math.ceil(pagination.value.rowsNumber / pagination.value.rowsPerPage)
      ) {
        pagination.value.page = nextPage;

        // 🔥 PERBAIKAN: Hitung indeks global baris pertama halaman baru
        selectedIndex.value = (nextPage - 1) * pagination.value.rowsPerPage;

        tableRef.value.requestServerInteraction();
      }
    } else if (event.key === "ArrowLeft") {
      event.preventDefault();
      const prevPage = pagination.value.page - 1;
      if (prevPage >= 1) {
        pagination.value.page = prevPage;

        // 🔥 PERBAIKAN: Hitung indeks global baris pertama halaman sebelumnya
        selectedIndex.value = (prevPage - 1) * pagination.value.rowsPerPage;

        tableRef.value.requestServerInteraction();
      }
    }
  }
};

watch(
  () => props.modelValue,
  (newValue, oldValue) => {
    if (oldValue === true && newValue === false) {
      filter.search = "";
    }

    if (newValue === true) {
      fetchItems();
    }
  }
);
</script>
<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    :maximized="$q.screen.lt.sm"
  >
    <q-card
      class="column fit"
      tabindex="0"
      @keydown="handleKeydown"
      ref="cardRef"
    >
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-subtitle text-grey-8 text-bold">Cari Produk</div>
          <div class="col-auto">
            <q-btn
              flat
              round
              size="sm"
              icon="close"
              @click="$emit('update:modelValue', false)"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-py-sm" :class="$q.screen.lt.sm ? 'q-px-xs' : ''">
        <!-- Ini untuk fitur nanti kalau bisa customize harga per item transaksi -->
        <q-input
          autofocus
          outlined
          dense
          debounce="300"
          v-model="filter.search"
          placeholder="Cari produk berdasarkan nama atau barcode"
          clearable
          @update:model-value="onFilterChange"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
        <q-checkbox
          v-if="showCostEnabled"
          v-model="showCost"
          label="Tampilkan Modal"
          style="margin-left: -10px"
        />
      </q-card-section>

      <q-card-section
        class="q-py-xs col"
        :class="$q.screen.lt.sm ? 'q-px-xs' : ''"
      >
        <q-table
          ref="tableRef"
          flat
          class="fit full-height-table"
          square
          bordered
          dense
          color="primary"
          row-key="id"
          v-model:pagination="pagination"
          :filter="filter.search"
          :loading="loading"
          :columns="computedColumns"
          :rows="rows"
          :rows-per-page-options="[10, 25, 50]"
          @request="fetchItems"
          binary-state-sort
          no-data-label="Tidak ada produk ditemukan"
          :rows-per-page-label="null"
        >
          <template v-slot:no-data="{ icon, message, filter }">
            <div class="full-width row flex-center text-grey-8 q-gutter-sm">
              <span>{{ message }}</span>
            </div>
          </template>
          <template v-slot:body="props">
            <q-tr
              tabindex="0"
              :props="props"
              :class="{
                inactive:
                  ['stocked', 'raw_materials'].includes(props.row.type) &&
                  props.row.stock <= 0,
                'selected-row': selectedIndex === props.rowIndex,
              }"
              class="cursor-pointer"
              @click="onProductSelect(props.row)"
              @keydown.enter.prevent.stop="onProductSelect(props.row)"
              @keydown.space.prevent="onProductSelect(props.row)"
            >
              <q-td key="name" :props="props" class="wrap-column">
                <LongTextView :text="props.row.name" />
                <LongTextView
                  v-if="props.row.description"
                  :text="props.row.description"
                  :max-length="50"
                  class="text-grey-6"
                />
                <template v-if="$q.screen.lt.md">
                  <div>
                    Stok:
                    {{
                      props.row.type == "stocked"
                        ? formatNumber(props.row.stock) + " " + props.row.uom
                        : "-"
                    }}
                  </div>
                  <div v-if="showCost">
                    Modal: Rp. {{ formatNumber(props.row.cost) }}
                  </div>
                  <div>Harga 1: Rp. {{ formatNumber(props.row.price_1) }}</div>
                  <div>Harga 2: Rp. {{ formatNumber(props.row.price_1) }}</div>
                  <div>Harga 3: Rp. {{ formatNumber(props.row.price_1) }}</div>
                </template>
              </q-td>
              <q-td key="stock" :props="props" class="text-right">
                {{
                  props.row.type == "stocked"
                    ? formatNumber(props.row.stock) + " " + props.row.uom
                    : "-"
                }}
              </q-td>
              <q-td key="cost" :props="props" class="wrap-column text-right">
                {{ formatNumber(props.row.cost) }}
              </q-td>
              <q-td key="price_1" :props="props" class="wrap-column text-right">
                {{ formatNumber(props.row.price_1) }}
              </q-td>
              <q-td key="price_2" :props="props" class="wrap-column text-right">
                {{ formatNumber(props.row.price_2) }}
              </q-td>
              <q-td key="price_3" :props="props" class="wrap-column text-right">
                {{ formatNumber(props.row.price_3) }}
              </q-td>
            </q-tr>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<style>
.q-card {
  width: 360px;
}

.selected-row td,
.selected-row {
  background-color: rgb(255, 255, 118) !important;
}
</style>
