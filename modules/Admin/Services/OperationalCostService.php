<?php

namespace Modules\Admin\Services;

use App\Helpers\ImageUploaderHelper;
use App\Models\FinanceTransaction;
use App\Models\OperationalCost;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OperationalCostService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
        protected FinanceTransactionService $financeTransactionService,
    ) {}

    public function duplicate($id)
    {
        $item = $this->find($id);
        $item->id = null;
        return $item;
    }

    public function find($id)
    {
        return OperationalCost::with(['financeAccount', 'category', 'creator', 'updater'])
            ->findOrFail($id);
    }

    public function findOrCreate($id = 0)
    {
        return $id ? OperationalCost::findOrFail($id) : new OperationalCost(['date' => date('Y-m-d')]);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $orderBy = $options['order_by'];
        $orderType = $options['order_type'];
        $filter = $options['filter'];

        $q = OperationalCost::with(['category', 'financeAccount']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('description', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['category_id']) && $filter['category_id'] !== 'all') {
            if ($filter['category_id'] === null) {
                $q->whereNull('category_id');
            } else if ($filter['category_id'] !== 'all') {
                $q->where('category_id', '=', $filter['category_id']);
            }
        }

        if (!empty($filter['finance_account_id']) && $filter['finance_account_id'] !== 'all') {
            $q->where('finance_account_id', '=', $filter['finance_account_id']);
        }

        // Tambahan filter tahun
        if (!empty($filter['year']) && $filter['year'] !== 'null') {
            $q->whereYear('date', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'null') {
                $q->whereMonth('date', $filter['month']);
            }
        }

        $q->orderBy($orderBy, $orderType);

        return  $q->paginate($options['per_page'])->withQueryString();
    }

    public function save(array $validated, $newImage)
    {
        try {
            DB::beginTransaction();

            // Inisialisasi variabel untuk rollback
            $oldItem = null;
            $newlyUploadedImagePath = null;

            $oldData = [];

            // 2. Tentukan item dan ambil data lama jika mode edit
            if (!empty($validated['id'])) {
                $item = $this->find($validated['id']);
                $oldItem = $item; // Ini gak boleh clone agar relationship gak hilang, gak boleh replicate karena bakalan ngebuang attribute yang dibutuhkan
            } else {
                $item = new OperationalCost();
            }

            // 3. PENANGANAN GAMBAR (IMAGE)
            $oldImagePath = $oldItem ? $oldItem->image_path : null;

            if ($newImage) {
                // Upload file baru dan hapus yang lama (helper yang tangani)
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $newImage,
                    'operational-costs',
                    $oldImagePath
                );
                $validated['image_path'] = $newlyUploadedImagePath;
            } elseif ($validated['image_path'] === null) {
                // User hapus gambar lama, hapus dari storage
                if ($oldImagePath) {
                    ImageUploaderHelper::deleteImage($oldImagePath);
                }
                $validated['image_path'] = null;
            } else {
                // Pertahankan gambar lama, tidak perlu ubah validated
            }

            unset($validated['image']);

            // 4. SIMPAN DATA OPERASIONAL
            $item->fill($validated);

            if ($oldData === $item->getAttributes()) {
                return false;
            }

            $item->save();

            // 5. PENANGANAN TRANSAKSI KEUANGAN
            // Logika pengembalian saldo hanya perlu dilakukan jika ada perubahan akun
            $this->financeTransactionService->handleTransaction(
                [
                    'ref_id'     => $item->id,
                    'ref_type'   => FinanceTransaction::RefType_OperationalCost,
                    'datetime'   => new Carbon($item->date),
                    'account_id' => $item->finance_account_id,
                    'amount'     => -abs($item->amount),
                    'type'       => FinanceTransaction::Type_Expense,
                    'notes'      => "Biaya operasional #$item->id",
                ],
                $oldItem ? [
                    'ref_id'     => $oldItem->id,
                    'ref_type'   => FinanceTransaction::RefType_OperationalCost,
                    'account_id' => $oldItem->finance_account_id,
                    'amount'     => -abs($oldItem->amount)
                ] : []
            );

            $this->documentVersionService->createVersion($item);

            // Log activity
            $item = $this->find($item->id); // refresh

            if (empty($validated['id'])) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCost,
                    UserActivityLog::Name_OperationalCost_Create,
                    "Biaya operasional ID: $item->id telah dibuat.",
                    [
                        'formatter' => 'operational-cost',
                        'new_data' => $this->generateActivityLogData($item),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCost,
                    UserActivityLog::Name_OperationalCost_Update,
                    "Biaya operasional ID: $item->id telah diperbarui.",
                    [
                        'formatter' => 'operational-cost',
                        'old_data' => $this->generateActivityLogData($oldItem),
                        'new_data' => $this->generateActivityLogData($item),
                    ]
                );
            }

            DB::commit();

            return $item;
        } catch (\Throwable $e) {
            DB::rollBack();

            // Hapus gambar baru jika ada error
            if ($newlyUploadedImagePath) {
                ImageUploaderHelper::deleteImage($newlyUploadedImagePath);
            }

            throw $e;
        }

        return false;
    }

    public function delete(OperationalCost $item)
    {
        try {
            DB::beginTransaction();

            // hapus item
            $item->delete();

            // hapus transaksi jika
            $this->financeTransactionService->reverseTransaction(
                $item->id,
                FinanceTransaction::RefType_OperationalCost
            );

            $this->documentVersionService->createDeletedVersion($item);

            // log aktivitas
            $this->userActivityLogService->log(
                UserActivityLog::Category_OperationalCost,
                UserActivityLog::Name_OperationalCost_Delete,
                "Biaya operasional ID: $item->id telah dihapus.",
                [
                    'formatter' => 'operational-cost',
                    'data' => $this->generateActivityLogData($item),
                ]
            );

            ImageUploaderHelper::deleteImage($item->image_path);

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();

            throw $ex;
        }
    }

    private function generateActivityLogData(OperationalCost $item)
    {
        $data = $item->getAttributes();
        $data['category_name']        = $item->category?->name;
        $data['created_by_username']  = $item->creator?->username;
        $data['updated_by_username']  = $item->updater?->username;
        $data['finance_account_name'] = $item->financeAccount?->name;
        return $data;
    }
}
