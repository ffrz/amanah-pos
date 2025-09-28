<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { formatNumber } from "@/helpers/formatter";
import { getQueryParams } from "@/helpers/utils";
import { handleFetchItems } from "@/helpers/client-req-handler";
import LongTextView from "./LongTextView.vue";

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
});

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

const columns = [
  { name: "name", label: "Nama", field: "name", align: "left", sortable: true },
  { name: "stock", label: "Stok", field: "stock", align: "right" },
  { name: "cost", label: "Modal (Rp)", field: "cost", align: "right" },
  { name: "price", label: "Harga (Rp)", field: "price", align: "right" },
];

const computedColumns = computed(() => {
  if (props.showCost) {
    return columns;
  }
  return columns.filter((col) => col.name !== "cost");
});

onMounted(() => {
  fetchItemsWithoutProps();
});

const fetchItemsWithoutProps = () => {
  if (props.modelValue === false) return;
  fetchItems();
};

const fetchItems = (props = null) => {
  loading.value = true;
  selectedIndex.value = -1; // Reset selected index on new fetch

  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.product.data"),
    loading,
    tableRef,
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
  const listCount = rows.value.length;
  if (listCount === 0) return;

  if (event.key === "ArrowDown") {
    event.preventDefault();
    selectedIndex.value = (selectedIndex.value + 1) % listCount;
  } else if (event.key === "ArrowUp") {
    event.preventDefault();
    selectedIndex.value = (selectedIndex.value - 1 + listCount) % listCount;
  } else if (event.key === "Enter" && selectedIndex.value !== -1) {
    event.preventDefault();
    onProductSelect(rows.value[selectedIndex.value]);
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
                inactive: !props.row.active,
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
              </q-td>
              <q-td key="stock" :props="props" class="wrap-column text-right">
                {{
                  props.row.type == "stocked"
                    ? formatNumber(props.row.stock) + " " + props.row.uom
                    : "-"
                }}
              </q-td>
              <q-td
                v-if="showCost"
                key="cost"
                :props="props"
                class="wrap-column text-right"
              >
                {{ formatNumber(props.row.cost) }}
              </q-td>
              <q-td key="price" :props="props" class="wrap-column text-right">
                {{ formatNumber(props.row.price) }}
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
.selected-row {
  background-color: #e0e0e0;
}
</style>
