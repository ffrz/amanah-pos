<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SupplierService
{
    /**
     * Menyimpan (membuat atau memperbarui) data Pemasok.
     * Tidak ada transaksi, versioning, atau logging di sini.
     */
    public function save(array $validatedData, ?Supplier $supplier = null): Supplier
    {
        $item = $supplier ?? new Supplier();

        $item->fill($this->normalizeData($validatedData));

        $item->save();

        return $item;
    }

    /**
     * Menghapus Pemasok.
     */
    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    /**
     * Logika untuk mem-filter dan mengambil data Supplier.
     */
    public function getData(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Supplier::query();

        if (!empty($filter['search'])) {
            $q->where(function (Builder $q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone_1', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        return $q->paginate($request->get('per_page', 10))->withQueryString();
    }

    /**
     * Normalisasi data dari request (Memindahkan logika pengisian nilai default).
     */
    protected function normalizeData(array $data): array
    {
        $keys = [
            'phone_1',
            'phone_2',
            'phone_3',
            'address',
            'return_address',
            'bank_account_name_1',
            'bank_account_number_1',
            'bank_account_holder_1',
            'bank_account_name_2',
            'bank_account_number_2',
            'bank_account_holder_2',
            'url_1',
            'url_2',
            'notes'
        ];

        foreach ($keys as $key) {
            $data[$key] = $data[$key] ?? '';
        }

        return $data;
    }
}
