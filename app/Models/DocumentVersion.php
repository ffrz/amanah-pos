<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $document_type
 * @property int $document_id
 * @property int $version
 * @property array $data
 * @property int|null $created_by
 * @property string|null $changelog
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class DocumentVersion extends BaseModel
{
    use HasFactory;

    // Nama tabel sudah sesuai dengan konvensi Laravel: document_versions

    protected $fillable = [
        'document_type',
        'document_id',
        'version',
        'data',
        'created_by',
        'is_deleted',
        'changelog',
    ];

    protected $casts = [
        'document_type' => 'string',
        'document_id' => 'integer',
        'version' => 'integer',
        'data' => 'array',
        'created_at' => 'datetime',
        'is_deleted' => 'boolean',
        'changelog' => 'string',
    ];

    /**
     * Mendefinisikan relasi polimorfik ke dokumen utama.
     */
    public function document(): MorphTo
    {
        return $this->morphTo();
    }
}
