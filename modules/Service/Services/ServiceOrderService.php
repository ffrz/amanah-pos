<?php

namespace Modules\Service\Services;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Models\UserActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\CustomerService;
use Modules\Admin\Services\DocumentVersionService;
use Modules\Admin\Services\UserActivityLogService;

class ServiceOrderService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
        protected CustomerService $customerService
    ) {}

    public function find(int $id): ServiceOrder
    {
        return ServiceOrder::with([
            'customer',
            'technician',
            'receivedBy:id,name,username',
            'closedBy:id,name,username',
            'creator:id,name,username',
            'updater:id,name,username'
        ])->findOrFail($id);
    }

    public function findOrCreate($id): ServiceOrder
    {
        return $id ? $this->find($id) : new ServiceOrder([
            'received_datetime' => now(),
            'order_status'      => ServiceOrder::OrderStatus_Open,
            'service_status'    => ServiceOrder::ServiceStatus_Received,
            'payment_status'    => ServiceOrder::PaymentStatus_Unpaid,
            'repair_status'     => ServiceOrder::RepairStatus_Pending,
            'received_by_uid'   => Auth::id(),
        ]);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'] ?? [];

        $q = ServiceOrder::query()->with(['customer:id,name', 'technician:id,name']);

        if (!empty($filter['search'])) {
            $search = '%' . $filter['search'] . '%';
            $q->where(function ($q) use ($search) {
                $q->where('order_code', 'like', $search)
                    ->orWhere('customer_name', 'like', $search)
                    ->orWhere('customer_phone', 'like', $search)
                    ->orWhere('device', 'like', $search);
            });
        }

        foreach (['order_status', 'service_status', 'payment_status', 'repair_status'] as $f) {
            if (!empty($filter[$f]) && $filter[$f] !== 'all') {
                $q->where($f, $filter[$f]);
            }
        }

        return $q->orderBy($options['order_by'], $options['order_type'])
            ->paginate($options['per_page']);
    }

    public function save(ServiceOrder $item, array $data): ServiceOrder
    {
        $oldData = $item->toArray();
        $item->fill($data);

        return DB::transaction(function () use ($item, $data, $oldData) {
            $isNew = !$item->exists;

            // 1. Logika Customer Otomatis (On-the-fly)
            // Jika customer_id kosong atau 0 (sesuai input dari Editor.vue)
            if (empty($data['customer_id'])) {
                $newCustomer = Customer::create([
                    // Menggunakan service yang sudah kita inject di constructor
                    'code'    => $this->customerService->generateCustomerCode(),
                    'name'    => $data['customer_name'],
                    'phone'   => $data['customer_phone'],
                    'address' => $data['customer_address'],
                    'active'  => true,
                ]);

                // Set ID customer yang baru dibuat ke item ServiceOrder
                $item->customer_id = $newCustomer->id;

                // Opsional: Catat log aktivitas pembuatan customer otomatis
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Customer,
                    UserActivityLog::Name_Customer_Create,
                    "Pelanggan #{$newCustomer->id} ({$newCustomer->name}) dibuat otomatis via Order Service.",
                    ['data' => $newCustomer->toArray()]
                );
            }

            // 2. Logika Closing Order
            if ($item->order_status === ServiceOrder::OrderStatus_Closed) {
                if (!$item->closed_by_uid) {
                    $item->closed_by_uid = Auth::id();
                    $item->closed_datetime = now();
                }
            } else {
                // Jika status dirubah kembali ke open/canceled, hapus data closing
                $item->closed_by_uid = null;
                $item->closed_datetime = null;
            }

            // Simpan Service Order
            $item->save();

            // Buat versi dokumen untuk audit trail
            $this->documentVersionService->createVersion($item);

            // 3. Activity Logging Service Order
            $logName = $isNew ? 'service.service-order.create' : 'service.service-order.update';
            $this->userActivityLogService->log(
                'service.service-order',
                $logName,
                "Servis Order {$item->order_code} telah " . ($isNew ? "dibuat" : "diperbarui"),
                [
                    'new_data' => $item->toArray(),
                    'old_data' => $isNew ? null : $oldData
                ]
            );

            return $item;
        });
    }

    public function delete(ServiceOrder $item): bool
    {
        return DB::transaction(function () use ($item) {
            $this->documentVersionService->createDeletedVersion($item);
            $this->userActivityLogService->log(
                'service.service-order',
                'service.service-order.delete',
                "Servis Order {$item->order_code} dihapus",
                ['data' => $item->toArray()]
            );
            return $item->delete();
        });
    }
}
