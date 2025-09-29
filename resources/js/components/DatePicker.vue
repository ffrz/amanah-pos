<template>
  <q-input
    v-model="displayDate"
    :label="props.label"
    :readonly="props.readonly"
    :disable="props.disable"
    :error="props.error"
    :error-message="props.errorMessage"
    :mask="
      props.displayFormat
        .replaceAll('D', '#')
        .replaceAll('M', '#')
        .replaceAll('Y', '#')
    "
    :rules="displayRules"
  >
    <template v-slot:append>
      <q-btn
        icon="event"
        :disable="props.disable || props.readonly"
        flat
        rounded
        dense
      >
        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
          <q-date
            v-model="dbDate"
            :options="dateOptions"
            @update:model-value="updateDate"
          >
            <div class="row items-center justify-end">
              <q-btn v-close-popup label="Close" color="primary" flat />
            </div>
          </q-date>
        </q-popup-proxy>
      </q-btn>
    </template>
  </q-input>
</template>

<script setup>
import { ref, watch, defineEmits, computed } from "vue";
import { date as QuasarDate } from "quasar";

const props = defineProps({
  modelValue: {
    type: [Date, null],
    required: false,
    default: null,
  },
  label: String,
  readonly: Boolean,
  disable: Boolean,
  error: Boolean,
  errorMessage: String,
  rules: {
    type: Array,
    default: () => [],
  },
  minDate: {
    type: [Date, null],
    default: null,
  },
  maxDate: {
    type: [Date, null],
    default: null,
  },
  // PROPS BARU UNTUK FORMAT
  displayFormat: {
    type: String,
    default: "DD/MM/YYYY",
  },
});

const emit = defineEmits(["update:modelValue"]);

const displayDate = ref("");
const dbDate = ref("");

const dateOptions = (dateStr) => {
  let isAllowed = true;
  const minDateFormatted = props.minDate
    ? QuasarDate.formatDate(props.minDate, "YYYY/MM/DD")
    : null;
  const maxDateFormatted = props.maxDate
    ? QuasarDate.formatDate(props.maxDate, "YYYY/MM/DD")
    : null;
  if (minDateFormatted) {
    isAllowed =
      isAllowed &&
      QuasarDate.isBetweenDates(dateStr, minDateFormatted, "2999/01/01", {
        inclusiveFrom: true,
      });
  }
  if (maxDateFormatted) {
    isAllowed =
      isAllowed &&
      QuasarDate.isBetweenDates(dateStr, "1900/01/01", maxDateFormatted, {
        inclusiveTo: true,
      });
  }
  return isAllowed;
};

const displayRules = computed(() => [
  (val) => !!val || "Tanggal harus diisi",
  (val) => {
    const dateObj = QuasarDate.extractDate(val, props.displayFormat);
    if (!dateObj) {
      return "Format tanggal tidak valid";
    }

    // Membersihkan waktu untuk perbandingan yang aman
    dateObj.setHours(0, 0, 0, 0);
    const minDateCleaned = props.minDate ? new Date(props.minDate) : null;
    if (minDateCleaned) minDateCleaned.setHours(0, 0, 0, 0);

    const maxDateCleaned = props.maxDate ? new Date(props.maxDate) : null;
    if (maxDateCleaned) maxDateCleaned.setHours(0, 0, 0, 0);

    // Lakukan perbandingan yang aman
    if (minDateCleaned && dateObj < minDateCleaned) {
      return `Tanggal tidak boleh kurang dari ${QuasarDate.formatDate(
        props.minDate,
        props.displayFormat
      )}`;
    }
    if (maxDateCleaned && dateObj > maxDateCleaned) {
      return `Tanggal tidak boleh lebih dari ${QuasarDate.formatDate(
        props.maxDate,
        props.displayFormat
      )}`;
    }

    emit("update:modelValue", dateObj);
    return true;
  },
]);

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue instanceof Date) {
      dbDate.value = QuasarDate.formatDate(newValue, "YYYY/MM/DD");
      displayDate.value = QuasarDate.formatDate(newValue, props.displayFormat);
    } else {
      dbDate.value = "";
      displayDate.value = "";
    }
  },
  { immediate: true }
);

const updateDate = (newDateStr) => {
  const dateObj = newDateStr !== null ? new Date(newDateStr) : null;
  emit("update:modelValue", dateObj);
  displayDate.value = dateObj
    ? QuasarDate.formatDate(dateObj, props.displayFormat)
    : null;
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
