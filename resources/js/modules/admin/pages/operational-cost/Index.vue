<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatDate, formatNumber } from "@/helpers/formatter";
import { createMonthOptions, createYearOptions } from "@/helpers/options";
import { useCostCategoryFilter } from "@/composables/useCostCategoryOptions";
import useTableHeight from "@/composables/useTableHeight";
import LongTextView from "@/components/LongTextView.vue";
import ImageViewer from "@/components/ImageViewer.vue";
import { useFinanceAccount } from "@/composables/useFinanceAccount";

const title = "Biaya Operasional";
const page = usePage();
const $q = useQuasar();
const showFilter = ref(false);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const activeImagePath = ref(null);
const showImageViewer = ref(false);

const rows = ref([]);
const loading = ref(true);
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1; // months are 0-based, so adding 1 to get correct month number

const years = [
  { label: "Semua Tahun", value: null },
  { label: `${currentYear}`, value: currentYear },
  ...createYearOptions(currentYear - 2, currentYear - 1).reverse(),
];

const months = [{ value: null, label: "Semua Bulan" }, ...createMonthOptions()];

const { costCategoryOptions } = useCostCategoryFilter(page.props.categories);
const categories = [
  { value: "all", label: "Semua" },
  { value: null, label: "Tanpa Kategori" },
  ...costCategoryOptions,
];

const { accountOptions } = useFinanceAccount(page.props.finance_accounts);
const accounts = [
  { value: "all", label: "Semua" },
  { value: null, label: "Tidak Diset" },
  ...accountOptions,
];

const filter = reactive({
  search: "",
  category_id: "all",
  year: currentYear,
  month: currentMonth,
  ...getQueryParams(),
});
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
});
const columns = [
  {
    name: "id",
    label: $q.screen.lt.md ? "Item" : "ID",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "date",
    label: "Tanggal",
    field: "date",
    align: "left",
    sortable: true,
  },
  {
    name: "account_id",
    label: "Akun",
    field: "account_id",
    align: "left",
    sortable: true,
  },
  {
    name: "category_id",
    label: "Kategori",
    field: "category_id",
    align: "left",
    sortable: true,
  },
  {
    name: "description",
    label: "Deskripsi",
    field: "description",
    align: "left",
    sortable: true,
  },
  {
    name: "amount",
    label: "Jumlah (Rp.)",
    field: "amount",
    align: "right",
    sortable: true,
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
  {
    name: "action",
    align: "right",
  },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Biaya ${row.description}?`,
    url: route("admin.operational-cost.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.operational-cost.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id" || col.name === "action");
});

watch(
  () => filter.year,
  (newVal) => {
    if (newVal === null) {
      filter.month = null;
    }
  }
);

const showAttachment = (url) => {
  activeImagePath.value = url;
  showImageViewer.value = true;
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        rounded
        flat
        @click="showFilter = !showFilter"
      />
      <q-btn
        v-if="$can('admin.operational-cost.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.operational-cost.add'))"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            v-model="filter.year"
            :options="years"
            label="Tahun"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.month"
            :options="months"
            label="Bulan"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            :disable="filter.year === null"
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.category_id"
            :options="categories"
            label="Kategori"
            dense
            class="custom-select col-xs-12 col-sm-3"
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.finance_account_id"
            :options="accounts"
            label="Akun Kas"
            dense
            class="custom-select col-xs-12 col-sm-3"
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-input
            class="col"
            outlined
            dense
            debounce="300"
            v-model="filter.search"
            placeholder="Cari"
            clearable
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-xs">
      <q-table
        ref="tableRef"
        class="full-height-table"
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
        :style="{ height: tableHeight }"
        @request="fetchItems"
        binary-state-sort
      >
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>
        <template v-slot:no-data="{ icon, message, filter }">
          <div class="full-width row flex-center text-grey-8 q-gutter-sm">
            <span>
              {{ message }}
              {{ filter ? " with term " + filter : "" }}</span
            >
          </div>
        </template>
        <template v-slot:body="props">
          <q-tr
            :props="props"
            class="cursor-pointer"
            @click.stop="
              router.get(
                route('admin.operational-cost.detail', {
                  id: props.row.id,
                })
              )
            "
          >
            <q-td key="id" :props="props" class="wrap-column">
              <template v-if="!$q.screen.gt.sm"> ID: </template>
              {{ props.row.id }}
              <template v-if="!$q.screen.gt.sm">
                <div>
                  <q-icon name="calendar_clock" class="inline-icon" />
                  {{ formatDate(props.row.date) }}
                </div>
                <div v-if="props.row.finance_account">
                  <q-icon name="wallet" class="inline-icon" />
                  {{ props.row.finance_account.name }}
                </div>
                <div v-if="props.row.category">
                  <q-icon name="category" class="inline-icon" />
                  {{ props.row.category.name }}
                </div>
                <LongTextView
                  icon="description"
                  :text="props.row.description"
                  class="inline-icon"
                />
                <div>
                  <q-icon name="money" class="inline-icon" /> Rp.
                  {{ formatNumber(props.row.amount) }}
                </div>
                <LongTextView
                  icon="notes"
                  :text="props.row.notes"
                  class="inline-icon text-grey-9"
                />
              </template>
            </q-td>
            <q-td key="date" :props="props" class="wrap-column">
              {{ formatDate(props.row.date) }}
            </q-td>
            <q-td key="account_id" :props="props" class="wrap-column">
              {{
                props.row.finance_account ? props.row.finance_account.name : ""
              }}
            </q-td>
            <q-td key="category_id" :props="props">
              <div v-if="props.row.category">
                {{ props.row.category.name }}
              </div>
            </q-td>
            <q-td key="description" :props="props">
              <LongTextView :text="props.row.description" :max-length="50" />
            </q-td>
            <q-td key="amount" :props="props">
              {{ formatNumber(props.row.amount) }}
            </q-td>
            <q-td key="notes" :props="props">
              <LongTextView :text="props.row.notes" :max-length="50" />
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end no-wrap">
                <q-btn
                  icon="image"
                  color="primary"
                  dense
                  flat
                  :disable="
                    props.row.image_path === null || props.row.image_path === ''
                  "
                  @click.stop="showAttachment(props.row.image_path)"
                >
                  <q-tooltip v-if="props.row.image_path != null">
                    Lihat Bukti
                  </q-tooltip>
                </q-btn>
                <q-btn
                  v-if="
                    $can('admin.operational-cost.add') ||
                    $can('admin.operational-cost.edit') ||
                    $can('admin.operational-cost.delete')
                  "
                  icon="more_vert"
                  dense
                  flat
                  rounded
                  @click.stop
                >
                  <q-menu
                    anchor="bottom right"
                    self="top right"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-list style="width: 200px">
                      <q-item
                        v-if="$can('admin.operational-cost.add')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route(
                              'admin.operational-cost.duplicate',
                              props.row.id
                            )
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="file_copy" />
                        </q-item-section>
                        <q-item-section> Duplikat </q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.operational-cost.edit')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.operational-cost.edit', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.operational-cost.delete')"
                        @click.stop="deleteItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
                        <q-item-section avatar>
                          <q-icon name="delete_forever" />
                        </q-item-section>
                        <q-item-section>Hapus</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
    <ImageViewer v-model="showImageViewer" :imageUrl="`/${activeImagePath}`" />
  </authenticated-layout>
</template>
