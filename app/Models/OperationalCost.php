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

namespace App\Models;

use App\Models\Traits\HasDocumentVersions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationalCost extends BaseModel
{
    use HasFactory,
        HasDocumentVersions,
        SoftDeletes;

    protected $fillable = [
        'category_id',
        'finance_account_id',
        'date',
        'description',
        'amount',
        'image_path',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'finance_account_id' => 'integer',
            'date' => 'string',
            'description' => 'string',
            'amount' => 'decimal:2',
            'notes' => 'string',
            'image_path' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'string',
            'updated_at' => 'string',
        ];
    }

    public function category()
    {
        return $this->belongsTo(OperationalCostCategory::class);
    }

    public function financeAccount()
    {
        return $this->belongsTo(FinanceAccount::class);
    }
}
