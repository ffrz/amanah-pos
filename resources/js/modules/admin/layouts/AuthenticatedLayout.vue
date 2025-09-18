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

const LEFT_DRAWER_STORAGE_KEY = "amanah-pos.layout.left-drawer-open";
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
        <slot name="left-button"></slot>
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
            :label="page.props.company.name"
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
                    <div class="text-bold">{{ page.props.auth.user.name }}</div>
                    <div class="text-grey-8 text-caption">
                      {{ $CONSTANTS.USER_ROLES[page.props.auth.user.role] }} @
                      {{ page.props.company.name }}
                    </div>
                  </q-item-label>
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item
                v-close-popup
                class="subnav"
                clickable
                v-ripple
                :active="$page.url.startsWith('/admin/settings/profile')"
                @click="router.get(route('admin.profile.edit'))"
              >
                <q-item-section>
                  <q-item-label
                    ><q-icon name="manage_accounts" class="q-mr-sm" /> Profil
                    Saya</q-item-label
                  >
                </q-item-section>
              </q-item>
              <q-item
                v-close-popup
                v-if="$can('admin.company-profile.edit')"
                class="subnav"
                clickable
                v-ripple
                :active="
                  $page.url.startsWith('/admin/settings/company-profile')
                "
                @click="router.get(route('admin.company-profile.edit'))"
              >
                <q-item-section>
                  <q-item-label
                    ><q-icon name="home_work" class="q-mr-sm" /> Profil
                    Perusahaan</q-item-label
                  >
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
            :active="$page.url.startsWith('/admin/dashboard')"
            @click="router.get(route('admin.dashboard'))"
          >
            <q-item-section avatar>
              <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Dashboard</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
          <q-expansion-item
            v-if="
              $page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN ||
              $page.props.auth.user.role == $CONSTANTS.USER_ROLE_CASHIER
            "
            icon="storefront"
            label="Penjualan"
            :default-opened="
              $page.url.startsWith('/admin/sales-orders') ||
              $page.url.startsWith('/admin/customers')
            "
          >
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/sales-orders')"
              @click="router.get(route('admin.sales-order.index'))"
            >
              <q-item-section avatar>
                <q-icon name="shopping_cart" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Penjualan</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/customers')"
              @click="router.get(route('admin.customer.index'))"
            >
              <q-item-section avatar>
                <q-icon name="people" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pelanggan</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-expansion-item
            v-if="
              $page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN ||
              $page.props.auth.user.role == $CONSTANTS.USER_ROLE_CASHIER
            "
            icon="local_shipping"
            label="Pembelian"
            :default-opened="
              $page.url.startsWith('/admin/purchase-orders') ||
              $page.url.startsWith('/admin/suppliers')
            "
          >
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/purchase-orders')"
              @click="router.get(route('admin.purchase-order.index'))"
            >
              <q-item-section avatar>
                <q-icon name="shopping_cart" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pembelian</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/suppliers')"
              @click="router.get(route('admin.supplier.index'))"
            >
              <q-item-section avatar>
                <q-icon name="people" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pemasok</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-expansion-item
            v-if="
              $page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN ||
              $page.props.auth.user.role == $CONSTANTS.USER_ROLE_CASHIER
            "
            icon="inventory_2"
            label="Inventori"
            :default-opened="
              $page.url.startsWith('/admin/products') ||
              $page.url.startsWith('/admin/product-categories') ||
              $page.url.startsWith('/admin/stock-adjustments') ||
              $page.url.startsWith('/admin/stock-movements')
            "
          >
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/stock-adjustments')"
              @click="router.get(route('admin.stock-adjustment.index'))"
            >
              <q-item-section avatar>
                <q-icon name="swap_vert" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Penyesuaian Stok</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/stock-movements')"
              @click="router.get(route('admin.stock-movement.index'))"
            >
              <q-item-section avatar>
                <q-icon name="timeline" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Riwayat Stok</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/products')"
              @click="router.get(route('admin.product.index'))"
            >
              <q-item-section avatar>
                <q-icon name="box" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Produk</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/product-categories')"
              @click="router.get(route('admin.product-category.index'))"
            >
              <q-item-section avatar>
                <q-icon name="category" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Kategori Produk</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-separator />

          <q-expansion-item
            v-if="$page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN"
            icon="wallet"
            label="Wallet"
            :default-opened="
              $page.url.startsWith('/admin/customer-wallet-transactions') ||
              $page.url.startsWith(
                '/admin/customer-wallet-transaction-confirmations'
              )
            "
          >
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="
                $page.url.startsWith(
                  '/admin/customer-wallet-transaction-confirmations'
                )
              "
              @click="
                router.get(
                  route('admin.customer-wallet-transaction-confirmation.index')
                )
              "
            >
              <q-item-section avatar>
                <q-icon name="add_task" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Konfirmasi Top Up</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="
                $page.url.startsWith('/admin/customer-wallet-transactions')
              "
              @click="
                router.get(route('admin.customer-wallet-transaction.index'))
              "
            >
              <q-item-section avatar>
                <q-icon name="moving" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Transaksi</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-separator />

          <q-expansion-item
            v-if="$page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN"
            icon="finance"
            label="Keuangan"
            :default-opened="
              $page.url.startsWith('/admin/finance-accounts') ||
              $page.url.startsWith('/admin/finance-transactions')
            "
          >
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/finance-transactions')"
              @click="router.get(route('admin.finance-transaction.index'))"
            >
              <q-item-section avatar>
                <q-icon name="moving" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Transaksi</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/finance-accounts')"
              @click="router.get(route('admin.finance-account.index'))"
            >
              <q-item-section avatar>
                <q-icon name="groups_2" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Akun Kas</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-expansion-item
            v-if="$page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN"
            icon="paid"
            label="Operasional"
            :default-opened="
              $page.url.startsWith('/admin/operational-costs') ||
              $page.url.startsWith('/admin/operational-cost-categories')
            "
          >
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/operational-costs')"
              @click="router.get(route('admin.operational-cost.index'))"
            >
              <q-item-section avatar>
                <q-icon name="request_quote" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Biaya Operasional</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="
                $page.url.startsWith('/admin/operational-cost-categories')
              "
              @click="
                router.get(route('admin.operational-cost-category.index'))
              "
            >
              <q-item-section avatar>
                <q-icon name="category" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Kategori</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>
          <q-separator />
          <q-expansion-item
            icon="settings"
            label="Pengaturan"
            :default-opened="$page.url.startsWith('/admin/settings')"
          >
            <q-item
              v-if="$page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/settings/users')"
              @click="router.get(route('admin.user.index'))"
            >
              <q-item-section avatar>
                <q-icon name="group" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pengguna</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/settings/profile')"
              @click="router.get(route('admin.profile.edit'))"
            >
              <q-item-section avatar>
                <q-icon name="manage_accounts" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Profil Saya</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/settings/company-profile')"
              @click="router.get(route('admin.company-profile.edit'))"
            >
              <q-item-section avatar>
                <q-icon name="apartment" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Profil Perusahaan</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>
          <q-separator />
          <q-item
            clickable
            v-close-popup
            v-ripple
            style="color: inherit"
            :href="route('admin.auth.logout')"
          >
            <q-item-section avatar>
              <q-icon name="logout" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Logout</q-item-label>
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
