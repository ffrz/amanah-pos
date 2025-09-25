<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const title = "Rincian Role Pengguna";

const groupedPermissions = computed(() => {
  const groups = {};
  if (page.props.data.permissions) {
    page.props.data.permissions.forEach((permission) => {
      const category = permission.category;

      if (!groups[category]) {
        groups[category] = [];
      }
      groups[category].push(permission);
    });
  }
  return groups;
});
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
          @click="$goBack()"
        />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="edit"
          dense
          color="grey"
          size="sm"
          rounded
          flat
          @click="
            router.get(
              route('admin.user-role.edit', { id: page.props.data.id })
            )
          "
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-xs">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">Info Role</div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 100px">Nama Role</td>
                    <td style="width: 1px">:</td>
                    <td>{{ page.props.data.name }}</td>
                  </tr>
                  <tr>
                    <td>Deskripsi</td>
                    <td>:</td>
                    <td>{{ page.props.data.description }}</td>
                  </tr>
                </tbody>
              </table>
              <div class="text-subtitle1 text-bold text-grey-8 q-mt-md">
                Daftar Pengguna
              </div>
              <div v-if="page.props.data.users.length > 0">
                <div v-for="user in page.props.data.users">
                  ● {{ user.username }} - {{ user.name }}
                </div>
              </div>
              <div v-else class="text-grey-8 text-italic">
                Belum ada akun pengguna yang terdaftar.
              </div>

              <div class="text-subtitle1 text-bold text-grey-8 q-mt-md">
                Daftar Hak Akses
              </div>
              <div v-if="page.props.data.permissions.length > 0">
                <div
                  v-for="(permissions, category) in groupedPermissions"
                  :key="category"
                >
                  <div class="text-subtitle2 text-bold text-grey-8 q-mt-xs">
                    {{ category }}
                  </div>
                  <div v-for="permission in permissions" :key="permission.id">
                    ● {{ permission.label }}
                  </div>
                </div>
              </div>
              <div v-else class="text-grey-8 text-italic">
                Belum ada hak akses yang ditetapkan.
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
