<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
  href: {
    type: String,
    default: "",
  },
  method: {
    type: String,
    default: "get",
  },
  label: {
    type: String,
    default: "",
  },
  target: {
    type: String,
    default: "_self",
  },
});

const openLink = () => {
  if (props.target == "_self") {
    router[props.method](props.href);
  }
  window.open(props.href, props.target);
};
</script>

<template>
  <a @click.prevent="openLink" :href="props.href" :target="props.target">
    <template v-if="props.label">
      {{ props.label }}
    </template>
    <template v-else>
      <slot></slot>
    </template>
  </a>
</template>

<style scoped>
a {
  cursor: pointer;
  text-decoration: none;
}
</style>
