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

namespace App\Models\Traits;

use App\Models\DocumentVersion;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasDocumentVersions
{
    /**
     * Mendefinisikan relasi ke semua versi dokumen.
     */
    public function versions(): MorphMany
    {
        return $this->morphMany(DocumentVersion::class, 'document')->orderByDesc('version');
    }
}
