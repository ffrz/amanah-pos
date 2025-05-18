<script setup>
import { usePage, router } from '@inertiajs/vue3';
import { formatNumber } from '@/helpers/utils';

const page = usePage();

const goToUrl = (url, newFilter) => {
  const baseFilter = { order_status: 'all', service_status: 'all', repair_status: 'all', payment_status: 'all', search: '' };
  const mergedFilter = { ...baseFilter, ...newFilter };
  localStorage.setItem('fixsync.service-orders.filter', JSON.stringify(mergedFilter));
  router.visit(url, {
    data: mergedFilter
  });
}

</script>


<template>
  <div class="row">
    <q-card class="bg-transparent no-shadow no-border col" bordered>
      <q-card-section class="q-pa-none">
        <div class="row q-col-gutter-sm ">
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-item :style="`background-color: #fd8e24`" class="q-pa-none" clickable
              @click="goToUrl(route('admin.service-order.index'), { order_status: 'open' })">
              <q-item-section side :style="`background-color: #fd7e14`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="handyman" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  {{ $page.props.data.active_order_count }}
                </q-item-label>
                <q-item-label>{{ $t('active_order') }}</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-item :style="`background-color: #dc3545`" class="q-pa-none" clickable
              @click="goToUrl(route('admin.service-order.index'), { order_status: 'open', service_status: 'received' })">
              <q-item-section side :style="`background-color: #cc2535`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="handyman" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  {{ $page.props.data.received_order_count }}
                </q-item-label>
                <q-item-label>{{ $t('not_yet_checked') }}</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-item :style="`background-color: #20c997`" class="q-pa-none" clickable
              @click="goToUrl(route('admin.service-order.index'), { order_status: 'open', service_status: 'in_progress' })">
              <q-item-section side :style="`background-color: #10b987`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="handyman" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  {{ $page.props.data.in_progress_order_count }}
                </q-item-label>
                <q-item-label>{{ $t('in_progress') }}</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-item :style="`background-color: #28a745`" class="q-pa-none" clickable
            @click="goToUrl(route('admin.service-order.index'), { order_status: 'open', service_status: 'completed' })">
              <q-item-section side :style="`background-color: #189735`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="handyman" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  {{ $page.props.data.pickable_order_count }}
                </q-item-label>
                <q-item-label>Siap Diambil</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <div class="col-md-4 col-xs-12">
            <q-item :style="`background-color: #e83e8c`" class="q-pa-none" clickable
            @click="goToUrl(route('admin.service-order.index'), { order_status: 'open', service_status: 'completed' })">
              <q-item-section side :style="`background-color: #d82e7c`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="request_quote" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  Rp. {{ formatNumber($page.props.data.total_active_bill) }}
                </q-item-label>
                <q-item-label>Total Tagihan Aktif</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <div class="col-md-4 col-xs-12">
            <q-item :style="`background-color: #e83e8c`" class="q-pa-none" clickable
            @click="goToUrl(route('admin.service-order.index'), { order_status: 'open', service_status: 'completed' })">
              <q-item-section side :style="`background-color: #d82e7c`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="request_quote" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  Rp. {{ formatNumber($page.props.data.total_active_downpayment) }}
                </q-item-label>
                <q-item-label>Total Uang Muka</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <div class="col-md-4 col-xs-12">
            <q-item :style="`background-color: #e83e8c`" class="q-pa-none" clickable
            @click="goToUrl(route('admin.service-order.index'), { order_status: 'open', service_status: 'completed' })">
              <q-item-section side :style="`background-color: #d82e7c`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="request_quote" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  Rp. {{ formatNumber($page.props.data.total_billable_order) }}
                </q-item-label>
                <q-item-label>Total Sisa Tagihan</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <div class="col-sm-6 col-xs-12">
            <q-item :style="`background-color: #008cff`" class="q-pa-none" clickable
              @click="$inertia.visit(route('admin.customer.index', { status: 'active' }))">
              <q-item-section side :style="`background-color: #007bff`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="groups_2" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  {{ $page.props.data.active_technician_count }}
                </q-item-label>
                <q-item-label>Teknisi Aktif</q-item-label>
              </q-item-section>
            </q-item>
          </div>
          <div class="col-sm-6 col-xs-12">
            <q-item :style="`background-color: #008cff`" class="q-pa-none" clickable
              @click="$inertia.visit(route('admin.customer.index', { status: 'active' }))">
              <q-item-section side :style="`background-color: #007bff`" class="q-pa-lg q-mr-none text-white">
                <q-icon class="material-filled" name="groups_2" color="white" size="24px" />
              </q-item-section>
              <q-item-section class=" q-pa-md q-ml-none  text-white">
                <q-item-label class="text-white text-h6 text-weight-bolder">
                  {{ $page.props.data.active_customer_count }}
                </q-item-label>
                <q-item-label>Santri Aktif</q-item-label>
              </q-item-section>
            </q-item>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>
