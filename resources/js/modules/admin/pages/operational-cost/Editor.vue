<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DatePicker from "@/components/DatePicker.vue";
import { ref } from "vue";
import { useCostCategoryFilter } from "@/composables/useCostCategoryOptions";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Biaya Operasional";

const { costCategoryOptions } = useCostCategoryFilter(page.props.categories);

const categories = ref(costCategoryOptions);
const filteredCategories = ref([...categories.value]);
const form = useForm({
  id: page.props.data.id,
  category_id: page.props.data.category_id,
  date: page.props.data.date,
  description: page.props.data.description,
  notes: page.props.data.notes,
  amount: parseFloat(page.props.data.amount),
});

const submit = () =>
  handleSubmit({ form, url: route("admin.operational-cost.save") });
const filterCategories = (val, update) => {
  update(() => {
    filteredCategories.value = categories.value.filter((item) =>
      item.label.toLowerCase().includes(val.toLowerCase())
    );
  });
};
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
          @click="$inertia.get(route('admin.operational-cost.index'))"
        />
      </div>
    </template>

    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-select
                autofocus
                v-model="form.category_id"
                label="Kategori"
                use-input
                input-debounce="300"
                clearable
                :options="filteredCategories"
                map-options
                emit-value
                @filter="filterCategories"
                :error="!!form.errors.category_id"
                :disable="form.processing"
                hide-bottom-space
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Kategori tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <date-picker
                v-model="form.date"
                label="Tanggal"
                :error="!!form.errors.date"
                :disable="form.processing"
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.description"
                label="Deskripsi"
                lazy-rules
                :error="!!form.errors.description"
                :disable="form.processing"
                :error-message="form.errors.description"
                :rules="[
                  (val) => (val && val.length > 0) || 'Deskripsi harus diisi.',
                ]"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.amount"
                label="Jumlah"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.amount"
                :errorMessage="form.errors.amount"
                :rules="[]"
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
                hide-bottom-space
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="router.get(route('admin.operational-cost.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
