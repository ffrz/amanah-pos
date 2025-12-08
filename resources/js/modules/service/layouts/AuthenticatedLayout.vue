<script setup>
import { defineComponent, onMounted, provide, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

defineComponent({
  name: "AuthenticatedLayout",
});

const props = defineProps({
  showDrawerButton: {
    type: Boolean,
    default: true,
  },
});

const LEFT_DRAWER_STORAGE_KEY = "shiftech-pos-service.layout.left-drawer-open";
const $q = useQuasar();
const page = usePage();
const leftDrawerOpen = ref(
  JSON.parse(localStorage.getItem(LEFT_DRAWER_STORAGE_KEY))
);
const isDropdownOpen = ref(false);
const toggleLeftDrawer = () => (leftDrawerOpen.value = !leftDrawerOpen.value);

const hideDrawer = () => {
  leftDrawerOpen.value = false;
};

watch(leftDrawerOpen, (newValue) => {
  localStorage.setItem(LEFT_DRAWER_STORAGE_KEY, newValue);
});

onMounted(() => {
  leftDrawerOpen.value = JSON.parse(
    localStorage.getItem(LEFT_DRAWER_STORAGE_KEY)
  );

  if ($q.screen.lt.md) {
    leftDrawerOpen.value = false;
  }
});

defineExpose({
  hideDrawer,
});
</script>

<template>
  <q-layout view="lHh LpR lFf">
    <q-header>
      <q-toolbar class="bg-grey-1 text-black toolbar-scrolled">
        <q-btn
          v-if="showDrawerButton && !leftDrawerOpen"
          flat
          dense
          aria-label="Menu"
          @click="toggleLeftDrawer"
        >
          <q-icon class="material-symbols-outlined">dock_to_right</q-icon>
        </q-btn>
        <template v-if="$q.screen.gt.sm">
          <div class="q-ml-sm">
            <slot name="left-button"></slot>
          </div>
        </template>
        <q-toolbar-title style="font-size: 16px">
          <slot name="title">{{ $config.APP_NAME }}</slot>
        </q-toolbar-title>
        <slot name="right-button"></slot>
      </q-toolbar>
      <slot name="header"></slot>
    </q-header>
    <q-drawer
      :breakpoint="768"
      v-model="leftDrawerOpen"
      bordered
      class="bg-grey-2"
      style="color: #444"
    >
      <div
        class="absolute-top"
        style="
          height: 50px;
          border-bottom: 1px solid #ddd;
          align-items: center;
          justify-content: center;
        "
      >
        <div
          style="
            width: 100%;
            padding: 8px;
            display: flex;
            justify-content: space-between;
          "
        >
          <q-btn-dropdown
            v-model="isDropdownOpen"
            class="profile-btn text-bold"
            flat
            :label="'🛠 ' + page.props.company.name"
            style="
              justify-content: space-between;
              flex-grow: 1;
              overflow: hidden;
            "
            :class="{ 'profile-btn-active': isDropdownOpen }"
          >
            <q-list id="profile-btn-popup" style="color: #444">
              <q-item>
                <q-avatar style="margin-left: -15px"
                  ><q-icon name="person"
                /></q-avatar>
                <q-item-section>
                  <q-item-label>
                    <my-link
                      class="text-bold text-grey-9"
                      :href="route('admin.user-profile.edit')"
                    >
                      {{ page.props.auth.user.name }}
                    </my-link>
                  </q-item-label>
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item
                clickable
                v-close-popup
                v-ripple
                style="color: inherit"
                :href="route('admin.auth.logout')"
              >
                <q-item-section>
                  <q-item-label
                    ><q-icon name="logout" class="q-mr-sm" />
                    Keluar</q-item-label
                  >
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
          <q-btn
            v-if="leftDrawerOpen"
            flat
            dense
            aria-label="Menu"
            @click="toggleLeftDrawer"
          >
            <q-icon name="dock_to_right" />
          </q-btn>
        </div>
      </div>
      <q-scroll-area style="height: calc(100% - 50px); margin-top: 50px">
        <q-list id="main-nav" style="margin-bottom: 50px">
          <q-item
            clickable
            v-ripple
            :active="$page.url.startsWith('/service/home')"
            @click="router.get(route('service.home'))"
          >
            <q-item-section avatar>
              <q-icon name="home" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Beranda</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-if="$can('service.dashboard')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/service/dashboard')"
            @click="router.get(route('service.dashboard'))"
          >
            <q-item-section avatar>
              <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Dashboard</q-item-label>
            </q-item-section>
          </q-item>

          <q-item
            v-if="$can('service.report.index')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/service/reports')"
            @click="router.get(route('service.report.index'))"
          >
            <q-item-section avatar>
              <q-icon name="analytics" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Laporan</q-item-label>
            </q-item-section>
          </q-item>

          <q-item clickable v-ripple @click="window.location.href = '/admin'">
            <q-item-section avatar>
              <q-icon name="apps" />
            </q-item-section>
            <q-item-section>
              <q-item-label>POS</q-item-label>
            </q-item-section>
          </q-item>

          <div class="absolute-bottom text-grey-6 q-pa-md">
            &copy; 2025 -
            {{ $config.APP_NAME + " v" + $config.APP_VERSION_STR }}
          </div>
        </q-list>
      </q-scroll-area>
    </q-drawer>
    <q-page-container class="bg-grey-1">
      <q-page>
        <slot></slot>
      </q-page>
    </q-page-container>
    <slot name="footer"></slot>
  </q-layout>
</template>

<style>
.profile-btn span.block {
  text-align: left !important;
  width: 100% !important;
  margin-left: 10px !important;
}
</style>
<style scoped>
.q-toolbar {
  border-bottom: 1px solid transparent;
  /* Optional border line */
}

.toolbar-scrolled {
  box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);
  /* Add shadow */
  border-bottom: 1px solid #ddd;
  /* Optional border line */
}

.profile-btn-active {
  background-color: #ddd !important;
}

#profile-btn-popup .q-item--active {
  color: inherit !important;
}
</style>
