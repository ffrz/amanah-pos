<template>
  <q-input
    v-model="displayDatetime"
    :label="props.label"
    :readonly="props.readonly"
    :disable="props.disable"
    :error="props.error"
    :error-message="props.errorMessage"
    :mask="props.displayMask"
    :rules="inputRules"
    :hide-bottom-space="props.hideBottomSpace"
    debounce="500"
  >
    <!-- Hanya SATU tombol untuk memicu popup Date & Time -->
    <template v-slot:append>
      <q-icon
        name="calendar_today"
        class="cursor-pointer"
        :disable="props.disable || props.readonly"
        size="xs"
        @click="openDialog"
      >
        <!-- DIGANTI DENGAN Q-DIALOG UNTUK CENTERING -->
        <q-dialog
          v-model="isDialogOpen"
          transition-show="scale"
          transition-hide="scale"
          :maximized="$q.screen.lt.sm"
        >
          <!-- Menggunakan q-card untuk struktur dan menjaga style yang baik -->
          <q-card
            class="datetime-dialog-card"
            :class="{ 'full-width full-height': $q.screen.lt.sm }"
          >
            <!-- Konten Date/Time Picker -->
            <div class="column items-center full-width">
              <!-- 1. Date Picker (Kalender) -->
              <q-date
                v-model="dateValue"
                mask="YYYY-MM-DD"
                :options="dateOptions"
                @update:model-value="onDateUpdate"
                no-header
                class="custom-q-date-compact"
                :class="{ 'full-width': $q.screen.lt.sm }"
              >
                <!-- Slot q-date diganti dengan Time Picker yang disesuaikan -->
                <template v-slot:default>
                  <!-- 2. Time Picker Input (Terintegrasi - Jam:Menit:Detik) -->
                  <div class="q-pa-sm full-width text-center">
                    <div
                      class="row no-wrap items-center justify-center q-gutter-x-sm"
                    >
                      <!-- Input Jam -->
                      <div class="col-auto column items-center">
                        <q-btn
                          icon="expand_less"
                          flat
                          round
                          dense
                          @click="incrementHour"
                        />
                        <div class="text-h6">{{ hourDisplay }}</div>
                        <q-btn
                          icon="expand_more"
                          flat
                          round
                          dense
                          @click="decrementHour"
                        />
                      </div>

                      <div class="col-auto text-h6">:</div>

                      <!-- Input Menit -->
                      <div class="col-auto column items-center">
                        <q-btn
                          icon="expand_less"
                          flat
                          round
                          dense
                          @click="incrementMinute"
                        />
                        <div class="text-h6">{{ minuteDisplay }}</div>
                        <q-btn
                          icon="expand_more"
                          flat
                          round
                          dense
                          @click="decrementMinute"
                        />
                      </div>

                      <div class="col-auto text-h6">:</div>

                      <!-- Input Detik -->
                      <div class="col-auto column items-center">
                        <q-btn
                          icon="expand_less"
                          flat
                          round
                          dense
                          @click="incrementSecond"
                        />
                        <div class="text-h6">{{ secondDisplay }}</div>
                        <q-btn
                          icon="expand_more"
                          flat
                          round
                          dense
                          @click="decrementSecond"
                        />
                      </div>
                    </div>
                  </div>
                </template>
              </q-date>

              <!-- 3. Footer Tombol Kontrol (Hari Ini, Batal, OK) -->
              <div
                class="row full-width items-center justify-between q-pa-sm bg-grey-2"
              >
                <!-- Tombol Clear (opsional, tapi penting) -->
                <div>
                  <q-btn
                    label="Hari Ini"
                    color="primary"
                    flat
                    @click="setToday"
                  />
                  <q-btn
                    label="Clear"
                    color="negative"
                    flat
                    @click="clearDatetime"
                  />
                </div>
                <div>
                  <q-btn
                    label="Batal"
                    color="primary"
                    flat
                    @click="isDialogOpen = false"
                    class="q-mr-sm"
                  />
                  <q-btn label="OK" color="primary" @click="closeDialog" />
                </div>
              </div>
            </div>
          </q-card>
        </q-dialog>
      </q-icon>
    </template>
  </q-input>
</template>

<script setup>
import { ref, watch, computed, defineEmits, getCurrentInstance } from "vue";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import isBetween from "dayjs/plugin/isBetween";

dayjs.extend(customParseFormat);
dayjs.extend(isBetween);

// Akses Quasar $q secara internal
const $q = getCurrentInstance()?.appContext.config.globalProperties.$q;

const props = defineProps({
  modelValue: {
    type: [Date, String, null],
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

// STATE BARU UNTUK MENGELOLA Q-DIALOG
const isDialogOpen = ref(false);

const dateValue = ref(""); // YYYY-MM-DD
const displayDatetime = ref("");

// BARU: State untuk menyimpan nilai awal ketika dialog dibuka (untuk fungsi BATAL)
const initialStagingValue = ref(null);

// State internal untuk Jam, Menit, dan Detik (nilai yang SEMENTARA dipilih di dialog)
const hourValue = ref(0);
const minuteValue = ref(0);
const secondValue = ref(0);

// Computed untuk tampilan dua digit
const hourDisplay = computed(() => String(hourValue.value).padStart(2, "0"));
const minuteDisplay = computed(() =>
  String(minuteValue.value).padStart(2, "0")
);
const secondDisplay = computed(() =>
  String(secondValue.value).padStart(2, "0")
);

// Gabungkan dateValue dan state Jam/Menit/Detik menjadi satu objek dayjs
const combinedDatetime = computed(() => {
  if (!dateValue.value) {
    return null;
  }

  const time = `${hourDisplay.value}:${minuteDisplay.value}:${secondDisplay.value}`;

  return dayjs(`${dateValue.value} ${time}`, "YYYY-MM-DD HH:mm:ss");
});

// Fungsi untuk membuka dialog
const openDialog = () => {
  // BARU: Simpan nilai awal menggunakan Day.js agar support String maupun Date
  if (props.modelValue) {
    initialStagingValue.value = dayjs(props.modelValue);
  } else {
    initialStagingValue.value = null;
  }

  // Saat dialog dibuka, pastikan nilai-nilai internal sesuai dengan modelValue saat ini
  if (props.modelValue) {
    // GANTI LOGIKA: Jangan pakai instanceof Date, tapi langsung parse dayjs
    const dayjsObj = dayjs(props.modelValue);

    // Pastikan valid sebelum di-assign
    if (dayjsObj.isValid()) {
      dateValue.value = dayjsObj.format("YYYY-MM-DD");
      hourValue.value = dayjsObj.hour();
      minuteValue.value = dayjsObj.minute();
      secondValue.value = dayjsObj.second();
    }
  } else {
    // Jika modelValue null, set ke hari ini (staging/default)
    const now = dayjs();
    dateValue.value = now.format("YYYY-MM-DD");
    hourValue.value = now.hour();
    minuteValue.value = now.minute();
    secondValue.value = now.second();
  }

  isDialogOpen.value = true;
};

// Fungsi untuk mengirim nilai ke parent (Hanya dipanggil oleh closeDialog/OK)
const updateModelValue = () => {
  if (combinedDatetime.value && combinedDatetime.value.isValid()) {
    // Nilai baru yang akan di-commit
    const newDate = combinedDatetime.value.toDate(); // Timestamp saat ini
    const currentTimestamp = props.modelValue
      ? dayjs(props.modelValue).valueOf()
      : -1;
    const newTimestamp = newDate.getTime();

    displayDatetime.value = combinedDatetime.value.format(props.displayFormat); // 2. Emit Date object ke modelValue (DENGAN cek redundansi)
    if (currentTimestamp !== newTimestamp) {
      emit("update:modelValue", newDate);
    }
  } else {
    // Kasus null (Clear)
    if (props.modelValue !== null) {
      emit("update:modelValue", null);
    }
    displayDatetime.value = "";
  }
};

// Dipanggil ketika tanggal di kalender berubah
const onDateUpdate = () => {
  // BARU: Tidak ada lagi updateModelValue() di sini. Perubahan disimpan di state internal.
  if (!dateValue.value) {
    // Jika tanggal dipilih, pastikan ada nilai jam/menit/detik default
    hourValue.value = dayjs().hour();
    minuteValue.value = dayjs().minute();
    secondValue.value = dayjs().second();
  }
};

// *** Logic Custom Time Pickers ***

// FUNGSI INI DIHAPUS karena hanya memanggil updateModelValue() yang sekarang tidak diinginkan secara langsung
// const updateTimeValues = () => {
//   // Jika waktu diubah, pastikan ada tanggal untuk dikombinasikan
//   if (!dateValue.value) {
//     dateValue.value = dayjs().format("YYYY-MM-DD");
//   }
//   updateModelValue();
// };

const incrementHour = () => {
  hourValue.value = (hourValue.value + 1) % 24; // updateTimeValues(); // DIHAPUS
  if (!dateValue.value) {
    dateValue.value = dayjs().format("YYYY-MM-DD");
  }
};

const decrementHour = () => {
  hourValue.value = (hourValue.value - 1 + 24) % 24; // updateTimeValues(); // DIHAPUS
  if (!dateValue.value) {
    dateValue.value = dayjs().format("YYYY-MM-DD");
  }
};

const incrementMinute = () => {
  minuteValue.value = (minuteValue.value + 1) % 60;
  if (minuteValue.value === 0) {
    incrementHour();
  } else {
    // updateTimeValues(); // DIHAPUS
    if (!dateValue.value) {
      dateValue.value = dayjs().format("YYYY-MM-DD");
    }
  }
};

const decrementMinute = () => {
  minuteValue.value = (minuteValue.value - 1 + 60) % 60;
  if (minuteValue.value === 59) {
    decrementHour();
  } else {
    // updateTimeValues(); // DIHAPUS
    if (!dateValue.value) {
      dateValue.value = dayjs().format("YYYY-MM-DD");
    }
  }
};

const incrementSecond = () => {
  secondValue.value = (secondValue.value + 1) % 60;
  if (secondValue.value === 0) {
    incrementMinute();
  } else {
    // updateTimeValues(); // DIHAPUS
    if (!dateValue.value) {
      dateValue.value = dayjs().format("YYYY-MM-DD");
    }
  }
};

const decrementSecond = () => {
  secondValue.value = (secondValue.value - 1 + 60) % 60;
  if (secondValue.value === 59) {
    decrementMinute();
  } else {
    // updateTimeValues(); // DIHAPUS
    if (!dateValue.value) {
      dateValue.value = dayjs().format("YYYY-MM-DD");
    }
  }
};
// *** End Logic Custom Time Pickers ***

// Aturan validasi input (digunakan pada q-input)
const inputRules = computed(() => [
  ...props.rules,
  (val) => {
    // BARU: Menambahkan cek redundansi sebelum emit
    // 1. Abaikan validasi jika input kosong
    if (!val) {
      // emit("update:modelValue", null); // <--- LOGIKA LAMA
      if (props.modelValue !== null) {
        // Hanya emit jika modelValue BUKAN null
        emit("update:modelValue", null);
      }
      return true;
    }

    const datetimeObj = dayjs(val, props.displayFormat, true); // 2. Cek format tidak valid

    if (!datetimeObj.isValid()) {
      // emit("update:modelValue", null); // <--- LOGIKA LAMA
      if (props.modelValue !== null) {
        // Hanya emit jika modelValue BUKAN null
        emit("update:modelValue", null);
      }
      return "Format tanggal dan waktu tidak valid (e.g., DD/MM/YYYY HH:mm:ss)";
    } // Nilai baru yang valid

    const newDate = datetimeObj.toDate(); // Timestamp saat ini
    const currentTimestamp = props.modelValue ? props.modelValue.getTime() : -1; // Timestamp baru
    const newTimestamp = newDate.getTime();

    const minDateCleaned = props.minDate
      ? dayjs(props.minDate).startOf("day")
      : null;
    const maxDateCleaned = props.maxDate
      ? dayjs(props.maxDate).endOf("day")
      : null; // 3. Cek Min Date

    if (minDateCleaned && datetimeObj.isBefore(minDateCleaned)) {
      // BARU: Cek redundansi sebelum emit saat Min Date Error
      // emit("update:modelValue", datetimeObj.toDate()); // <--- LOGIKA LAMA
      if (currentTimestamp !== newTimestamp) {
        emit("update:modelValue", newDate);
      }
      return `Tanggal tidak boleh kurang dari ${minDateCleaned.format(
        props.displayFormat
      )}`;
    } // 4. Cek Max Date
    if (maxDateCleaned && datetimeObj.isAfter(maxDateCleaned)) {
      // BARU: Cek redundansi sebelum emit saat Max Date Error
      // emit("update:modelValue", datetimeObj.toDate()); // <--- LOGIKA LAMA
      if (currentTimestamp !== newTimestamp) {
        emit("update:modelValue", newDate);
      }
      return `Tanggal tidak boleh lebih dari ${maxDateCleaned.format(
        props.displayFormat
      )}`;
    } // 5. Jika valid, emit nilai ke modelValue // BARU: Cek redundansi // emit("update:modelValue", datetimeObj.toDate()); // <--- LOGIKA LAMA

    if (currentTimestamp !== newTimestamp) {
      emit("update:modelValue", newDate);
    }
    return true;
  },
]);

// Set nilai ke hari ini (hanya update state internal)
const setToday = () => {
  const now = dayjs();
  dateValue.value = now.format("YYYY-MM-DD");
  hourValue.value = now.hour();
  minuteValue.value = now.minute();
  secondValue.value = now.second(); // updateModelValue(); // DIHAPUS - Hanya di-commit saat OK diklik
};

// Fungsi Clear (hanya update state internal)
const clearDatetime = () => {
  dateValue.value = "";
  hourValue.value = 0;
  minuteValue.value = 0;
  secondValue.value = 0; // displayDatetime.value = ""; // TIDAK PERLU di sini, akan di-update saat OK // emit("update:modelValue", null); // DIHAPUS - Hanya di-commit saat OK diklik
};

// Fungsi Tutup Dialog (saat tombol OK diklik)
const closeDialog = () => {
  // MEMASTIKAN model value sudah di-update terakhir kali
  updateModelValue();
  isDialogOpen.value = false;
};

// BARU: Fungsi Batal Dialog (saat tombol Batal diklik)
const cancelDialog = () => {
  // 1. Reset working state ke nilai awal yang disimpan
  if (initialStagingValue.value) {
    const dayjsObj = initialStagingValue.value;
    dateValue.value = dayjsObj.format("YYYY-MM-DD");
    hourValue.value = dayjsObj.hour();
    minuteValue.value = dayjsObj.minute();
    secondValue.value = dayjsObj.second(); // Pastikan displayDatetime juga sinkron dengan nilai awal, // karena displayDatetime.value dipengaruhi oleh input manual/watcher
    displayDatetime.value = dayjsObj.format(props.displayFormat);
  } else {
    // Nilai awal adalah null
    dateValue.value = "";
    hourValue.value = 0;
    minuteValue.value = 0;
    secondValue.value = 0;
    displayDatetime.value = "";
  } // 2. Tutup dialog

  isDialogOpen.value = false;
  initialStagingValue.value = null;
};

// Fungsi options untuk q-date (memfilter tanggal yang diizinkan)
const dateOptions = (date) => {
  const checkDate = dayjs(date, "YYYY/MM/DD");
  const minDateCleaned = props.minDate
    ? dayjs(props.minDate).startOf("day")
    : null;
  const maxDateCleaned = props.maxDate
    ? dayjs(props.maxDate).endOf("day")
    : null;

  let isValid = true;

  if (minDateCleaned && checkDate.isBefore(minDateCleaned)) {
    isValid = false;
  }
  if (maxDateCleaned && checkDate.isAfter(maxDateCleaned)) {
    isValid = false;
  }

  return isValid;
};

// Watcher untuk sinkronisasi nilai eksternal (modelValue) ke internal state
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue instanceof Date) {
      const dayjsObj = dayjs(newValue);
      dateValue.value = dayjsObj.format("YYYY-MM-DD");
      hourValue.value = dayjsObj.hour();
      minuteValue.value = dayjsObj.minute();
      secondValue.value = dayjsObj.second(); // Hanya update displayDatetime jika dialog TIDAK terbuka, // atau jika ini adalah inisialisasi awal.
      if (!isDialogOpen.value) {
        displayDatetime.value = dayjsObj.format(props.displayFormat);
      }
    } else if (newValue === null || newValue === undefined) {
      dateValue.value = "";
      hourValue.value = 0;
      minuteValue.value = 0;
      secondValue.value = 0;
      displayDatetime.value = "";
    }
  },
  { immediate: true }
);

// Watcher untuk input manual displayDatetime
watch(displayDatetime, (newValue) => {
  // BARU: Menambahkan cek redundansi untuk input manual
  if (!newValue) {
    // emit("update:modelValue", null); // <--- LOGIKA LAMA
    if (props.modelValue !== null) {
      emit("update:modelValue", null);
    }
    return;
  } // Ketika user mengetik, coba parsing

  const datetimeObj = dayjs(newValue, props.displayFormat, true);
  if (datetimeObj.isValid()) {
    // Jika valid, update dateValue dan timeValue untuk sinkronisasi popup
    dateValue.value = datetimeObj.format("YYYY-MM-DD");
    hourValue.value = datetimeObj.hour();
    minuteValue.value = datetimeObj.minute();
    secondValue.value = datetimeObj.second(); // ModelValue akan di-emit oleh inputRules (yang kini memiliki cek redundansi)
  }
});
</script>

<style scoped>
/* Pastikan Q-Card di desktop tidak terlalu lebar */
.datetime-dialog-card {
  min-width: 320px;
  max-width: 450px;
  border-radius: 8px; /* Tampilan modern */
}

/* Tambahkan sedikit style untuk menyelaraskan kontrol jam/menit */
.text-h6 {
  min-width: 2.5em; /* memastikan angka dua digit tetap pada lebar yang sama */
}

/* START: CSS KUSTOM UNTUK MEMBUAT Q-DATE LEBIH PADAT */
.custom-q-date-compact {
  /* Hilangkan border radius bawaan dan shadow, agar tampak seperti PrimeVue */
  border-radius: 0;
  box-shadow: none;
  padding: 5px;
}

/* Menghilangkan header yang mungkin masih tersisa */
.custom-q-date-compact :deep(.q-date__header) {
  /* Ini adalah header utama */
  display: none !important;
  padding: 0;
  height: 0;
}

.custom-q-date-compact :deep(.q-date__content) {
  /* Hilangkan padding vertikal dari konten kalender */
  padding-top: 0;
  padding-bottom: 0;
}

.custom-q-date-compact :deep(.q-date__navigation) {
  /* Tambahkan padding horizontal yang sedikit lebih kecil pada navigasi bulan/tahun */
  padding: 8px 12px;
}

.custom-q-date-compact :deep(.q-date__calendar) {
  /* Atur ulang padding pada kalender utama */
  padding: 0 12px 8px 12px;
}

.custom-q-date-compact :deep(.q-date__calendar-item) {
  /* Kurangi ukuran sel kalender untuk tampilan yang lebih padat */
  width: 32px;
  height: 32px;
}

.custom-q-date-compact :deep(.q-btn) {
  /* Kurangi ukuran tombol di dalam kalender */
  min-width: 32px;
  height: 32px;
  padding: 0;
}
/* END: CSS KUSTOM UNTUK MEMBUAT Q-DATE LEBIH PADAT */

/* Penyesuaian responsif Quasar */
@media (max-width: 600px) {
  /* Pastikan q-card mengambil lebar/tinggi penuh saat maximized */
  .datetime-dialog-card.full-width.full-height {
    width: 100%;
    height: 100%;
    /* Atur ulang padding untuk tampilan mobile */
    border-radius: 0;
  }
}
</style>
