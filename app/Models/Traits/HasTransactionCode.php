<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait HasTransactionCode
{
    /**
     * Boot trait — akan otomatis dipanggil oleh Eloquent saat model di-boot.
     */
    protected static function bootHasTransactionCode(): void
    {
        static::creating(function (Model $model) {
            $field = $model->getCodeFieldName();
            if (empty($model->$field)) {
                $model->$field = $model->generateTransactionCode();
            }
        });
    }

    /**
     * Generate kode transaksi unik.
     *
     * Format default: PREFIX-YYMMDD-##### (contoh: SO-251015-00012)
     * Kamu bisa override getTransactionPrefix() di model untuk prefix custom.
     */
    public function generateTransactionCode(): string
    {
        // Gunakan transaksi agar atomic jika banyak insert paralel
        $nextNumber = DB::transaction(function () {
            $table = $this->getTable();
            $max = DB::table($table)
                ->select(DB::raw('MAX(id) as max_id'))
                ->lockForUpdate()
                ->first();

            return ($max->max_id ?? 0) + 1;
        });

        return $this->generateTransactionCodeWithDateAndSequence(now(), $nextNumber);
    }

    public function generateTransactionCodeWithDateAndSequence(\DateTime $date, $nextNumber): string
    {
        $prefix = $this->getTransactionPrefix();
        $datePart = $date->format('ymd');
        $sequence = str_pad($nextNumber, $this->getTransactionNumberPadSize(), '0', STR_PAD_LEFT);
        return "{$prefix}-{$datePart}-{$sequence}";
    }

    public function getCodeFieldName(): string
    {
        return property_exists($this, 'codeFieldName')
            ? $this->codeFieldName
            : 'code';
    }

    /**
     * Prefix default bisa di-override di setiap model.
     * Misalnya di SalesOrder → return 'SO';
     * di PurchaseOrder → return 'PO';
     */
    public function getTransactionPrefix(): string
    {
        return property_exists($this, 'transactionPrefix')
            ? $this->transactionPrefix
            : Str::upper(Str::substr(class_basename($this), 0, 2));
    }

    public function getTransactionNumberPadSize(): string
    {
        return property_exists($this, 'transactionNumberPadSize')
            ? $this->transactionNumberPadSize
            : 0;
    }

    /**
     * Accessor untuk kompatibilitas dengan formatted_id lama.
     */
    public function getFormattedIdAttribute(): string
    {
        return $this->code ?? $this->generateLegacyFormattedId();
    }

    /**
     * Legacy generator (opsional, fallback untuk data lama).
     */
    protected function generateLegacyFormattedId(): string
    {
        $prefix = $this->getTransactionPrefix();
        $idPart = str_pad($this->id ?? 0, $this->getTransactionNumberPadSize(), '0', STR_PAD_LEFT);
        $datePart = optional($this->created_at)->format('ymd') ?? now()->format('ymd');

        return "{$prefix}-{$datePart}-{$idPart}";
    }
}
