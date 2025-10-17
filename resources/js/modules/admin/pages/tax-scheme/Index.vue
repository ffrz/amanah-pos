<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import useTableHeight from "@/composables/useTableHeight";

const title = "Skema Pajak";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);
const filter = reactive({
  search: "",
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
  {
    name: "name",
    label: "Nama",
    field: "name",
    align: "left",
    sortable: true,
  },
  // --- Kolom Baru untuk Pajak ---
  {
    name: "rate_percentage",
    label: "Tarif",
    field: "rate_percentage",
    align: "right",
    sortable: true,
    format: (val) => `${val}%`, // Format sebagai persentase
  },
  {
    name: "is_inclusive",
    label: "Tipe Harga",
    field: "is_inclusive",
    align: "center",
    // Format Tipe Harga: Inclusive atau Exclusive
    format: (val) => (val ? "INCLUSIVE" : "EXCLUSIVE"),
    sortable: true,
  },
  {
    name: "tax_authority",
    label: "Otoritas",
    field: "tax_authority",
    align: "left",
    sortable: true,
  },
  {
    name: "active",
    label: "Status",
    field: "active",
    align: "center",
    sortable: true,
  },
  // ----------------------------
  {
    name: "description",
    label: "Deskripsi",
    field: "description",
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
    message: `Hapus Skema Pajak ${row.name}?`,
    url: route("admin.tax-scheme.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.tax-scheme.data"),
    loading,
    tableRef,
  });
};

// Sesuaikan kolom yang ditampilkan di layar kecil
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;

  // Tampilkan Nama, Tarif, Status, dan Aksi di mobile
  return columns.filter((col) => col.name === "name" || col.name === "action");
});
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
        v-if="$can('admin.tax-scheme.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('admin.tax-scheme.add'))"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
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
            <span
              >{{ message }} {{ filter ? " with term " + filter : "" }}</span
            >
          </div>
        </template>
        <template v-slot:body="props">
          <q-tr :props="props">
            <!-- Kolom Nama + Informasi Detail di Mobile -->
            <q-td key="name" :props="props" class="wrap-column">
              <div class="text-weight-bold">{{ props.row.name }}</div>
              <!-- Tampilan Informasi Detail di Layar Kecil -->
              <template v-if="!$q.screen.gt.sm">
                <div class="text-grey-8">
                  Tarif:
                  <span class="text-weight-medium"
                    >{{ props.row.rate_percentage }}%</span
                  >
                </div>
                <div>
                  Tipe:
                  <span class="text-weight-medium text-uppercase">{{
                    props.row.is_inclusive ? "Inclusive" : "Exclusive"
                  }}</span>
                </div>
                <div v-if="props.row.tax_authority" class="text-grey-8">
                  <q-icon name="account_balance" size="xs" /> Otoritas:
                  {{ props.row.tax_authority }}
                </div>
                <div v-if="props.row.description" class="text-grey-8">
                  <q-icon name="description" size="xs" />
                  {{ props.row.description }}
                </div>
                <q-badge :color="props.row.active ? 'positive' : 'negative'">
                  {{ props.row.active ? "Aktif" : "Non-Aktif" }}
                </q-badge>
              </template>
            </q-td>

            <!-- Kolom Tarif (rate_percentage) -->
            <q-td
              key="rate_percentage"
              :props="props"
              class="text-right text-weight-bold"
            >
              {{ props.row.rate_percentage }}%
            </q-td>

            <!-- Kolom Tipe Harga (is_inclusive) -->
            <q-td key="is_inclusive" :props="props" class="text-center">
              <q-badge
                :color="props.row.is_inclusive ? 'green-7' : 'deep-orange-7'"
                text-color="white"
              >
                {{ props.row.is_inclusive ? "INCLUSIVE" : "EXCLUSIVE" }}
              </q-badge>
            </q-td>

            <!-- Kolom Otoritas Pajak (tax_authority) -->
            <q-td key="tax_authority" :props="props" class="wrap-column">
              {{ props.row.tax_authority }}
            </q-td>

            <!-- Kolom Status Aktif (active) -->
            <q-td key="active" :props="props" class="text-center">
              <q-badge :color="props.row.active ? 'positive' : 'negative'">
                {{ props.row.active ? "Aktif" : "Non-Aktif" }}
              </q-badge>
            </q-td>

            <!-- Kolom Deskripsi (description) -->
            <q-td key="description" :props="props" class="wrap-column">
              {{ props.row.description }}
            </q-td>

            <!-- Kolom Aksi -->
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  v-if="
                    $can('admin.tax-scheme.add') ||
                    $can('admin.tax-scheme.edit') ||
                    $can('admin.tax-scheme.delete')
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
                        v-if="$can('admin.tax-scheme.add')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.tax-scheme.duplicate', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="file_copy" />
                        </q-item-section>
                        <q-item-section>Duplikat</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.tax-scheme.edit')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.tax-scheme.edit', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.tax-scheme.delete')"
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
  </authenticated-layout>
</template>
