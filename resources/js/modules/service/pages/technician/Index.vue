<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { formatPhoneNumber } from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";
import useTableHeight from "@/composables/useTableHeight";
import { useTableClickProtection } from "@/composables/useTableClickProtection";

const title = "Teknisi Service";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);

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
  {
    name: "code",
    label: "Kode",
    field: "code",
    align: "left",
    sortable: true,
  },
  {
    name: "name",
    label: "Nama Teknisi",
    field: "name",
    align: "left",
    sortable: true,
  },
  {
    name: "email",
    label: "Email",
    field: "email",
    align: "left",
    sortable: true,
  },
  {
    name: "phone",
    label: "No HP",
    field: "phone",
    align: "left",
  },
  {
    name: "address",
    label: "Alamat",
    field: "address",
    align: "left",
  },
  {
    name: "action",
    align: "right",
  },
];

const statuses = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Teknisi ${row.name}?`,
    url: route("service.technician.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("service.technician.data"),
    loading,
    tableRef,
  });
};

const onFilterChange = () => fetchItems();

const onRowClicked = (row) =>
  router.get(route("service.technician.detail", { id: row.id }));

const { protectClick } = useTableClickProtection();
const protectedRowClick = protectClick(onRowClicked);

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  // Mobile mode: Tampilkan Nama (yang didalamnya ada Kode) dan Action
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
        v-if="$can('service.technician.add')"
        icon="add"
        dense
        rounded
        class="q-ml-sm"
        color="primary"
        @click="router.get(route('service.technician.add'))"
      />
    </template>

    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-xs-12 col-sm-2"
            style="min-width: 150px"
            v-model="filter.status"
            :options="statuses"
            label="Status"
            dense
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
            placeholder="Cari Kode, Nama, Email, atau HP..."
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
        class="full-height-table"
        :style="{ height: tableHeight }"
        ref="tableRef"
        flat
        bordered
        square
        color="primary"
        row-key="id"
        v-model:pagination="pagination"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
        @request="fetchItems"
        binary-state-sort
        :filter="filter.search"
      >
        <template v-slot:loading>
          <q-inner-loading showing color="primary" />
        </template>

        <template v-slot:body="props">
          <q-tr
            :props="props"
            :class="!props.row.active ? 'bg-red-1 text-grey-7' : ''"
            class="cursor-pointer"
            @click.stop="protectedRowClick(props.row, $event)"
          >
            <q-td key="code" :props="props" v-if="$q.screen.gt.sm">
              <q-badge
                color="grey-3"
                text-color="grey-9"
                class="text-weight-bold"
              >
                {{ props.row.code }}
              </q-badge>
            </q-td>

            <q-td key="name" :props="props" class="wrap-column">
              <div>
                {{ props.row.name }}
                <span v-if="$q.screen.lt.md" class="text-primary q-mr-xs">
                  ({{ props.row.code }})
                </span>
              </div>

              <template v-if="$q.screen.lt.md">
                <div v-if="props.row.email" class="text-caption">
                  <q-icon name="email" /> {{ props.row.email }}
                </div>
                <div v-if="props.row.phone" class="text-caption">
                  <q-icon name="phone" />
                  {{ formatPhoneNumber(props.row.phone) }}
                </div>
                <LongTextView
                  v-if="props.row.address"
                  class="text-caption"
                  :text="props.row.address"
                  icon="place"
                />
              </template>
            </q-td>

            <q-td key="email" :props="props">
              {{ props.row.email }}
            </q-td>

            <q-td key="phone" :props="props">
              {{ formatPhoneNumber(props.row.phone) }}
            </q-td>

            <q-td key="address" :props="props">
              <LongTextView :text="props.row.address" />
            </q-td>

            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  v-if="
                    $can('service.technician.add') ||
                    $can('service.technician.edit') ||
                    $can('service.technician.delete')
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
                    <q-list style="min-width: 150px">
                      <q-item
                        v-if="$can('service.technician.add')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('service.technician.duplicate', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar
                          ><q-icon name="content_copy"
                        /></q-item-section>
                        <q-item-section>Duplikat</q-item-section>
                      </q-item>

                      <q-item
                        v-if="$can('service.technician.edit')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('service.technician.edit', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar
                          ><q-icon name="edit"
                        /></q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>

                      <q-separator />

                      <q-item
                        v-if="$can('service.technician.delete')"
                        @click.stop="deleteItem(props.row)"
                        clickable
                        v-ripple
                        class="text-negative"
                        v-close-popup
                      >
                        <q-item-section avatar
                          ><q-icon name="delete"
                        /></q-item-section>
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
