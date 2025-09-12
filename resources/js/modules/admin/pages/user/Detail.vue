<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Pengguna";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
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
          icon="edit"
          dense
          color="primary"
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
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-9">
                Profil Pengguna
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 125px">ID Pengguna</td>
                    <td style="width: 1px">:</td>
                    <td>{{ page.props.data.username }}</td>
                  </tr>
                  <tr>
                    <td>Nama Pengguna</td>
                    <td>:</td>
                    <td>{{ page.props.data.name }}</td>
                  </tr>
                  <tr>
                    <td>Hak Akses</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.USER_ROLES[page.props.data.role] }}</td>
                  </tr>
                  <tr>
                    <td>Akun Kas</td>
                    <td>:</td>
                    <td>
                      {{
                        page.props.data.cashier_account_id
                          ? page.props.data.cashier_account.name
                          : "-"
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Dibuat</td>
                    <td>:</td>
                    <td>
                      {{
                        $dayjs(page.props.data.created_at).format(
                          "DD MMMM YY HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Diperbarui</td>
                    <td>:</td>
                    <td>
                      {{
                        $dayjs(page.props.data.updated_at).format(
                          "DD MMMM YY HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Terakhir login</td>
                    <td>:</td>
                    <td>
                      {{
                        page.props.data.last_login_datetime
                          ? $dayjs(page.props.data.last_login_datetime).format(
                              "DD MMMM YYYY HH:mm:ss"
                            )
                          : "Belum pernah login"
                      }}
                    </td>
                  </tr>
                  <tr v-if="page.props.data.last_activity_datetime">
                    <td>Aktifitas Terakhir</td>
                    <td>:</td>
                    <td>
                      {{
                        $dayjs(page.props.data.last_activity_datetime).format(
                          "DD MMMM YYYY HH:mm:ss"
                        )
                      }}
                      <br />{{ page.props.data.last_activity_description }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
