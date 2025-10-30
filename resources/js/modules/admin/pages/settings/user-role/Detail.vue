<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const title = "Rincian Role Pengguna";

const groupedPermissions = computed(() => {
  const groups = {};
  if (page.props.data.permissions) {
    page.props.data.permissions.forEach((permission) => {
      const rawCategory = permission.category;
      const displayCategory = rawCategory.replace("Manajemen ", "");

      if (!groups[displayCategory]) {
        groups[displayCategory] = [];
      }
      groups[displayCategory].push(permission);
    });
  }
  return groups;
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout :show-drawer-button="true">
    <template #title>{{ title }}</template>
    <template #left-button> </template>
    <template #right-button> </template>
    <q-page class="row justify-center q-pa-xs">
      <div class="col col-md-8">
        <div class="q-col-gutter-xs row">
          <div class="col-xs-12 col-md-5">
            <q-card square flat bordered class="full-height full-width">
              <q-card-section class="bg-grey-2 q-py-sm">
                <div class="row items-center">
                  <q-icon
                    name="assignment_ind"
                    size="sm"
                    color="grey-8"
                    class="q-mr-sm"
                  />
                  <div class="text-subtite1 text-bold text-grey-8">
                    Info Peran
                  </div>
                </div>
              </q-card-section>

              <q-card-section>
                <div class="text-subtite1 text-bold q-mb-sm">
                  {{ page.props.data.name }}
                </div>

                <div class="text-subtitle2 text-grey-7">Deskripsi:</div>
                <div class="text-body2">
                  {{ page.props.data.description || "Tidak ada deskripsi." }}
                </div>

                <div class="text-subtitle2 text-bold text-grey-8 q-my-sm">
                  <q-icon name="group" class="q-mr-xs" /> Pengguna Terdaftar
                </div>
                <div
                  v-if="page.props.data.users.length > 0"
                  class="q-gutter-xs"
                >
                  <i-link
                    v-for="user in page.props.data.users"
                    :key="user.id"
                    :href="route('admin.user.detail', { id: user.id })"
                    style="text-decoration: none"
                  >
                    <q-chip clickable text-color="primary" color="grey-3">
                      {{ user.username }}
                    </q-chip>
                  </i-link>
                </div>
                <div v-else class="text-grey-8 text-italic">
                  Belum ada akun pengguna yang terdaftar.
                </div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-xs-12 col-md-7">
            <q-card square flat bordered class="full-height full-width">
              <q-card-section class="bg-grey-3 q-py-sm">
                <div class="row items-center">
                  <q-icon
                    name="security"
                    size="sm"
                    color="grey-8"
                    class="q-mr-sm"
                  />
                  <div class="text-subtite1 text-grey-8 text-bold">
                    Daftar Hak Akses
                  </div>
                </div>
              </q-card-section>

              <q-card-section
                v-if="page.props.data.permissions.length > 0"
                class="q-pa-none"
              >
                <q-list bordered separator>
                  <q-expansion-item
                    v-for="(permissions, category) in groupedPermissions"
                    :key="category"
                    :label="category"
                    header-class="text-bold bg-grey-1 text-grey-7"
                    default-opened
                  >
                    <q-card-section class="q-py-sm">
                      <div>
                        <q-chip
                          v-for="permission in permissions"
                          :key="permission.id"
                          color="grey-2"
                          text-color="grey-9"
                          size="sm"
                        >
                          {{ permission.label }}
                        </q-chip>
                      </div>
                    </q-card-section>
                  </q-expansion-item>
                </q-list>
              </q-card-section>

              <q-card-section v-else>
                <div class="text-grey-7 text-italic">
                  Belum ada hak akses yang ditetapkan.
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>

<style scoped>
/* Anda bisa menambahkan styling di sini jika diperlukan */
</style>
