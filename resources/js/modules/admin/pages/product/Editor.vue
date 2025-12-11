<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { createOptions } from "@/helpers/options";
import { onMounted, ref, watch } from "vue";

// Components
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import CheckBox from "@/components/CheckBox.vue";
import BarcodeInput from "@/components/BarcodeInput.vue";
import SupplierAutocomplete from "@/components/SupplierAutocomplete.vue";
import ProductCategoryAutocomplete from "@/components/ProductCategoryAutocomplete.vue";
import UomAutocomplete from "@/components/UomAutocomplete.vue";

// Import komponen pricing (sesuaikan path)
import PricingLevelRow from "./editor/PricingLevelRow.vue";
import PricingUnitCard from "./editor/PricingUnitCard.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Produk";
const types = createOptions(window.CONSTANTS.PRODUCT_TYPES);

const tab = ref("general");
const selectedSupplier = ref(page.props.data.supplier);
const availableUoms = ref(usePage().props.uoms);

// Definisi Level Harga Global
const priceLevels = [
  { key: 1, label: "Harga Eceran" },
  { key: 2, label: "Harga Partai" },
  { key: 3, label: "Harga Grosir" },
];

const form = useForm({
  id: page.props.data.id,
  category_id: page.props.data.category_id,
  supplier_id: page.props.data.supplier_id,
  type: page.props.data.type || "",
  name: page.props.data.name || "",
  description: page.props.data.description || "",
  stock: parseFloat(page.props.data.stock) || 0,
  min_stock: parseFloat(page.props.data.min_stock) || 0,

  uom: page.props.data.uom || "",
  barcode: page.props.data.barcode || "",
  cost: parseFloat(page.props.data.cost) || 0,

  // Harga Dasar (Satuan Utama)
  price_1: parseFloat(page.props.data.price_1) || 0,
  price_2: parseFloat(page.props.data.price_2) || 0,
  price_3: parseFloat(page.props.data.price_3) || 0,

  price_1_markup: parseFloat(page.props.data.price_1_markup) || 0,
  price_2_markup: parseFloat(page.props.data.price_2_markup) || 0,
  price_3_markup: parseFloat(page.props.data.price_3_markup) || 0,

  // Tiers Satuan Utama
  price_1_tiers: page.props.data.price_1_tiers || [],
  price_2_tiers: page.props.data.price_2_tiers || [],
  price_3_tiers: page.props.data.price_3_tiers || [],

  // Options
  price_1_option: "price",
  price_2_option: "price",
  price_3_option: "price",

  active: !!page.props.data.active,
  price_editable: !!page.props.data.price_editable,
  notes: page.props.data.notes || "",

  // Product Units (Multi Satuan) - Pastikan array
  // Exclude base unit from the list, because its already represented by main fields
  product_units: (page.props.data.product_units || []).filter(
    (unit) => !unit.is_base_unit
  ),
});

const submit = () => {
  handleSubmit({ form, url: route("admin.product.save") });
};

watch(selectedSupplier, (val) => (form.supplier_id = val ? val.id : null));

// --- LOGIC MULTI SATUAN ---

const addUnit = () => {
  form.product_units.push({
    id: null,
    name: null,
    conversion_factor: 1,
    barcode: "",
    // Inisialisasi field harga flat
    price_1: 0,
    price_1_markup: 0,
    price_1_tiers: [],
    price_2: 0,
    price_2_markup: 0,
    price_2_tiers: [],
    price_3: 0,
    price_3_markup: 0,
    price_3_tiers: [],
  });
};

const removeUnit = (index) => form.product_units.splice(index, 1);

// Helper hitung HPP Konversi (HPP Utama * Faktor)
const getConvertedCost = (rate) => {
  const baseCost = parseFloat(form.cost) || 0;
  const conversion = parseFloat(rate) || 1;
  return baseCost * conversion;
};

// --- LIFECYCLE ---
onMounted(() => {
  // Init array tier satuan utama
  [1, 2, 3].forEach((i) => {
    if (!form[`price_${i}_tiers`]) form[`price_${i}_tiers`] = [];
  });

  // Init array tier multi satuan
  if (form.product_units && form.product_units.length > 0) {
    form.product_units.forEach((unit) => {
      [1, 2, 3].forEach((level) => {
        const tierKey = `price_${level}_tiers`;
        if (!unit[tierKey]) {
          unit[tierKey] = [];
        }
      });
    });
  }
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <q-btn
        icon="arrow_back"
        dense
        color="grey-7"
        flat
        rounded
        @click="router.get(route('admin.product.index'))"
      />
    </template>
    <template #right-button>
      <q-btn
        class="q-ml-xs"
        type="submit"
        icon="check"
        rounded
        dense
        color="primary"
        :disable="form.processing"
        @click="submit()"
      />
    </template>

    <q-page class="row justify-center">
      <div class="col col-md-8 q-pa-xs">
        <q-form
          class="row"
          @keydown.enter.prevent
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <input type="hidden" name="id" v-model="form.id" />

            <q-tabs
              v-model="tab"
              dense
              class="text-grey-7"
              active-color="primary"
              indicator-color="primary"
              align="justify"
              switch-indicator
            >
              <q-tab name="general" label="Umum" />
              <q-tab name="inventory" label="Inventori" />
              <q-tab name="pricing" label="Harga & Modal" />
            </q-tabs>

            <q-tab-panels v-model="tab" animated keep-alive>
              <q-tab-panel name="general">
                <div class="column q-gutter-y-md">
                  <q-select
                    autofocus
                    v-model="form.type"
                    :options="types"
                    label="Jenis Produk"
                    map-options
                    emit-value
                    hide-bottom-space
                  />
                  <q-input
                    v-model.trim="form.name"
                    label="Kode / Nama Produk"
                    lazy-rules
                    :error="!!form.errors.name"
                    :error-message="form.errors.name"
                    :rules="[(val) => (val && val.length > 0) || 'Wajib diisi']"
                  />
                  <q-input
                    v-model.trim="form.description"
                    type="textarea"
                    autogrow
                    counter
                    maxlength="200"
                    label="Deskripsi"
                  />
                  <ProductCategoryAutocomplete
                    v-model:modelValue="form.category_id"
                    :categories="page.props.categories"
                    label="Kategori"
                  />
                  <SupplierAutocomplete
                    v-model="selectedSupplier"
                    label="Supplier Default"
                  />
                  <CheckBox v-model="form.active" label="Produk Aktif" />
                </div>
              </q-tab-panel>

              <q-tab-panel name="inventory">
                <div class="column q-gutter-y-xs">
                  <div class="text-grey-8 text-subtitle1">Satuan Utama</div>
                  <div class="row q-gutter-md">
                    <div class="col">
                      <UomAutocomplete
                        dense
                        v-model:modelValue="form.uom"
                        v-model:items="availableUoms"
                        :items="page.props.uoms"
                        label="Satuan Dasar (Stok)"
                        :error="!!form.errors.uom"
                        :error-message="form.errors.uom"
                      />
                    </div>
                    <div class="col">
                      <BarcodeInput
                        dense
                        v-model.trim="form.barcode"
                        label="Barcode (Utama)"
                        placeholder="Opsional"
                      />
                    </div>
                  </div>

                  <q-separator class="q-my-md" />
                  <div class="text-grey-8 text-subtitle1">
                    Pengaturan Multi Satuan
                  </div>

                  <div
                    class="row q-gutter-sm q-pt-none items-start"
                    v-for="(unit, index) in form.product_units"
                    :key="index"
                  >
                    <div class="col">
                      <UomAutocomplete
                        dense
                        v-model:modelValue="unit.name"
                        v-model:items="availableUoms"
                        label="Satuan Konversi"
                        :error="!!form.errors[`product_units.${index}.name`]"
                        :error-message="
                          form.errors[`product_units.${index}.name`]
                        "
                        hide-bottom-space
                      />
                    </div>
                    <div class="col">
                      <LocaleNumberInput
                        dense
                        v-model:modelValue="unit.conversion_factor"
                        label="Jumlah Isi"
                        :error="
                          !!form.errors[
                            `product_units.${index}.conversion_factor`
                          ]
                        "
                        :error-message="
                          form.errors[
                            `product_units.${index}.conversion_factor`
                          ]
                        "
                        hide-bottom-space
                      />
                      <div class="q-pt-xs text-grey-6 text-caption">
                        1 {{ unit.name || "..." }} =
                        {{ unit.conversion_factor || 0 }}
                        {{ form.uom || "..." }}
                      </div>
                    </div>
                    <div class="col">
                      <BarcodeInput
                        dense
                        v-model.trim="unit.barcode"
                        label="Barcode"
                        hide-bottom-space
                      />
                    </div>
                    <div class="col-auto">
                      <q-btn
                        size="sm"
                        class="q-mt-sm"
                        icon="delete"
                        dense
                        flat
                        color="red"
                        @click="removeUnit(index)"
                      />
                    </div>
                  </div>

                  <div class="row">
                    <q-btn
                      dense
                      flat
                      color="primary"
                      @click="addUnit()"
                      icon="add"
                      >Tambah satuan</q-btn
                    >
                  </div>

                  <q-separator class="q-my-md" />
                  <div class="text-grey-8 text-subtitle1">Informasi Stok</div>
                  <div class="row q-gutter-md">
                    <LocaleNumberInput
                      v-model:modelValue="form.stock"
                      :label="`Stok Aktual (${form.uom || 'Unit'})`"
                      class="col"
                    />
                    <LocaleNumberInput
                      v-model:modelValue="form.min_stock"
                      :label="`Stok Minimum (${form.uom || 'Unit'})`"
                      class="col"
                    />
                  </div>
                </div>
              </q-tab-panel>

              <q-tab-panel name="pricing" class="q-px-none">
                <div class="column q-gutter-y-md">
                  <div class="q-px-md">
                    <LocaleNumberInput
                      v-if="$can('admin.product:view-cost')"
                      v-model:modelValue="form.cost"
                      label="Modal / HPP Satuan Dasar (Rp)"
                      bg-color="yellow-1"
                    />
                    <CheckBox
                      class="q-mt-sm"
                      v-model="form.price_editable"
                      label="Izinkan kasir mengubah harga manual"
                    />
                  </div>

                  <q-separator />

                  <div
                    v-for="(unitContext, idx) in [
                      -1,
                      ...form.product_units.keys(),
                    ]"
                    :key="idx"
                  >
                    <template v-if="unitContext === -1">
                      <PricingUnitCard
                        title="Satuan Utama"
                        :label="form.uom || '...'"
                        :badge-label="`1 ${form.uom || 'Unit'}`"
                        badge-color="blue"
                        icon="inventory_2"
                        :cost="form.cost"
                      >
                        <div
                          v-for="(level, lIdx) in priceLevels"
                          :key="level.key"
                          :class="lIdx % 2 !== 0 ? 'bg-white' : ''"
                        >
                          <PricingLevelRow
                            :label="level.label"
                            :cost="form.cost"
                            v-model:price="form[`price_${level.key}`]"
                            v-model:markup="form[`price_${level.key}_markup`]"
                            v-model:tiers="form[`price_${level.key}_tiers`]"
                          />
                        </div>
                      </PricingUnitCard>
                    </template>

                    <template v-else>
                      <PricingUnitCard
                        title="Satuan Konversi"
                        :label="form.product_units[unitContext].name || 'Baru'"
                        :badge-label="`Isi ${form.product_units[unitContext].conversion_factor}`"
                        badge-color="orange"
                        icon="layers"
                        :cost="
                          getConvertedCost(
                            form.product_units[unitContext].conversion_factor
                          )
                        "
                      >
                        <div
                          v-for="(level, lIdx) in priceLevels"
                          :key="level.key"
                          :class="lIdx % 2 !== 0 ? 'bg-grey-1' : ''"
                        >
                          <PricingLevelRow
                            :label="level.label"
                            :cost="
                              getConvertedCost(
                                form.product_units[unitContext]
                                  .conversion_factor
                              )
                            "
                            v-model:price="
                              form.product_units[unitContext][
                                `price_${level.key}`
                              ]
                            "
                            v-model:markup="
                              form.product_units[unitContext][
                                `price_${level.key}_markup`
                              ]
                            "
                            v-model:tiers="
                              form.product_units[unitContext][
                                `price_${level.key}_tiers`
                              ]
                            "
                          />
                        </div>
                      </PricingUnitCard>
                    </template>
                  </div>
                </div>
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
