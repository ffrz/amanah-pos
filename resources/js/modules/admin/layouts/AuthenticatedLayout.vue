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

const LEFT_DRAWER_STORAGE_KEY = "shiftech-pos.layout.left-drawer-open";
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
            :active="$page.url.startsWith('/admin/home')"
            @click="router.get(route('admin.home'))"
          >
            <q-item-section avatar>
              <q-icon name="home" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Beranda</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-if="$can('admin.dashboard')"
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
          <q-item
            v-if="$can('admin.cashier-session.open')"
            clickable
            v-ripple
            @click="
              router.get(
                route(
                  'admin.cashier-session.' +
                    (page.props.auth.cashier_session ? 'close' : 'open'),
                  page.props.auth.cashier_session?.id
                )
              )
            "
          >
            <q-item-section avatar>
              <q-icon
                :name="page.props.auth.cashier_session ? 'logout' : 'login'"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label
                >{{ !page.props.auth.cashier_session ? "Mulai" : "Tutup" }} Sesi
                Kasir</q-item-label
              >
            </q-item-section>
          </q-item>
          <q-expansion-item icon="o_fast_forward" label="Pintasan">
            <q-item
              v-if="$can('admin.sales-order.edit')"
              class="subnav show-icon"
              clickable
              v-ripple
              @click="router.get(route('admin.sales-order.add'))"
            >
              <q-item-section avatar>
                <q-icon name="add_shopping_cart" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Penjualan Baru </q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-if="$can('admin.purchase-order.edit')"
              class="subnav show-icon"
              clickable
              v-ripple
              @click="router.get(route('admin.purchase-order.add'))"
            >
              <q-item-section avatar>
                <q-icon name="o_shopping_basket" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pembelian Baru </q-item-label>
              </q-item-section>
            </q-item>

            <q-separator />

            <q-item
              v-if="$can('admin.customer.add')"
              class="subnav show-icon"
              clickable
              v-ripple
              @click="router.get(route('admin.customer.add'))"
            >
              <q-item-section avatar>
                <q-icon name="person_add_alt" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pelanggan Baru </q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-if="$can('admin.supplier.add')"
              class="subnav show-icon"
              clickable
              v-ripple
              @click="router.get(route('admin.supplier.add'))"
            >
              <q-item-section avatar>
                <q-icon name="o_business_center" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Supplier Baru </q-item-label>
              </q-item-section>
            </q-item>

            <q-separator />

            <q-item
              v-if="$can('admin.product.add')"
              class="subnav show-icon"
              clickable
              v-ripple
              @click="router.get(route('admin.product.add'))"
            >
              <q-item-section avatar>
                <q-icon name="o_inventory_2" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Produk Baru </q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-expansion-item
            v-if="
              $can('admin.sales-order.index') ||
              $can('admin.customer.index') ||
              $can('admin.sales-order-return.index')
            "
            icon="storefront"
            label="Penjualan"
            :default-opened="
              $page.url.startsWith('/admin/sales-orders') ||
              $page.url.startsWith('/admin/sales-order-returns') ||
              $page.url.startsWith('/admin/customers') ||
              $page.url.startsWith('/admin/customer-ledgers') ||
              $page.url.startsWith('/admin/customer-wallet-transactions') ||
              $page.url.startsWith(
                '/admin/customer-wallet-transaction-confirmations'
              )
            "
          >
            <q-item
              v-if="$can('admin.sales-order.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/sales-orders')"
              @click="router.get(route('admin.sales-order.index'))"
            >
              <q-item-section avatar>
                <q-icon name="add_shopping_cart" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Penjualan</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.sales-order-return.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/sales-order-returns')"
              @click="router.get(route('admin.sales-order-return.index'))"
            >
              <q-item-section avatar>
                <q-icon name="remove_shopping_cart" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Retur Penjualan</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.customer.index')"
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
            <q-expansion-item
              class="subnav"
              v-if="
                $can('admin.customer-wallet-transaction-confirmation.index') ||
                $can('admin.customer-wallet-transaction.index')
              "
              icon="wallet"
              label="Deposit"
              :default-opened="
                $page.url.startsWith('/admin/customer-wallet-transactions') ||
                $page.url.startsWith(
                  '/admin/customer-wallet-transaction-confirmations'
                )
              "
            >
              <q-item
                v-if="
                  $can('admin.customer-wallet-transaction-confirmation.index')
                "
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
                    route(
                      'admin.customer-wallet-transaction-confirmation.index'
                    )
                  )
                "
              >
                <q-item-section avatar>
                  <q-icon name="add_task" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Konfirmasi Deposit</q-item-label>
                </q-item-section>
              </q-item>
              <q-item
                v-if="$can('admin.customer-wallet-transaction.index')"
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
                  <q-item-label>Transaksi Deposit</q-item-label>
                </q-item-section>
              </q-item>
            </q-expansion-item>
            <q-item
              v-if="$can('admin.customer-ledger.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/customer-ledgers')"
              @click="router.get(route('admin.customer-ledger.index'))"
            >
              <q-item-section avatar>
                <q-icon name="moving" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Piutang</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-expansion-item
            v-if="
              $can('admin.purchase-order.index') ||
              $can('admin.supplier.index') ||
              $can('admin.purchase-order-return.index')
            "
            icon="local_shipping"
            label="Pembelian"
            :default-opened="
              $page.url.startsWith('/admin/purchase-orders') ||
              $page.url.startsWith('/admin/purchase-order-returns') ||
              $page.url.startsWith('/admin/suppliers') ||
              $page.url.startsWith('/admin/supplier-ledgers') ||
              $page.url.startsWith('/admin/supplier-wallet-transactions')
            "
          >
            <q-item
              v-if="$can('admin.purchase-order.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/purchase-orders')"
              @click="router.get(route('admin.purchase-order.index'))"
            >
              <q-item-section avatar>
                <q-icon name="add_shopping_cart" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pembelian</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.purchase-order-return.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/purchase-order-returns')"
              @click="router.get(route('admin.purchase-order-return.index'))"
            >
              <q-item-section avatar>
                <q-icon name="remove_shopping_cart" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Retur Pembelian</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.supplier.index')"
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
                <q-item-label>Supplier</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.supplier-wallet-transaction.index')"
              class="subnav"
              clickable
              v-ripple
              :active="
                $page.url.startsWith('/admin/supplier-wallet-transactions')
              "
              @click="
                router.get(route('admin.supplier-wallet-transaction.index'))
              "
            >
              <q-item-section avatar>
                <q-icon name="moving" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Deposit</q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-if="$can('admin.supplier-ledger.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/supplier-ledgers')"
              @click="router.get(route('admin.supplier-ledger.index'))"
            >
              <q-item-section avatar>
                <q-icon name="moving" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Utang</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-expansion-item
            v-if="
              $can('admin.product.index') ||
              $can('admin.product-category.index') ||
              $can('admin.stock-adjustment.index') ||
              $can('admin.stock-movement.index')
            "
            icon="inventory_2"
            label="Inventori"
            :default-opened="
              $page.url.startsWith('/admin/products') ||
              $page.url.startsWith('/admin/product-categories') ||
              $page.url.startsWith('/admin/stock-adjustments') ||
              $page.url.startsWith('/admin/stock-movements') ||
              $page.url.startsWith('/admin/uoms')
            "
          >
            <q-item
              v-if="$can('admin.stock-adjustment.index')"
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
              v-if="$can('admin.stock-movement.index')"
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
              v-if="$can('admin.product.index')"
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
              v-if="$can('admin.product-category.index')"
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
            <q-item
              v-if="$can('admin.uom.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/uoms')"
              @click="router.get(route('admin.uom.index'))"
            >
              <q-item-section avatar>
                <q-icon name="bia" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Satuan Produk</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-item
            v-if="$config.FEATURE.SERVICE.ENABLED"
            clickable
            v-ripple
            @click="$window.location.href = '/service'"
          >
            <q-item-section avatar>
              <q-icon name="construction" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Servis</q-item-label>
            </q-item-section>
          </q-item>

          <q-expansion-item
            v-if="
              $can('admin.cashier-session.index') ||
              $can('admin.cashier-terminal.index') ||
              $can('admin.cashier-cash-drop.index')
            "
            icon="point_of_sale"
            label="Kasir"
            :default-opened="
              $page.url.startsWith('/admin/cashier-terminals') ||
              $page.url.startsWith('/admin/cashier-cash-drops') ||
              $page.url.startsWith('/admin/cashier-session')
            "
          >
            <q-item
              v-if="$can('admin.cashier-cash-drop.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/cashier-cash-drops')"
              @click="router.get(route('admin.cashier-cash-drop.index'))"
            >
              <q-item-section avatar>
                <q-icon name="send_money" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Setoran</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.cashier-session.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/cashier-sessions')"
              @click="router.get(route('admin.cashier-session.index'))"
            >
              <q-item-section avatar>
                <q-icon name="tv_signin" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Sesi Kasir</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.cashier-terminal.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/cashier-terminals')"
              @click="router.get(route('admin.cashier-terminal.index'))"
            >
              <q-item-section avatar>
                <q-icon name="point_of_sale" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Terminal Kasir</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-expansion-item
            v-if="
              $can('admin.finance-transaction.index') ||
              $can('admin.finance-account.index') ||
              $can('admin.finance-transaction-category.index') ||
              $can('admin.finance-transaction-tags.index')
            "
            icon="finance"
            label="Keuangan"
            :default-opened="
              $page.url.startsWith('/admin/finance-accounts') ||
              $page.url.startsWith('/admin/finance-transactions') ||
              $page.url.startsWith('/admin/finance-transaction-categories') ||
              $page.url.startsWith('/admin/finance-transaction-tags')
            "
          >
            <q-item
              v-if="$can('admin.finance-transaction.index')"
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
              v-if="$can('admin.finance-account.index')"
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
            <q-item
              v-if="$can('admin.finance-transaction-category.index')"
              class="subnav"
              clickable
              v-ripple
              :active="
                $page.url.startsWith('/admin/finance-transaction-categories')
              "
              @click="
                router.get(route('admin.finance-transaction-category.index'))
              "
            >
              <q-item-section avatar>
                <q-icon name="category" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Kategori Transaksi</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.finance-transaction-tags.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/finance-transaction-tags')"
              @click="router.get(route('admin.finance-transaction-tag.index'))"
            >
              <q-item-section avatar>
                <q-icon name="category" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Label Transaksi</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-expansion-item
            v-if="
              $can('admin.operational-cost.index') ||
              $can('admin.operational-cost-category.index')
            "
            icon="paid"
            label="Operasional"
            :default-opened="
              $page.url.startsWith('/admin/operational-costs') ||
              $page.url.startsWith('/admin/operational-cost-categories')
            "
          >
            <q-item
              v-if="$can('admin.operational-cost.index')"
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
              v-if="$can('admin.operational-cost-category.index')"
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
                <q-item-label>Kategori Biaya</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <q-item
            v-if="$can('admin.report.index')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/reports')"
            @click="router.get(route('admin.report.index'))"
          >
            <q-item-section avatar>
              <q-icon name="analytics" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Laporan</q-item-label>
            </q-item-section>
          </q-item>

          <q-expansion-item
            icon="settings"
            label="Pengaturan"
            :default-opened="
              $page.url.startsWith('/admin/settings') &&
              !$page.url.startsWith('/admin/settings/profile')
            "
          >
            <q-item
              v-if="$can('admin.user.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/settings/users')"
              @click="router.get(route('admin.user.index'))"
            >
              <q-item-section avatar>
                <q-icon name="person" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Pengguna</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.user-role.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/settings/user-roles')"
              @click="router.get(route('admin.user-role.index'))"
            >
              <q-item-section avatar>
                <q-icon name="group" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Peran Pengguna</q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-if="false && $can('admin.tax-scheme.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/settings/tax-schemes')"
              @click="router.get(route('admin.tax-scheme.index'))"
            >
              <q-item-section avatar>
                <q-icon name="credit_card_gear" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Skema Pajak</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              v-if="$can('admin.pos-settings.edit')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/settings/pos')"
              @click="router.get(route('admin.pos-settings.edit'))"
            >
              <q-item-section avatar>
                <q-icon name="point_of_sale" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Point of Sales</q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-if="$can('admin.company-profile.edit')"
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

            <q-item
              v-if="$can('admin.user-activity-log.index')"
              class="subnav"
              clickable
              v-ripple
              :active="
                $page.url.startsWith('/admin/settings/user-activity-log')
              "
              @click="router.get(route('admin.user-activity-log.index'))"
            >
              <q-item-section avatar>
                <q-icon name="article" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Log Aktivitas</q-item-label>
              </q-item-section>
            </q-item>

            <q-item
              v-if="$can('admin.database-settings.index')"
              class="subnav"
              clickable
              v-ripple
              :active="$page.url.startsWith('/admin/settings/database')"
              @click="router.get(route('admin.database-settings.index'))"
            >
              <q-item-section avatar>
                <q-icon name="database" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Database</q-item-label>
              </q-item-section>
            </q-item>
            <q-item
              class="subnav"
              clickable
              v-ripple
              :active="
                $page.url.startsWith('/admin/settings/system-information')
              "
              @click="router.get(route('admin.settings.system-information'))"
            >
              <q-item-section avatar>
                <q-icon name="info" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Informasi Sistem</q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>
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
