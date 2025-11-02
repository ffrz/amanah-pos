<template>
  <q-input
    v-model="displayDatetime"
    :label="props.label"
    :readonly="props.readonly"
    :disable="props.disable"
    :error="props.error"
    :error-message="props.errorMessage"
    :mask="props.displayMask"
    :rules="displayRules"
    :hide-bottom-space="props.hideBottomSpace"
  >
    <template v-slot:append>
      <q-btn
        icon="event"
        :disable="props.disable || props.readonly"
        flat
        rounded
        dense
        size="sm"
      >
        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
          <q-date
            v-model="dateValue"
            mask="YYYY-MM-DD"
            @update:model-value="updateDateTime"
          />
        </q-popup-proxy>
      </q-btn>

      <q-btn
        icon="access_time"
        :disable="props.disable || props.readonly"
        flat
        rounded
        dense
        size="sm"
      >
        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
          <q-time
            v-model="timeValue"
            mask="HH:mm:ss"
            format24h
            with-seconds
            @update:model-value="updateDateTime"
          />
        </q-popup-proxy>
      </q-btn>
    </template>
  </q-input>
</template>

<script setup>
import { ref, watch, computed, defineEmits } from "vue";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
dayjs.extend(customParseFormat);

const props = defineProps({
  modelValue: {
    type: [Date, null],
    required: false,
    default: null,
  },
  label: {
    type: String,
    default: "",
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  disable: {
    type: Boolean,
    default: false,
  },
  error: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: "",
  },
  rules: {
    type: Array,
    default: () => [],
  },
  displayFormat: {
    type: String,
    default: "DD/MM/YYYY HH:mm:ss",
  },
  displayMask: {
    type: String,
    default: "##/##/#### ##:##:##",
  },
  minDate: {
    type: [Date, null],
    default: null,
  },
  maxDate: {
    type: [Date, null],
    default: null,
  },
  hideBottomSpace: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue"]);

const dateValue = ref("");
const timeValue = ref("");
const displayDatetime = ref("");

// Gabungkan dateValue dan timeValue menjadi satu objek dayjs
const combinedDatetime = computed(() => {
  if (!timeValue.value) {
    timeValue.value = "00:00:00";
  }

  if (dateValue.value && timeValue.value) {
    return dayjs(
      `${dateValue.value} ${timeValue.value}`,
      "YYYY-MM-DD HH:mm:ss"
    );
  }
  return null;
});

const displayRules = computed(() => [
  (val) => !!val || "Tanggal dan waktu harus diisi",
  (val) => {
    const datetimeObj = dayjs(val, props.displayFormat, true);
    if (!datetimeObj.isValid()) {
      return "Format tanggal dan waktu tidak valid";
    }

    const minDateCleaned = props.minDate ? dayjs(props.minDate) : null;
    const maxDateCleaned = props.maxDate ? dayjs(props.maxDate) : null;

    if (minDateCleaned && datetimeObj.isBefore(minDateCleaned)) {
      return `Tanggal tidak boleh kurang dari ${minDateCleaned.format(
        props.displayFormat
      )}`;
    }
    if (maxDateCleaned && datetimeObj.isAfter(maxDateCleaned)) {
      return `Tanggal tidak boleh lebih dari ${maxDateCleaned.format(
        props.displayFormat
      )}`;
    }

    emit("update:modelValue", datetimeObj.toDate());
    return true;
  },
]);

const updateDateTime = () => {
  if (combinedDatetime.value) {
    emit("update:modelValue", combinedDatetime.value.toDate());
    displayDatetime.value = combinedDatetime.value.format(props.displayFormat);
  } else {
    emit("update:modelValue", null);
    displayDatetime.value = null;
  }
};

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue instanceof Date) {
      dateValue.value = dayjs(newValue).format("YYYY-MM-DD");
      timeValue.value = dayjs(newValue).format("HH:mm:ss");
      displayDatetime.value = dayjs(newValue).format(props.displayFormat);
    } else {
      dateValue.value = "";
      timeValue.value = "";
      displayDatetime.value = "";
    }
  },
  { immediate: true }
);
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
