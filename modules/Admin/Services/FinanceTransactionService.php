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

use App\Exceptions\BusinessRuleViolationException;
use App\Helpers\ImageUploaderHelper;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FinanceTransactionService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
    ) {}

    public function find(int $id): FinanceTransaction
    {
        return FinanceTransaction::with(['account', 'category', 'creator', 'updater'])
            ->findOrFail($id);
    }

    public function findOrCreate($id): FinanceTransaction
    {
        return $id ? $this->find($id) : new FinanceTransaction(['datetime' => Carbon::now()]);
    }

    public function addToBalance(FinanceAccount $account, $amount): void
    {
        $lockedAccount = FinanceAccount::where('id', $account->id)->lockForUpdate()->firstOrFail();
        $lockedAccount->balance += $amount;
        $lockedAccount->save();
    }

    public function handleTransaction(array $newData, array $oldData = [])
    {
        // Cek apakah ada data lama dan akun keuangan berubah
        if (isset($oldData['account_id'])) {
            // Kembalikan saldo akun lama
            $oldAccount = FinanceAccount::findOrFail($oldData['account_id']);
            $this->addToBalance($oldAccount, -$oldData['amount']);
        }

        // Jika tidak ada akun finansial baru, hapus transaksi lama dan berhenti
        if (empty($newData['account_id'])) {
            if (isset($oldData['ref_id'])) {
                FinanceTransaction::where('ref_id', $oldData['ref_id'])
                    ->where('ref_type', $oldData['ref_type'])
                    ->delete();
            }
            return;
        }

        // Perbarui saldo akun baru
        $account = FinanceAccount::findOrFail($newData['account_id']);
        $this->addToBalance($account, $newData['amount']);

        // Buat transaksi baru atau perbarui transaksi
        return FinanceTransaction::updateOrCreate(
            [
                'ref_id' => $newData['ref_id'],
                'ref_type' => $newData['ref_type'],
            ],
            [
                'account_id' => $newData['account_id'],
                'category_id' => $newData['category_id'] ?? null,
                'datetime' => $newData['datetime'],
                'amount' => $newData['amount'],
                'type' => $newData['type'],
                'notes' => $newData['notes'],
                'image_path' => $newData['image_path'] ?? null,
            ]
        );
    }

    public function reverseTransaction($ref_id, $ref_type)
    {
        $trx = FinanceTransaction::where('ref_id', $ref_id)
            ->where('ref_type', $ref_type)
            ->first();

        if ($trx) {
            $account = $trx->account;
            $this->addToBalance($account, -1 * $trx->amount);
            $trx->delete();
        }

        return $trx;
    }

    public function getData(array $options)
    {
        $filter = $options['filter'];
        $hasDateFilter = !empty($filter['start_date']) || !empty($filter['end_date']) || !empty($filter['from_datetime']);
        $perPage = $options['per_page'] ?? 15;

        $q = FinanceTransaction::with([
            'account' => function ($query) {
                $query->select('id', 'name', 'bank', 'number', 'holder');
            },
            'category' => function ($query) {
                $query->select('id', 'name');
            },
            'tags:id,name',
        ])
            ->select(
                'id',
                'code',
                'datetime',
                'type',
                'amount',
                'notes',
                'image_path',
                'account_id',
                'category_id',
            )
            ->whereNull('deleted_at');

        // --- LOGIKA FILTER (TIDAK DIUBAH) ---
        if (!empty($filter['type']) && $filter['type'] !== 'all') {
            $q->where('type', $filter['type']);
        }

        // Jika filter akun hanya 1, kita bisa langsung menggunakannya untuk saldo awal
        $singleAccountId = null;
        if (!empty($filter['account_id']) && $filter['account_id'] !== 'all') {
            $singleAccountId = $filter['account_id'];
            $q->where('account_id', $singleAccountId);
        }

        if (!empty($filter['category_id']) && $filter['category_id'] !== 'all') {
            if ($filter['category_id'] === 'none') {
                $q->whereNull('category_id');
            } else {
                $q->where('category_id', $filter['category_id']);
            }
        }

        if (!empty($filter['tags']) && is_array($filter['tags'])) {
            $tagIds = array_map('intval', $filter['tags']);

            $q->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('finance_transaction_tags.id', $tagIds);
            });
        }

        if (!empty($filter['user_id']) && $filter['user_id'] !== 'all') {
            $q->where('created_by', $filter['user_id']);
        }

        $startDate = $filter['start_date'] ?? null;
        $endDate = $filter['end_date'] ?? null;

        if ($startDate) {
            $q->where('datetime', '>=', $startDate);
        }
        if ($endDate) {
            $q->where('datetime', '<=', $endDate);
        }

        if (!empty($filter['from_datetime'])) {
            $start = Carbon::parse($filter['from_datetime']);
            $end = empty($filter['to_datetime']) ? Carbon::now() : Carbon::parse($filter['to_datetime']);
            $q->whereBetween('created_at', [$start, $end]);
        }

        if (!$hasDateFilter) {
            $defaultStartDate = Carbon::now()->subDays(90)->startOfDay();
            $q->where('datetime', '>=', $defaultStartDate);
        }

        if (!empty($filter['search'])) {
            $q->where(function (Builder $q) use ($filter) {
                $q->orWhere('code', 'like', "%" . $filter['search'] . "%");
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        // --- MODIFIKASI UNTUK SALDO BERJALAN ---

        // 1. Ubah urutan menjadi ASC untuk perhitungan saldo
        // cursorPaginate akan secara otomatis menyortir berdasarkan kolom cursor (biasanya 'id' dan kolom orderBy lainnya)
        $q->orderBy('datetime', 'asc');

        // 2. Ambil data dengan pagination
        $paginatedTransactions = $q->cursorPaginate($perPage)->withQueryString();
        $items = $paginatedTransactions->items();

        if (empty($items)) {
            return $paginatedTransactions;
        }

        // Tentukan tanggal sebelum transaksi pertama di halaman ini
        $firstTransaction = head($items);
        $earliestDate = Carbon::parse($firstTransaction->datetime)->subSecond();

        // Kumpulkan semua account_id yang ada di halaman hasil
        $accountIds = $singleAccountId ? [$singleAccountId] : collect($items)->pluck('account_id')->unique()->toArray();

        // 3. Ambil Saldo Awal (Opening Balance) per Akun 
        // Kita harus menghitung total amount dari semua transaksi sebelum earliestDate.

        $openingBalances = FinanceTransaction::query()
            ->whereIn('account_id', $accountIds)
            ->where('datetime', '<=', $earliestDate)
            ->whereNull('deleted_at')
            ->groupBy('account_id')
            ->select('account_id', DB::raw('SUM(amount) as opening_balance'))
            ->pluck('opening_balance', 'account_id')
            ->toArray();

        // 4. Iterasi dan Hitung Saldo Berjalan (Running Balance)
        $currentBalances = [];
        foreach ($accountIds as $accountId) {
            // Inisialisasi saldo berjalan dengan saldo awal (0 jika tidak ada transaksi sebelumnya)
            $currentBalances[$accountId] = $openingBalances[$accountId] ?? 0;
        }

        foreach ($items as $transaction) {
            // Ambil saldo berjalan saat ini untuk akun transaksi ini
            $balance = $currentBalances[$transaction->account_id];

            // Tambahkan (atau kurangi) jumlah transaksi ke saldo
            $balance += $transaction->amount;

            // Simpan saldo sebagai kolom 'balance' pada objek transaksi
            $transaction->balance = $balance;

            // Perbarui saldo berjalan untuk perhitungan transaksi berikutnya
            $currentBalances[$transaction->account_id] = $balance;
        }

        // 5. Kembalikan Urutan ke DESC untuk tampilan daftar (terbaru di atas)
        // Karena kita tidak memuat ulang koleksi, kita hanya perlu membalik urutan item
        $reversedItems = collect($items)->reverse()->values();

        $paginatedTransactions->setCollection($reversedItems);
        return $paginatedTransactions;
    }

    public function delete(FinanceTransaction $item): FinanceTransaction
    {
        if ($item->ref_type && $item->ref_type != FinanceTransaction::RefType_FinanceTransaction) {
            throw new BusinessRuleViolationException("
                Transaksi #$item->id tidak dapat dihapus karena berkaitan dengan transaksi lainnya.
                Ref type: $item->ref_type. Ref ID: $item->ref_id", 403);
        }

        return DB::transaction(function () use ($item) {
            if ($item->type === FinanceTransaction::Type_Transfer && $item->ref_type === FinanceTransaction::RefType_FinanceTransaction && $item->ref_id) {
                $pair = FinanceTransaction::find($item->ref_id);
                if ($pair) {
                    $this->deleteTransaction($pair);

                    $this->documentVersionService->createDeletedVersion($item);

                    $this->userActivityLogService->log(
                        UserActivityLog::Category_FinanceTransaction,
                        UserActivityLog::Name_FinanceTransaction_Delete,
                        "Transaksi $pair->code telah dihapus",
                        [
                            "data" => $pair->toArray(),
                            "formatter" => "finance-transaction",
                        ]
                    );
                }
            }

            $this->deleteTransaction($item);

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_FinanceTransaction,
                UserActivityLog::Name_FinanceTransaction_Delete,
                "Transaksi $item->code telah dihapus",
                [
                    "data" => $item->toArray(),
                    "formatter" => "finance-transaction",
                ]
            );

            return $item;
        });
    }

    private function deleteTransaction($trx)
    {
        $this->addToBalance($trx->account, -$trx->amount);
        $trx->delete();
        ImageUploaderHelper::deleteImage($trx->image_path);
    }

    public function save(array $validated, $imageFile)
    {
        $item = null;
        $newlyUploadedImagePath = null;

        try {
            DB::beginTransaction();

            if ($imageFile) {
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $imageFile,
                    'finance-transactions'
                );
                $validated['image_path'] = $newlyUploadedImagePath;
                unset($validated['image']);
            }

            if ($validated['type'] === FinanceTransaction::Type_Transfer && !empty($validated['to_account_id'])) {
                $items = $this->createTransferTransactions($validated); // pair

                foreach ($items as $item) {
                    $this->documentVersionService->createVersion($item);

                    $code = $item->code;

                    $this->userActivityLogService->log(
                        UserActivityLog::Category_FinanceTransaction,
                        UserActivityLog::Name_FinanceTransaction_Create,
                        "Transaksi $code telah dibuat",
                        [
                            "data" => $item->toArray(),
                            "formatter" => "finance-transaction",
                        ]
                    );
                }
            } else {
                $item = $this->createSingleTransaction($validated);

                $this->documentVersionService->createVersion($item);

                $this->userActivityLogService->log(
                    UserActivityLog::Category_FinanceTransaction,
                    UserActivityLog::Name_FinanceTransaction_Create,
                    "Transaksi $item->code telah dibuat",
                    [
                        "data" => $item->toArray(),
                        "formatter" => "finance-transaction",
                    ]
                );
            }

            DB::commit();

            return $item;
        } catch (Exception $ex) {
            DB::rollBack();

            if ($newlyUploadedImagePath) {
                ImageUploaderHelper::deleteImage($newlyUploadedImagePath);
            }

            throw $ex;
        }
    }

    private function createSingleTransaction(array $validated): FinanceTransaction
    {
        // Handle transaksi biasa (income / expense)
        $amount = $validated['amount'];
        if ($validated['type'] === FinanceTransaction::Type_Expense) {
            $amount = -$amount;
        }

        $transaction = new FinanceTransaction();
        $transaction->fill([
            'account_id'  => $validated['account_id'],
            'category_id' => $validated['category_id'] ?? null,
            'datetime'    => $validated['datetime'],
            'type'        => $validated['type'],
            'amount'      => $amount,
            'notes'       => $validated['notes'],
            'image_path'  => $validated['image_path'],
        ]);
        $transaction->save();

        $this->addToBalance($transaction->account, $transaction->amount);

        if (!empty($validated['tags'])) {
            $this->syncTags($transaction, $validated['tags']);
        }

        return $transaction;
    }

    private function createTransferTransactions(array $validated): array
    {
        $fromTransaction = new FinanceTransaction();
        $fromTransaction->fill([
            'account_id'  => $validated['account_id'],
            'category_id' => $validated['category_id'] ?? null,
            'datetime'    => $validated['datetime'],
            'type'        => FinanceTransaction::Type_Transfer,
            'amount'      => -$validated['amount'],
            'notes'       => 'Transfer ke akun #' . $validated['to_account_id'] . '. ' . $validated['notes'],
            'image_path'  => $validated['image_path'],
        ]);
        $fromTransaction->save();

        // Update saldo akun asal
        $this->addToBalance($fromTransaction->account, $fromTransaction->amount);

        // Transaksi masuk ke akun tujuan
        $toTransaction = new FinanceTransaction();
        $toTransaction->fill([
            'account_id'  => $validated['to_account_id'],
            'category_id' => $validated['category_id'] ?? null,
            'datetime'    => $validated['datetime'],
            'type'        => FinanceTransaction::Type_Transfer,
            'amount'      => $validated['amount'], // masuk
            'notes'       => 'Transfer dari akun #' . $validated['account_id'] . '. ' . $validated['notes'],
            'image_path'  => $validated['image_path'],
        ]);
        $toTransaction->save();

        // Update saldo akun tujuan
        $this->addToBalance($toTransaction->account, $toTransaction->amount);

        // Link untuk keperluan delete
        $fromTransaction->ref_type = FinanceTransaction::RefType_FinanceTransaction;
        $fromTransaction->ref_id = $toTransaction->id;
        $fromTransaction->save();

        $toTransaction->ref_type = FinanceTransaction::RefType_FinanceTransaction;
        $toTransaction->ref_id = $fromTransaction->id;
        $toTransaction->save();

        // tag

        if (!empty($validated['tags'])) {
            $this->syncTags($fromTransaction, $validated['tags']);
            $this->syncTags($toTransaction, $validated['tags']);
        }

        return [$fromTransaction, $toTransaction];
    }

    private function syncTags(FinanceTransaction $trx, array $tags): void
    {
        // Ambil hanya integer (jaga-jaga)
        $cleanTags = collect($tags)
            ->map(fn($v) => intval($v))
            ->filter(fn($v) => $v > 0)
            ->values()
            ->all();

        $trx->tags()->sync($cleanTags);
    }
}
