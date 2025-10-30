<script setup>
import { ref, computed } from "vue";
import { useQuasar } from "quasar";

const $q = useQuasar();

const props = defineProps({
  // Data Kolom
  primaryColumns: {
    type: Array,
    required: true,
  },
  optionalColumns: {
    type: Array,
    default: () => [],
  },
  // Data Form Awal (Filter unik)
  initialFilter: {
    type: Object,
    default: () => ({}),
  },
  // Data Form Awal (Sort unik)
  initialSortOptions: {
    type: Array,
    default: () => [],
  },
});

const columnOptions = computed(() => [
  ...props.primaryColumns,
  ...props.optionalColumns,
]);

const primaryColumnValues = computed(() =>
  props.primaryColumns.map((c) => c.value)
);

const orientationOptions = [
  { value: "auto", label: "Otomatis" },
  { value: "portrait", label: "Potret" },
  { value: "landscape", label: "Lansekap" },
];

const getInitialValue = () => ({
  columns: primaryColumnValues.value.slice(),
  filter: JSON.parse(JSON.stringify(props.initialFilter)),
  sortOptions: JSON.parse(JSON.stringify(props.initialSortOptions)),
  orientation: "auto",
});

const form = ref(getInitialValue());

const reset = () => {
  form.value = getInitialValue();
  $q.notify({
    type: "info",
    message: "Filter laporan direset ke nilai awal.",
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
  return true;
};

const submit = (format) => {
  if (form.value.columns.length === 0) {
    $q.notify({
      type: "negative",
      message: `Kolom belum dipilih.`,
      timeout: 2000,
    });
    return;
  }
  emit("submit", { format, form: form.value });
};

const emit = defineEmits(["submit"]);

defineExpose({
  form,
  submit,
  reset,
  columnOptions,
});
</script>
<template>
  <q-page class="row justify-center q-pa-xs">
    <div class="col col-md-8">
      <div class="q-col-gutter-sm row">
        <div class="col-xs-12 col-md-7">
          <q-card square flat bordered class="full-width q-mb-sm">
            <q-card-section class="bg-grey-2 q-py-sm">
              <div class="text-subtitle2 text-bold text-grey-8">
                <q-icon name="data_table" size="xs" class="q-mr-sm" />
                Kolom
              </div>
            </q-card-section>
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
                  @clear="form.columns = primaryColumnValues.slice()"
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

          <q-card square flat bordered class="full-width">
            <q-card-section class="bg-grey-2 q-py-sm">
              <div class="text-subtitle2 text-bold text-grey-8">
                <q-icon name="filter_alt" size="xs" class="q-mr-sm" />
                Filter
              </div>
            </q-card-section>
            <q-card-section class="q-py-sm">
              <slot name="filter" :form="form" />
            </q-card-section>
          </q-card>

          <q-card square flat bordered class="full-width q-mt-sm">
            <q-card-section class="bg-grey-2 q-py-sm">
              <div class="text-subtitle2 text-bold text-grey-8">
                <q-icon name="sort" size="xs" class="q-mr-sm" />
                Urutkan
              </div>
            </q-card-section>
            <q-card-section class="q-py-sm">
              <slot name="sort" :form="form" :columnOptions="columnOptions" />
            </q-card-section>
          </q-card>

          <q-card square flat bordered class="full-width q-mt-sm">
            <q-card-section class="bg-grey-2 q-py-sm">
              <div class="text-subtitle2 text-bold text-grey-8">
                <q-icon name="rotate_right" size="xs" class="q-mr-sm" />
                Orientasi
              </div>
            </q-card-section>
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
                  @click="submit('html')"
                />
              </div>
              <div class="q-my-sm">
                <q-btn
                  label="Unduh PDF"
                  class="full-width"
                  color="red"
                  icon="picture_as_pdf"
                  @click="submit('pdf')"
                />
              </div>
              <div class="q-my-sm">
                <q-btn
                  label="Unduh CSV"
                  class="full-width"
                  color="green"
                  icon="csv"
                  @click="submit('csv')"
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
