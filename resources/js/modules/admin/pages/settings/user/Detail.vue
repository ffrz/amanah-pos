<script setup>
import { formatDateTime } from "@/helpers/formatter";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const title = "Rincian Pengguna";

// Computed properties untuk data yang sering digunakan
const user = computed(() => page.props.data);
const statusColor = computed(() =>
  user.value.active ? "positive" : "negative"
);
const statusLabel = computed(() =>
  user.value.active ? "Aktif" : "Tidak Aktif"
);

const getUpdaterName = (updater) => {
  if (updater) {
    return `oleh ${updater.name} (${updater.username})`;
  }
  return "";
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="$inertia.get(route('admin.user.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          v-if="
            $can('admin.user.edit') &&
            page.props.data.id != page.props.auth.user.id
          "
          icon="edit"
          dense
          rounded
          flat
          size="sm"
          color="grey"
          @click="
            $inertia.get(route('admin.user.edit', { id: page.props.data.id }))
          "
        />
      </div>
    </template>

    <div class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section class="bg-grey-2 q-py-sm">
              <div class="row items-center justify-between">
                <div class="text-subtitle1 text-bold text-grey-9">
                  <q-icon name="person" class="q-mr-xs" size="sm" /> Profil
                  Pengguna
                </div>
                <q-badge
                  :color="statusColor"
                  :label="statusLabel"
                  class="q-pa-xs"
                />
              </div>
            </q-card-section>

            <q-list>
              <q-item class="bg-white">
                <q-item-section>
                  <q-item-label caption>Nama Pengguna</q-item-label>
                  <q-item-label class="text-subtitle1 text-bold">{{
                    user.name
                  }}</q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section>
                  <q-item-label caption>ID Pengguna</q-item-label>
                  <q-item-label>{{ user.username }}</q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section>
                  <q-item-label caption>Jenis Akun</q-item-label>
                  <q-item-label>{{
                    $CONSTANTS.USER_TYPES[user.type]
                  }}</q-item-label>
                </q-item-section>
              </q-item>

              <q-item v-if="user.roles.length > 0">
                <q-item-section>
                  <q-item-label caption>Peran Pengguna</q-item-label>
                  <q-item-label>
                    <div class="q-gutter-xs">
                      <i-link
                        v-for="role in user.roles"
                        :key="role.id"
                        :href="route('admin.user-role.detail', { id: role.id })"
                        style="text-decoration: none"
                      >
                        <q-chip
                          clickable
                          color="indigo-1"
                          text-color="indigo-10"
                          icon="military_tech"
                          size="sm"
                        >
                          {{ role.name }}
                        </q-chip>
                      </i-link>
                    </div>
                  </q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section>
                  <q-item-label caption>Dibuat</q-item-label>
                  <q-item-label>
                    {{ formatDateTime(user.created_at) }} oleh
                    <span class="text-bold"
                      >{{ user.creator?.name ?? "Sistem" }} ({{
                        user.creator?.username ?? "system"
                      }})</span
                    >
                  </q-item-label>
                </q-item-section>
              </q-item>

              <q-item v-if="user.updated_at">
                <q-item-section>
                  <q-item-label caption>Diperbarui</q-item-label>
                  <q-item-label>
                    {{ formatDateTime(user.updated_at) }}
                    <template v-if="user.updater">
                      oleh
                      <span class="text-bold"
                        >{{ user.updater.name }} ({{
                          user.updater.username
                        }})</span
                      >
                    </template>
                  </q-item-label>
                </q-item-section>
              </q-item>

              <q-item>
                <q-item-section>
                  <q-item-label caption>Terakhir Login</q-item-label>
                  <q-item-label class="text-bold">
                    {{
                      user.last_login_datetime
                        ? $dayjs(user.last_login_datetime).format(
                            "DD MMMM YYYY HH:mm:ss"
                          )
                        : "Belum pernah login"
                    }}
                  </q-item-label>
                </q-item-section>
              </q-item>

              <q-item v-if="user.last_activity_datetime" class="q-mb-md">
                <q-item-section>
                  <q-item-label caption>Aktifitas Terakhir</q-item-label>
                  <q-item-label class="text-bold">
                    {{
                      $dayjs(user.last_activity_datetime).format(
                        "DD MMMM YYYY HH:mm:ss"
                      )
                    }}
                    {{ user.last_activity_description }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
