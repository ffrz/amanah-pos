<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { formatNumber } from "@/helpers/formatter";
import { getQueryParams } from "@/helpers/utils";
import { handleFetchItems } from "@/helpers/client-req-handler";

// Prop untuk mengontrol dialog
const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
});

// Emit event
const emit = defineEmits(["update:modelValue", "product-selected"]);

const tableRef = ref(null);
const rows = ref([]);
const loading = ref(true);

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
  { name: "price", label: "Harga", field: "price", align: "right" },
];

const computedColumns = computed(() => {
  return columns;
});

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) => {
  loading.value = true;

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
  fetchItems();
};

const onProductSelect = (product) => {
  emit("product-selected", product);
  emit("update:modelValue", false); // Tutup dialog setelah memilih produk
};

watch(
  () => props.modelValue,
  (newValue, oldValue) => {
    if (oldValue === true && newValue === false) {
      filter.search = "";
    } else {
      fetchItems();
    }
  }
);
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="(val) => $emit('update:modelValue', val)"
    :maximized="$q.screen.lt.md"
  >
    <q-card style="min-width: 600px; min-height: 400px">
      <q-card-section class="q-py-sm">
        <div class="row items-center no-wrap">
          <div class="col text-h6 text-grey-8">Cari Produk</div>
          <div class="col-auto">
            <q-btn
              flat
              round
              icon="close"
              @click="$emit('update:modelValue', false)"
            />
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-py-sm">
        <q-input
          autofocus
          outlined
          dense
          debounce="300"
          v-model="filter.search"
          placeholder="Cari produk berdasarkan nama atau kode"
          clearable
          @update:model-value="onFilterChange"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </q-card-section>

      <q-table
        ref="tableRef"
        flat
        bordered
        square
        color="primary"
        row-key="id"
        virtual-scroll
        v-model:pagination="pagination"
        :filter="filter.search"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
        @request="fetchItems"
        binary-state-sort
        no-data-label="Tidak ada produk ditemukan"
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
            :class="{ inactive: !props.row.active }"
            class="cursor-pointer"
            @click="onProductSelect(props.row)"
            @keydown.enter="onProductSelect(props.row)"
            @keydown.space.prevent="onProductSelect(props.row)"
          >
            <q-td key="name" :props="props" class="wrap-column">
              {{ props.row.name }}
              <div
                v-if="props.row.category_id"
                class="text-grey-8 text-caption"
              >
                <q-icon name="category" />
                {{ props.row.category.name }}
              </div>
              <div
                v-if="props.row.supplier_id"
                class="text-grey-8 text-caption"
              >
                <q-icon name="local_shipping" />
                {{ props.row.supplier.name }}
              </div>
            </q-td>
            <q-td key="stock" :props="props" class="wrap-column text-right">
              {{
                props.row.type == "stocked"
                  ? formatNumber(props.row.stock) + " " + props.row.uom
                  : "-"
              }}
            </q-td>
            <q-td key="price" :props="props" class="wrap-column text-right">
              Rp. {{ formatNumber(props.row.price) }}
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </q-card>
  </q-dialog>
</template>
