<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit, transformPayload } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { Dialog, useQuasar } from "quasar";
import { computed, ref } from "vue";
import { createOptions } from "@/helpers/options";
import DateTimePicker from "@/components/DateTimePicker.vue";
import { formatNumber } from "@/helpers/formatter";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const $q = useQuasar();
const page = usePage();
const title = "Penyesuaian Stok";
const form = useForm({
  id: page.props.item.id,
  type: page.props.item.type,
  datetime: new Date(page.props.item.datetime),
  notes: page.props.item.notes,
  action: "save",
  details: [],
});
const types = createOptions(window.CONSTANTS.STOCK_ADJUSTMENT_TYPES);

const details = ref(
  page.props.details.map((item) => {
    if (typeof item.new_quantity === "string") {
      item.new_quantity = parseFloat(item.new_quantity);
    }
    return item;
  })
);

const submit = (action) => {
  const proceed = () => {
    form.action = action;
    form.details = details.value.map((d) => ({
      id: d.id,
      new_quantity: d.new_quantity,
    }));

    transformPayload(form, { datetime: "YYYY-MM-DD HH:mm:ss" });
    handleSubmit({ form, url: route("admin.stock-adjustment.save") });
  };

  if (action === "close" || action === "cancel") {
    Dialog.create({
      title: "Konfirmasi",
      message:
        action === "cancel"
          ? "Apakah Anda yakin akan membatalkan seluruh stok opname sesi ini?"
          : "Apakah Anda yakin data stok opname sudah diinput dengan benar?",
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

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter(
    (col) => col.name === "product_name" || col.name === "new_quantity"
  );
});

const printStockCard = () => {
  window.open(
    route("admin.stock-adjustment.print-stock-card", {
      id: form.id,
    }),
    "_blank"
  );
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>

    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          v-if="$q.screen.gt.sm"
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.stock-adjustment.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <q-btn
        icon="save"
        @click="submit('save')"
        :disable="form.processing"
        flat
        dense
        rounded
        color="grey-8"
      />
      <q-btn
        icon="check"
        @click="submit('close')"
        :disable="form.processing"
        dense
        rounded
        class="q-ml-xs"
        color="primary"
      />
      <q-btn
        icon="more_vert"
        dense
        flat
        rounded
        @click.stop
        class="q-ml-xs"
        color="grey-8"
        v-if="$can('admin.product.import') || $can('admin.product:view-cost')"
      >
        <q-menu
          anchor="bottom right"
          self="top right"
          transition-show="scale"
          transition-hide="scale"
        >
          <q-list style="width: 200px">
            <q-item clickable v-ripple v-close-popup @click="printStockCard()">
              <q-item-section avatar>
                <q-icon name="print" />
              </q-item-section>
              <q-item-section> Cetak Kartu Stok</q-item-section>
            </q-item>
            <q-item
              clickable
              v-ripple
              v-close-popup
              @click.stop="submit('cancel')"
            >
              <q-item-section avatar>
                <q-icon name="cancel" color="red" />
              </q-item-section>
              <q-item-section class="text-red">Batalkan</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-xs">
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
                maxlength="200"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                :rules="[]"
                hide-bottom-space
              />
            </q-card-section>
            <q-card-section class="q-pa-xs">
              <q-table
                flat
                bordered
                square
                color="primary"
                class="full-height-table"
                row-key="id"
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
                  <q-td :props="props" class="text-center">
                    <LocaleNumberInput
                      v-model:modelValue="props.row[props.col.name]"
                      input-class="text-right full-width"
                      hide-bottom-space
                      dense
                    />
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
