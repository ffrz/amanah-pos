<script setup>
import { handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { computed, onMounted, reactive, ref } from "vue";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import LongTextView from "@/components/LongTextView.vue";
import DocumentVersionDataViewer from "./DocumentVersionDataViewer.vue";

const props = defineProps({
  documentId: {
    type: [String, Number],
    required: true,
  },
  documentType: {
    type: String,
    required: true,
  },
});

const showJsonDialog = ref(false);
const selectedVersionData = ref(null);

const $q = useQuasar();
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  ...getQueryParams(),
});
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "created_at",
  descending: true,
});

let columns = [
  {
    name: "version",
    label: "Versi",
    field: "version",
    align: "center",
    sortable: true,
  },
  {
    name: "created_at",
    label: "Waktu Versi",
    field: "created_at",
    align: "left",
    sortable: true,
  },
  {
    name: "creator",
    label: "Dibuat Oleh",
    field: "creator",
    align: "left",
  },
  {
    name: "changelog",
    label: "Log Perubahan",
    field: "changelog",
    align: "left",
  },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (tableProps = null) =>
  handleFetchItems({
    pagination,
    filter,
    props: tableProps,
    rows,
    url: route("admin.document-version.data", {
      filter: {
        document_id: props.documentId,
        document_type: props.documentType,
      },
    }),
    loading,
  });

const computedColumns = computed(() => {
  if (!$q.screen.lt.sm) return columns;
  return columns.filter(
    (col) =>
      col.name === "version" ||
      col.name === "created_at" ||
      col.name === "changelog"
  );
});

const viewVersionData = (versionRow) => {
  selectedVersionData.value = versionRow;
  showJsonDialog.value = true;
};
</script>

<template>
  <q-table
    flat
    bordered
    square
    color="primary"
    class="full-height-table full-height-table2"
    row-key="id"
    v-model:pagination="pagination"
    :filter="filter.search"
    :loading="loading"
    :columns="computedColumns"
    :rows="rows"
    :rows-per-page-options="[10, 25, 50]"
    @request="fetchItems"
    binary-state-sort
  >
    <template v-slot:loading>
      <q-inner-loading showing color="red" />
    </template>

    <template v-slot:no-data="{ message, filter }">
      <div class="full-width row flex-center text-grey-8 q-gutter-sm">
        <span>
          {{ message }}
          {{ filter ? " dengan istilah " + filter : "" }}</span
        >
      </div>
    </template>

    <template v-slot:body="props">
      <q-tr
        :props="props"
        @click.stop="viewVersionData(props.row)"
        class="cursor-pointer"
      >
        <q-td key="version" :props="props">
          <div class="font-bold text-lg">
            <q-badge color="primary">V.{{ props.row.version }}</q-badge>
          </div>

          <template v-if="$q.screen.lt.sm">
            <div>
              <q-icon name="calendar_clock" class="inline-icon" />
              {{ formatDateTime(props.row.created_at) }}
            </div>

            <div class="text-grey-7">
              <q-icon name="person" class="inline-icon" />
              {{ props.row.creator?.name || "Sistem" }}
            </div>

            <LongTextView
              :text="props.row.changelog"
              icon="notes"
              class="text-grey-8 q-mt-xs"
            />
          </template>
        </q-td>

        <q-td key="created_at" :props="props">
          {{ formatDateTime(props.row.created_at) }}
        </q-td>

        <q-td key="creator" :props="props">
          {{ props.row.creator?.name || "Sistem" }}
        </q-td>

        <q-td key="changelog" :props="props">
          <LongTextView :text="props.row.changelog" />
        </q-td>
      </q-tr>
    </template>
  </q-table>
  <DocumentVersionDataViewer
    v-model:show-dialog="showJsonDialog"
    :selected-version="selectedVersionData"
  />
</template>
