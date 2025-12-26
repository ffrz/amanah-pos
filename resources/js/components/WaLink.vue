<script setup>
import { computed } from "vue";
import { QIcon } from "quasar";
import { waMeUrl } from "@/helpers/utils";
import { cleanPhoneNumber, formatPhoneNumber } from "@/helpers/formatter";

const props = defineProps({
  phone: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    default: "",
  },
  display_text: {
    type: String,
    default: "",
  },
});

// URL WA tetap menggunakan nomor yang sudah bersih (angka saja)
const waLink = computed(() => {
  const clean = cleanPhoneNumber(props.phone);
  return waMeUrl(clean, props.message);
});

// Teks tampilan menggunakan helper formatPhoneNumber
const displayText = computed(() => {
  if (props.display_text) {
    return props.display_text;
  }
  return formatPhoneNumber(props.phone);
});

// Penentuan tampilan link
const shouldDisplay = computed(() => {
  const clean = cleanPhoneNumber(props.phone);
  return clean && waLink.value !== "#";
});
</script>

<template>
  <template v-if="shouldDisplay">
    <a
      :href="waLink"
      target="_blank"
      rel="noopener noreferrer"
      class="text-primary text-bold"
    >
      {{ displayText }}
      <q-icon name="open_in_new" size="xs" class="q-ml-xs" />
    </a>
  </template>
  <span v-else>{{ displayText }}</span>
</template>
