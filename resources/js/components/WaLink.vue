<script setup>
import { computed } from "vue";
import { QIcon } from "quasar"; // Pastikan QIcon diimpor jika menggunakan Quasar
import { waMeUrl } from "@/helpers/utils";
// Import helper wa_me_url Anda (Sesuaikan path sesuai struktur proyek Anda)

const props = defineProps({
  // Nomor telepon yang akan diolah (wajib)
  phone: {
    type: String,
    required: true,
  },
  // Pesan WhatsApp default (opsional)
  message: {
    type: String,
    default: "",
  },
  // Teks yang ditampilkan (opsional, default: nomor telepon)
  display_text: {
    type: String,
    default: "",
  },
});

// Computed property untuk menghasilkan URL
const waLink = computed(() => {
  return waMeUrl(props.phone, props.message);
});

// Computed property untuk menentukan teks yang akan ditampilkan
const displayText = computed(() => {
  if (props.display_text) {
    return props.display_text;
  }
  // Jika nomor telepon kosong atau helper mengembalikan '#', tampilkan "-"
  return props.phone && waLink.value !== "#" ? props.phone : "-";
});

// Computed property untuk menentukan apakah link harus ditampilkan
const shouldDisplay = computed(() => {
  // Tampilkan jika ada nomor telepon DAN URL valid (bukan '#')
  return props.phone && waLink.value !== "#";
});
</script>

<template>
  <template v-if="shouldDisplay">
    <a :href="waLink" target="_blank" rel="noopener noreferrer">
      {{ displayText }}
      <q-icon name="open_in_new" size="xs" class="q-ml-xs" />
    </a>
  </template>
  <span v-else>{{ displayText }}</span>
</template>
