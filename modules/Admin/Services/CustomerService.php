<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * 
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * 
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Services;

use App\Exceptions\ModelNotModifiedException;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\UserActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerService
{

    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService
    ) {}

    public function addToBalance(Customer $customer, $balance)
    {
        $lockedCustomer = Customer::where('id', $customer->id)->lockForUpdate()->firstOrFail();
        $lockedCustomer->balance += $balance;
        $lockedCustomer->save();
        return $lockedCustomer;
    }

    public function find(int $id): Customer
    {
        return Customer::with(['creator', 'updater'])->findOrFail($id);
    }

    public function findOrCreate($id): Customer
    {
        return $id ? $this->find($id) : new Customer([
            'active' => true,
            'type' => Customer::Type_General,
            'code' => $this->generateCustomerCode(),
            'default_price_type' => Product::PriceType_Price1,
        ]);
    }

    private function generateCustomerCode(): string
    {
        $lastId = Customer::max('id') ?? 0;
        $nextId = $lastId + 1;
        $code = str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $prefix = Setting::value('customer.code-prefix', 'CST-');
        return $prefix . $code;
    }

    public function duplicate(int $id): Customer
    {
        return $this->find($id)->replicate([
            'wallet_balance',
            'password',
            'last_login_datetime',
            'last_activity_description',
            'last_activity_datetime'
        ]);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = Customer::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'])->withQueryString();
    }

    public function save(Customer $item, array $data): Customer
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $oldData = $item->toArray();

        $item->fill($data);

        if (empty($item->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($item, $oldData) {
            $isNew = !$item->id;

            $item->save();

            $this->documentVersionService->createVersion($item);

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Customer,
                    UserActivityLog::Name_Customer_Create,
                    "Pelanggan ID: $item->id, $item->name telah dibuat.",
                    [
                        'formatter' => 'customer',
                        'data' => $item->toArray()
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Customer,
                    UserActivityLog::Name_Customer_Update,
                    "Pelanggan ID: $item->id, $item->name telah diperbarui.",
                    [
                        'formatter' => 'customer',
                        'new_data' => $item->toArray(),
                        'old_data' => $oldData
                    ]
                );
            }

            return $item;
        });
    }

    public function delete(Customer $item): Customer
    {
        return DB::transaction(function () use ($item) {
            $item->delete();

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_Customer,
                UserActivityLog::Name_Customer_Delete,
                "Pelanggan ID: $item->id, $item->name telah dihapus.",
                [
                    'formatter' => 'customer',
                    'data' => $item->toArray()
                ]
            );
            return $item;
        });
    }
}
