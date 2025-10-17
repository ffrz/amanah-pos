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

class TaxScheme extends BaseModel
{
    use HasFactory,
        SoftDeletes,
        HasDocumentVersions;

    protected $fillable = [
        'name',
        'rate_percentage',
        'is_inclusive',
        'tax_authority',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'rate_percentage' => 'decimal:2',
            'is_inclusive' => 'boolean',
            'tax_authority' => 'string',
            'description' => 'string',
            'active' => 'boolean',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'deleted_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
