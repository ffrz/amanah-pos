<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { Dialog, useQuasar } from "quasar";
import { computed } from "vue";
import { createOptions } from "@/helpers/options";
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";
import { formatNumber } from "@/helpers/formatter";

const page = usePage();
const title = "Penyesuaian Stok";
const form = useForm({
  id: page.props.item.id,
  type: page.props.item.type,
  datetime: dayjs(page.props.item.datetime).format("YYYY-MM-DD HH:mm:ss"),
  notes: page.props.item.notes,
  action: "save",
  details: [],
});
const types = createOptions(window.CONSTANTS.STOCKADJUSTMENT_TYPES);
const details = page.props.details;

const submit = (action) => {
  const proceed = () => {
    form.action = action;
    form.details = details.map((d) => ({
      id: d.id,
      new_quantity: d.new_quantity,
    }));

    handleSubmit({ form, url: route("admin.stock-adjustment.save") });
  };

  if (action === "close" || action === "cancel") {
    Dialog.create({
      title: "Konfirmasi",
      message: "Aksi ini tidak dapat dibatalkan, apakah Anda yakin?",
      cancel: true,
      persistent: true,
    })
      .onOk(() => {
        proceed();
      })
      .onCancel(() => {
        // dibatalkan
      });
  } else {
    proceed();
  }
};

const columns = [
  {
    name: "product_name",
    label: "Nama Produk",
    field: "product_name",
    align: "left",
    sortable: true,
  },
  {
    name: "uom",
    label: "Satuan",
    field: "uom",
    align: "center",
  },
  {
    name: "old_quantity",
    label: "Tercatat",
    field: "old_quantity",
    align: "right",
    format: (val) => formatNumber(val),
  },
  {
    name: "new_quantity",
    label: "Aktual",
    field: "new_quantity",
    align: "right",
    format: (val) => formatNumber(val),
  },

  {
    name: "balance",
    label: "Selisih",
    field: "balance",
    align: "right",
    format: (val, row) => formatNumber(row.new_quantity - row.old_quantity),
  },
];

const $q = useQuasar();
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter(
    (col) => col.name === "product_name" || col.name === "new_quantity"
  );
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.stock-adjustment.index'))"
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1">Info Penyesuaian</div>
            </q-card-section>
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <date-time-picker
                v-model="form.datetime"
                label="Waktu"
                :error="!!form.errors.datetime"
                :disable="form.processing"
                :error-message="form.errors.datetime"
              />
              <q-select
                v-model="form.type"
                :options="types"
                label="Jenis Penyesuaian"
                class="custom-select"
                :error="!!form.errors.type"
                :disable="form.processing"
                map-options
                emit-value
                :error-message="form.errors.type"
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="1000"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                :rules="[]"
                hide-bottom-space
              />
            </q-card-section>
            <q-card-section>
              <q-table
                flat
                bordered
                square
                color="primary"
                class="full-height-table"
                row-key="id"
                virtual-scroll
                :columns="computedColumns"
                :rows="details"
                binary-state-sort
                :hide-pagination="true"
                :rows-per-page-options="[0]"
              >
                <template v-slot:body-cell-product_name="props">
                  <q-td :props="props">
                    <div class="text-bold">{{ props.row.product_name }}</div>
                    <div v-if="$q.screen.lt.md">
                      <div class="text-caption text-grey-8">
                        Tercatat: {{ formatNumber(props.row.old_quantity) }}
                        <br />
                        Selisih:
                        {{
                          formatNumber(
                            props.row.new_quantity - props.row.old_quantity
                          )
                        }}
                      </div>
                    </div>
                  </q-td>
                </template>
                <template v-slot:body-cell-new_quantity="props">
                  <q-td :props="props">
                    <q-input
                      v-model.number="props.row[props.col.name]"
                      input-class="text-right"
                      type="number"
                    />
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                @click="submit('save')"
                label="Simpan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="done_all"
                @click="submit('close')"
                label="Selesaikan"
                color="green"
                :disable="form.processing"
              />
              <q-btn
                icon="close"
                @click="submit('cancel')"
                label="Batalkan"
                color="red"
                :disable="form.processing"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
