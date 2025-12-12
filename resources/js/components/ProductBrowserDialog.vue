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

const showCost = ref(props.showCost);
const showCostEnabled = ref(props.showCost);

// Emit event
const emit = defineEmits(["update:modelValue", "product-selected"]);

const tableRef = ref(null);
const rows = ref([]);
const loading = ref(true);
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

// Update Definisi Kolom agar lebih ringkas & informatif
const columns = [
  {
    name: "name",
    label: "Nama Produk",
    field: "name",
    align: "left",
    sortable: true,
  },
  { name: "stock", label: "Stok", field: "stock", align: "left" }, // Align left utk breakdown text
  { name: "cost", label: "Modal", field: "cost", align: "right" },
  { name: "price_1", label: "Eceran", field: "price_1", align: "right" },
  { name: "price_2", label: "Partai", field: "price_2", align: "right" },
  { name: "price_3", label: "Grosir", field: "price_3", align: "right" },
];

const computedColumns = computed(() => {
  // handle desktop
  if ($q.screen.gt.sm) {
    if (!showCost.value) {
      return columns.filter((col) => col.name !== "cost");
    }
    return columns;
  }
  // layar kecil
  return columns.filter((col) => col.name === "name");
});

// --- HELPER LOGIC ---

// Helper Hitung Modal Unit
const getUnitCost = (row, unit) => {
  if (unit.cost && parseFloat(unit.cost) > 0) return parseFloat(unit.cost);
  return parseFloat(row.cost) * parseFloat(unit.conversion_factor);
};

// Helper Fallback Display Harga (Sama dengan Index.vue)
const getPriceDisplay = (p1, p2, p3) => {
  const price1 = parseFloat(p1) || 0;
  let price2 = parseFloat(p2) || 0;
  let price3 = parseFloat(p3) || 0;

  const isP2Fallback = price2 === 0;
  if (isP2Fallback) price2 = price1;

  const isP3Fallback = price3 === 0;
  if (isP3Fallback) price3 = price2;

  return {
    p1: { val: price1, isFallback: false },
    p2: { val: price2, isFallback: isP2Fallback },
    p3: { val: price3, isFallback: isP3Fallback },
  };
};

onMounted(() => {
  fetchItemsWithoutProps();
});

// --- KEYBOARD NAVIGATION LOGIC (SAMA SEPERTI SEBELUMNYA) ---
const scrollToSelectedIndex = () => {
  if (tableRef.value && selectedIndex.value !== -1) {
    const firstIndexOnPage =
      (pagination.value.page - 1) * pagination.value.rowsPerPage;
    const localIndex = selectedIndex.value - firstIndexOnPage;
    nextTick(() => {
      if (
        localIndex >= 0 &&
        localIndex < rows.value.length &&
        tableRef.value.scrollTo
      ) {
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
  const initialIndex = selectedIndex.value;
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.product.data"), // Pastikan ini mengarah ke controller yang sudah diupdate (dengan stock_breakdown & productUnits)
    loading,
    tableRef,
    onSuccess: () => {
      if (rows.value.length > 0) {
        if (initialIndex === 0 || initialIndex === -1) {
          selectedIndex.value = 0;
        }
        nextTick(() => {
          scrollToSelectedIndex();
        });
      } else {
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
  setTimeout(() => {
    emit("update:modelValue", false);
  }, 100);
};

const handleKeydown = (event) => {
  if (!props.modelValue) return;

  const listCount = rows.value.length;
  if (listCount === 0) return;

  const firstIndexOnPage =
    (pagination.value.page - 1) * pagination.value.rowsPerPage;
  const lastIndexOnPage = firstIndexOnPage + listCount - 1;

  if (event.key === "ArrowDown") {
    event.preventDefault();
    if (selectedIndex.value < lastIndexOnPage) {
      selectedIndex.value = selectedIndex.value + 1;
      scrollToSelectedIndex();
    } else if (selectedIndex.value === lastIndexOnPage) {
      handleKeydown({ key: "ArrowRight", preventDefault: () => {} });
    }
  } else if (event.key === "ArrowUp") {
    event.preventDefault();
    if (selectedIndex.value > firstIndexOnPage) {
      selectedIndex.value = selectedIndex.value - 1;
      scrollToSelectedIndex();
    } else if (
      selectedIndex.value === firstIndexOnPage &&
      firstIndexOnPage > 0
    ) {
      handleKeydown({ key: "ArrowLeft", preventDefault: () => {} });
    }
  } else if (event.key === "Enter" && selectedIndex.value !== -1) {
    event.preventDefault();
    const localIndex = selectedIndex.value - firstIndexOnPage;
    if (localIndex >= 0 && localIndex < listCount) {
      onProductSelect(rows.value[localIndex]);
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
        selectedIndex.value = (nextPage - 1) * pagination.value.rowsPerPage;
        tableRef.value.requestServerInteraction();
      }
    } else if (event.key === "ArrowLeft") {
      event.preventDefault();
      const prevPage = pagination.value.page - 1;
      if (prevPage >= 1) {
        pagination.value.page = prevPage;
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
      class="column fit product-browser-card"
      tabindex="0"
      @keydown="handleKeydown"
      ref="cardRef"
    >
      <q-card-section class="q-py-sm bg-primary text-white">
        <div class="row items-center no-wrap">
          <div class="col text-subtitle1 text-bold">Cari Produk</div>
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
        <q-input
          autofocus
          outlined
          dense
          debounce="300"
          v-model="filter.search"
          placeholder="Ketik nama atau scan barcode..."
          clearable
          @update:model-value="onFilterChange"
        >
          <template v-slot:prepend>
            <q-icon name="search" />
          </template>
        </q-input>
        <q-checkbox
          v-if="showCostEnabled"
          v-model="showCost"
          label="Tampilkan Modal"
          style="margin-left: -10px"
          size="sm"
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
          <template v-slot:header="props">
            <q-tr :props="props">
              <q-th v-for="col in props.cols" :key="col.name" :props="props">
                {{ col.label }}
              </q-th>
            </q-tr>
          </template>

          <template v-slot:body="props">
            <q-tr
              tabindex="0"
              :props="props"
              :class="{
                lowstock:
                  ['stocked', 'raw_materials'].includes(props.row.type) &&
                  props.row.stock <= 0,
                'selected-row': selectedIndex === props.rowIndex,
              }"
              class="cursor-pointer"
              @click="onProductSelect(props.row)"
              @keydown.enter.prevent.stop="onProductSelect(props.row)"
              @keydown.space.prevent="onProductSelect(props.row)"
            >
              <q-td
                key="name"
                :props="props"
                class="wrap-column"
                :style="
                  $q.screen.lt.md
                    ? 'white-space: normal; min-width: 280px;'
                    : 'max-width: 250px; white-space: normal;'
                "
              >
                <template v-if="$q.screen.gt.sm">
                  <div class="text-weight-bold">{{ props.row.name }}</div>
                  <div class="text-caption text-grey-7">
                    <span v-if="props.row.barcode"
                      >[{{ props.row.barcode }}]
                    </span>
                    <span v-if="props.row.category">{{
                      props.row.category.name
                    }}</span>
                  </div>
                </template>

                <template v-else>
                  <div class="text-weight-bold text-body2">
                    {{ props.row.name }}
                  </div>
                  <div class="text-caption text-grey-8 q-mb-xs">
                    <span v-if="props.row.barcode" class="q-mr-xs"
                      ><q-icon name="qr_code" /> {{ props.row.barcode }}</span
                    >
                    <span v-if="props.row.category"
                      ><q-icon name="category" />
                      {{ props.row.category.name }}</span
                    >
                  </div>

                  <div
                    v-if="props.row.type == 'stocked'"
                    class="q-mb-sm bg-blue-1 q-pa-xs rounded-borders"
                  >
                    <div
                      class="text-weight-bold text-primary"
                      style="font-size: 0.85rem"
                    >
                      Stok: {{ props.row.stock_breakdown_text }}
                    </div>
                    <div
                      v-if="props.row.product_units.length > 0"
                      class="text-caption text-grey-7"
                      style="font-size: 10px"
                    >
                      (Total: {{ formatNumber(props.row.stock) }}
                      {{ props.row.uom }})
                    </div>
                  </div>

                  <div class="q-mt-xs">
                    <div
                      v-for="(unitItem, uIdx) in [
                        {
                          is_base: true,
                          name: props.row.uom,
                          conversion_factor: 1,
                        },
                        ...props.row.product_units,
                      ]"
                      :key="uIdx"
                      class="q-mb-sm dashed-bottom q-pb-xs"
                    >
                      <div class="row items-center q-mb-xs">
                        <div class="col text-weight-bold text-grey-9">
                          {{ unitItem.name }}
                          <span
                            v-if="!unitItem.is_base"
                            class="text-caption text-grey-6 font-weight-normal"
                            >(x{{
                              formatNumber(unitItem.conversion_factor)
                            }})</span
                          >
                          <span
                            v-else
                            class="text-caption text-blue-8 font-weight-normal"
                            >(Dasar)</span
                          >
                        </div>
                        <div
                          v-if="showCost"
                          class="col-auto text-caption text-red-8"
                        >
                          Modal:
                          {{
                            formatNumber(
                              unitItem.is_base
                                ? props.row.cost
                                : getUnitCost(props.row, unitItem)
                            )
                          }}
                        </div>
                      </div>

                      <div class="row q-col-gutter-xs text-caption">
                        <div class="col-4">
                          <div class="text-grey-6" style="font-size: 10px">
                            Eceran
                          </div>
                          <div class="text-weight-medium">
                            {{
                              formatNumber(
                                unitItem.is_base
                                  ? props.row.price_1
                                  : unitItem.price_1
                              )
                            }}
                          </div>
                        </div>

                        <div class="col-4">
                          <div class="text-grey-6" style="font-size: 10px">
                            Partai
                          </div>
                          <template
                            v-for="disp in [
                              getPriceDisplay(
                                unitItem.is_base
                                  ? props.row.price_1
                                  : unitItem.price_1,
                                unitItem.is_base
                                  ? props.row.price_2
                                  : unitItem.price_2,
                                unitItem.is_base
                                  ? props.row.price_3
                                  : unitItem.price_3
                              ),
                            ]"
                          >
                            <div
                              :class="
                                disp.p2.isFallback
                                  ? 'text-grey-5 text-italic'
                                  : 'text-weight-medium'
                              "
                            >
                              {{ formatNumber(disp.p2.val) }}
                            </div>
                          </template>
                        </div>

                        <div class="col-4">
                          <div class="text-grey-6" style="font-size: 10px">
                            Grosir
                          </div>
                          <template
                            v-for="disp in [
                              getPriceDisplay(
                                unitItem.is_base
                                  ? props.row.price_1
                                  : unitItem.price_1,
                                unitItem.is_base
                                  ? props.row.price_2
                                  : unitItem.price_2,
                                unitItem.is_base
                                  ? props.row.price_3
                                  : unitItem.price_3
                              ),
                            ]"
                          >
                            <div
                              :class="
                                disp.p3.isFallback
                                  ? 'text-grey-5 text-italic'
                                  : 'text-weight-medium'
                              "
                            >
                              {{ formatNumber(disp.p3.val) }}
                            </div>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </q-td>

              <q-td key="stock" :props="props" class="wrap-column">
                <div v-if="props.row.type == 'stocked'">
                  <div
                    class="text-weight-bold text-primary"
                    style="font-size: 0.9em"
                  >
                    {{ props.row.stock_breakdown_text }}
                  </div>
                  <div class="text-caption text-grey-6" style="font-size: 10px">
                    (Total: {{ formatNumber(props.row.stock) }}
                    {{ props.row.uom }})
                  </div>
                </div>
                <div v-else>-</div>
              </q-td>

              <q-td
                key="cost"
                :props="props"
                class="text-right"
                v-if="showCost"
                style="vertical-align: top"
              >
                <div class="q-mb-xs">
                  <span class="text-weight-medium">{{
                    formatNumber(props.row.cost)
                  }}</span>
                  <span class="text-caption text-grey-6"
                    >/{{ props.row.uom }}</span
                  >
                </div>
                <div
                  v-for="unit in props.row.product_units"
                  :key="unit.id"
                  class="text-caption text-grey-9 q-mb-xs"
                >
                  {{ formatNumber(getUnitCost(props.row, unit)) }}
                  <span class="text-grey-6">/{{ unit.name }}</span>
                </div>
              </q-td>

              <q-td
                key="price_1"
                :props="props"
                class="text-right"
                style="vertical-align: top"
              >
                <div class="q-mb-xs">
                  <span class="text-weight-bold">{{
                    formatNumber(props.row.price_1)
                  }}</span>
                  <span class="text-caption text-grey-6"
                    >/{{ props.row.uom }}</span
                  >
                </div>
                <div
                  v-for="unit in props.row.product_units"
                  :key="unit.id"
                  class="text-caption text-grey-9 q-mb-xs"
                >
                  {{ formatNumber(unit.price_1) }}
                  <span class="text-grey-6">/{{ unit.name }}</span>
                </div>
              </q-td>

              <q-td
                key="price_2"
                :props="props"
                class="text-right"
                style="vertical-align: top"
              >
                <div class="q-mb-xs">
                  <template
                    v-for="display in [
                      getPriceDisplay(
                        props.row.price_1,
                        props.row.price_2,
                        props.row.price_3
                      ),
                    ]"
                  >
                    <span
                      :class="
                        display.p2.isFallback
                          ? 'text-grey-5 text-italic'
                          : 'text-weight-bold'
                      "
                    >
                      {{ formatNumber(display.p2.val) }}
                    </span>
                    <span class="text-caption text-grey-6"
                      >/{{ props.row.uom }}</span
                    >
                  </template>
                </div>
                <div
                  v-for="unit in props.row.product_units"
                  :key="unit.id"
                  class="text-caption text-grey-9 q-mb-xs"
                >
                  <template
                    v-for="uDisplay in [
                      getPriceDisplay(unit.price_1, unit.price_2, unit.price_3),
                    ]"
                  >
                    <span
                      :class="
                        uDisplay.p2.isFallback ? 'text-grey-5 text-italic' : ''
                      "
                    >
                      {{ formatNumber(uDisplay.p2.val) }}
                    </span>
                    <span class="text-grey-6">/{{ unit.name }}</span>
                  </template>
                </div>
              </q-td>

              <q-td
                key="price_3"
                :props="props"
                class="text-right"
                style="vertical-align: top"
              >
                <div class="q-mb-xs">
                  <template
                    v-for="display in [
                      getPriceDisplay(
                        props.row.price_1,
                        props.row.price_2,
                        props.row.price_3
                      ),
                    ]"
                  >
                    <span
                      :class="
                        display.p3.isFallback
                          ? 'text-grey-5 text-italic'
                          : 'text-weight-bold'
                      "
                    >
                      {{ formatNumber(display.p3.val) }}
                    </span>
                    <span class="text-caption text-grey-6"
                      >/{{ props.row.uom }}</span
                    >
                  </template>
                </div>
                <div
                  v-for="unit in props.row.product_units"
                  :key="unit.id"
                  class="text-caption text-grey-9 q-mb-xs"
                >
                  <template
                    v-for="uDisplay in [
                      getPriceDisplay(unit.price_1, unit.price_2, unit.price_3),
                    ]"
                  >
                    <span
                      :class="
                        uDisplay.p3.isFallback ? 'text-grey-5 text-italic' : ''
                      "
                    >
                      {{ formatNumber(uDisplay.p3.val) }}
                    </span>
                    <span class="text-grey-6">/{{ unit.name }}</span>
                  </template>
                </div>
              </q-td>
            </q-tr>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<style scoped>
.product-browser-card {
  width: 900px; /* Diperlebar agar 3 kolom harga muat */
  max-width: 95vw;
}

.selected-row td,
.selected-row {
  background-color: rgb(
    225,
    240,
    255
  ) !important; /* Warna seleksi lebih soft */
  /*border-bottom: 1px solid rgb(33, 150, 243) !important; /* Indikator garis bawah */
}

/* Hilangkan border kiri kanan seleksi agar bersih */
.selected-row td:first-child {
  border: 1px solid rgb(33, 150, 243) !important;
}

.lowstock {
  color: #d32f2f;
  background-color: #ffebee;
}
</style>
