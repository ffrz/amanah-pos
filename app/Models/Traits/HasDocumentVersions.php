<?php

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
