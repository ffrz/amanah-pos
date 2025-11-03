<script setup>
import { ref, computed, watch, toRaw } from "vue";
import { useQuasar } from "quasar";

const $q = useQuasar();

const emit = defineEmits(["beforeSubmit"]);

const props = defineProps({
  templates: {
    type: Array,
    required: false,
    default: () => [],
  },
  primaryColumns: {
    type: Array,
    required: true,
  },
  optionalColumns: {
    type: Array,
    default: () => [],
  },
  initialColumns: {
    type: Array,
    required: true,
  },
  initialFilter: {
    type: Object,
    default: () => ({}),
  },
  initialSortOptions: {
    type: Array,
    default: () => [],
  },
  routeName: {
    type: String,
    required: true,
  },
});

const templateOptions = [
  {
    value: null,
    label: "Disesuaikan",
  },
  ...props.templates,
];

const columnOptions = computed(() => {
  return [...props.primaryColumns, ...props.optionalColumns];
});

const primaryColumnValues = computed(() =>
  props.primaryColumns.map((c) => c.value)
);

const orientationOptions = [
  { value: "auto", label: "Otomatis" },
  { value: "portrait", label: "Potret" },
  { value: "landscape", label: "Lansekap" },
];

const getInitialValue = () => ({
  template: null,
  columns: props.initialColumns,
  filter: props.initialFilter,
  sortOptions: props.initialSortOptions,
  orientation: "auto",
});

const form = ref(getInitialValue());

const reset = () => {
  form.value = getInitialValue();
  $q.notify({
    message: "Opsi laporan direset ke nilai awal.",
    timeout: 1500,
  });
};

const onRemoveColumn = ({ index, value }) => {
  if (primaryColumnValues.value.includes(value)) {
    const col = props.primaryColumns.find((c) => c.value === value);
    $q.notify({
      type: "negative",
      message: `Kolom (${col.label}) tidak boleh dihapus.`,
      timeout: 2000,
    });
    return false;
  }

  const originalSortCount = form.value.sortOptions.length;

  form.value.sortOptions = form.value.sortOptions.filter(
    (option) => option.column !== value
  );

  const newSortCount = form.value.sortOptions.length;
  const deletedSortCount = originalSortCount - newSortCount;

  if (deletedSortCount > 0) {
    if (form.value.sortOptions.length === 0) {
      addSortOption();
    }
  }

  return true;
};

const handleSubmit = async (format) => {
  if (form.value.columns.length === 0) {
    $q.notify({
      type: "negative",
      message: `Kolom belum dipilih.`,
      timeout: 2000,
    });
    return;
  }

  const rawForm = toRaw(form.value);
  let params = {
    ...rawForm,
    format: format,
  };

  emit("beforeSubmit", params);

  try {
    const url = route(props.routeName, params);

    window.open(url, "_blank");

    if (format !== "html") {
      $q.notify({
        type: "positive",
        message: `Laporan ${format.toUpperCase()} sedang diunduh.`,
        timeout: 2000,
      });
    }
  } catch (error) {
    $q.notify({
      type: "negative",
      message: "Gagal menghasilkan URL laporan. [ROUTE ERROR]",
      timeout: 5000,
    });
    console.error("Route error:", error);
  }
};

const addSortOption = () => {
  const maxSort = 3;
  const currentSortCount = form.value.sortOptions.length;
  const selectedColumnCount = form.value.columns.length;

  if (currentSortCount >= maxSort) {
    $q.notify({
      type: "warning",
      message: `Maksimal hanya ${maxSort} opsi pengurutan yang diperbolehkan.`,
      timeout: 2000,
    });
    return;
  }

  if (currentSortCount >= selectedColumnCount) {
    $q.notify({
      type: "warning",
      message: `Jumlah opsi pengurutan tidak boleh melebihi jumlah kolom yang dipilih (${selectedColumnCount}).`,
      timeout: 2500,
    });
    return;
  }

  const usedColumns = form.value.sortOptions.map((o) => o.column);

  const availableColumn = columnOptions.value.find(
    (c) => !usedColumns.includes(c.value)
  );

  if (availableColumn) {
    form.value.sortOptions.push({
      column: availableColumn.value,
      order: "asc",
    });
  } else {
    $q.notify({
      type: "warning",
      message: "Tidak ada kolom unik yang tersedia untuk pengurutan.",
      timeout: 2500,
    });
  }
};

const removeSortOption = (index) => {
  form.value.sortOptions.splice(index, 1);
};

const getAvailableSortOptions = computed(() => (currentIndex) => {
  const usedColumns = form.value.sortOptions
    .slice(0, currentIndex)
    .map((o) => o.column);

  usedColumns.push(
    ...form.value.sortOptions.slice(currentIndex + 1).map((o) => o.column)
  );

  return columnOptions.value.filter((c) => {
    const currentColumnValue = form.value.sortOptions[currentIndex]?.column;
    return c.value === currentColumnValue || !usedColumns.includes(c.value);
  });
});

watch(
  () => form.value.template,
  (newTemplate, oldTemplate) => {
    if (newTemplate !== oldTemplate && newTemplate !== null) {
      const template = props.templates.find((a) => a.value === newTemplate);
      form.value.columns = template.columns;
    } else if (newTemplate === null) {
      form.value.columns = props.initialColumns;
    }
  }
);

defineExpose({
  form,
  reset,
  columnOptions,
});
</script>

<template>
  <q-page class="row justify-center q-pa-xs">
    <div class="col col-md-8">
      <div class="q-col-gutter-sm row">
        <div class="col-xs-12 col-md-7">
          <q-expansion-item default-opened class="full-width q-mb-sm bg-grey-2">
            <template v-slot:header>
              <div
                class="full-width bg-grey-2 text-subtitle2 text-bold text-grey-8 row items-center"
              >
                <q-icon name="analytics" size="xs" class="q-mr-sm" />
                Jenis Laporan
              </div>
            </template>

            <q-card square flat bordered class="full-width">
              <q-card-section class="q-py-sm">
                <div>
                  <q-select
                    label="Pilih Laporan"
                    v-model="form.template"
                    :options="templateOptions"
                    map-options
                    emit-value
                    hide-bottom-space
                  />
                </div>
              </q-card-section>
            </q-card>
          </q-expansion-item>

          <q-expansion-item default-opened class="full-width q-mb-sm bg-grey-2">
            <template v-slot:header>
              <div
                class="text-subtitle2 text-bold text-grey-8 row items-center"
              >
                <q-icon name="data_table" size="xs" class="q-mr-sm" />
                Kolom
              </div>
            </template>

            <q-card square flat bordered class="full-width">
              <q-card-section class="q-py-sm">
                <div>
                  <q-select
                    label="Pilih Kolom"
                    v-model="form.columns"
                    :options="columnOptions"
                    map-options
                    emit-value
                    multiple
                    clearable
                    use-chips
                    @remove="onRemoveColumn"
                    @clear="form.columns = initialColumns.slice()"
                    :rules="[
                      (val) =>
                        (val && val.length > 0) || 'Pilih minimal satu kolom',
                    ]"
                    hide-bottom-space
                  >
                    <template v-slot:selected-item="scope">
                      <q-chip
                        :removable="
                          !primaryColumnValues.includes(scope.opt.value)
                        "
                        @remove="
                          onRemoveColumn(scope.opt) &&
                            scope.removeAtIndex(scope.index)
                        "
                        dense
                      >
                        {{ scope.opt.label }}
                      </q-chip>
                    </template>
                  </q-select>
                </div>
              </q-card-section>
            </q-card>
          </q-expansion-item>

          <q-expansion-item class="full-width q-mb-sm bg-grey-2">
            <template v-slot:header>
              <div
                class="text-subtitle2 text-bold text-grey-8 row items-center"
              >
                <q-icon name="filter_alt" size="xs" class="q-mr-sm" />
                Filter
              </div>
            </template>

            <q-card square flat bordered class="full-width">
              <q-card-section class="q-py-sm">
                <slot name="filter" :form="form" />
              </q-card-section>
            </q-card>
          </q-expansion-item>

          <q-expansion-item class="full-width q-mb-sm bg-grey-2">
            <template v-slot:header>
              <div
                class="text-subtitle2 text-bold text-grey-8 row items-center"
              >
                <q-icon name="sort" size="xs" class="q-mr-sm" />
                Urutkan
              </div>
            </template>

            <q-card square flat bordered class="full-width">
              <q-card-section class="q-py-sm">
                <div
                  v-for="(sort, index) in form.sortOptions"
                  :key="index"
                  class="row q-col-gutter-sm q-mb-sm items-center"
                >
                  <q-select
                    v-model="sort.column"
                    class="col-grow"
                    style="min-width: 150px"
                    :options="getAvailableSortOptions(index)"
                    map-options
                    dense
                    emit-value
                    :label="index + 1 + ` - Urut berdasarkan`"
                  />

                  <q-btn
                    :icon="
                      sort.order === 'asc' ? 'arrow_upward' : 'arrow_downward'
                    "
                    color="grey-8"
                    flat
                    round
                    dense
                    @click="sort.order = sort.order === 'asc' ? 'desc' : 'asc'"
                    class="q-ml-sm"
                    size="sm"
                  >
                    <q-tooltip>
                      Urutkan:
                      {{
                        sort.order === "asc"
                          ? "Terkecil ke Terbesar"
                          : "Terbesar ke Terkecil"
                      }}
                    </q-tooltip>
                  </q-btn>

                  <q-btn
                    v-if="form.sortOptions.length > 1"
                    icon="delete"
                    color="red"
                    flat
                    round
                    dense
                    @click="removeSortOption(index)"
                    class="q-ml-xs"
                    size="sm"
                  >
                    <q-tooltip>Hapus Opsi Urutan</q-tooltip>
                  </q-btn>
                </div>

                <div
                  class="row q-mt-md"
                  v-if="
                    form.sortOptions.length < 3 &&
                    form.sortOptions.length < form.columns.length
                  "
                >
                  <q-btn
                    label="Tambah Opsi Pengurutan"
                    icon="add"
                    color="blue-grey"
                    flat
                    dense
                    size="sm"
                    @click="addSortOption"
                  />
                </div>
              </q-card-section>
            </q-card>
          </q-expansion-item>

          <q-expansion-item class="full-width bg-grey-2">
            <template v-slot:header>
              <div
                class="text-subtitle2 text-bold text-grey-8 row items-center"
              >
                <q-icon name="rotate_right" size="xs" class="q-mr-sm" />
                Halaman
              </div>
            </template>

            <q-card square flat bordered class="full-width">
              <q-card-section class="q-py-sm">
                <div>
                  <q-select
                    label="Orientasi"
                    v-model="form.orientation"
                    :options="orientationOptions"
                    map-options
                    emit-value
                  />
                </div>
              </q-card-section>
            </q-card>
          </q-expansion-item>
        </div>

        <div class="col-xs-12 col-md-5">
          <q-card square flat bordered class="full-height full-width">
            <q-card-section class="q-py-sm q-px-md">
              <div class="q-my-sm">
                <q-btn
                  label="Cetak"
                  class="full-width"
                  color="primary"
                  icon="print"
                  @click="handleSubmit('html')"
                />
              </div>
              <div class="q-my-sm">
                <q-btn
                  label="Unduh PDF"
                  class="full-width"
                  color="red"
                  icon="picture_as_pdf"
                  @click="handleSubmit('pdf')"
                />
              </div>
              <div class="q-my-sm">
                <q-btn
                  label="Reset"
                  class="full-width"
                  color="grey"
                  icon="refresh"
                  @click="reset()"
                />
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </q-page>
</template>
<style scoped>
.q-expansion-item {
  border: 1px solid #ddd;
}

.q-expansion-item .q-card {
  border: none;
}

.q-expansion-item :deep(.q-item__section) {
  padding-right: 0;
}
.q-expansion-item :deep(.text-subtitle2) {
  flex-grow: 1;
}
</style>
