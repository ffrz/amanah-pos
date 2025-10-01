<?php

namespace Modules\Admin\Services;

use App\Models\User;
use App\Models\DocumentVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DocumentVersionService
{
    /**
     * Membuat versi baru (snapshot) untuk dokumen yang diberikan.
     *
     * @param Model $document Model Eloquent yang akan di-versi
     * @param string $changelog Deskripsi singkat perubahan
     * @param User|null $user Pengguna yang membuat perubahan (default: pengguna yang sedang login)
     * @return DocumentVersion
     */
    public function createVersion(Model $document, string $changelog = null, ?User $user = null): DocumentVersion
    {
        // Tentukan pengguna yang bertanggung jawab
        $responsibleUser = $user ?? (Auth::check() ? Auth::user() : null);

        // Dapatkan nomor versi berikutnya
        $latestVersion = $document->versions()->max('version') ?? 0;
        $nextVersion = $latestVersion + 1;

        // Persiapan Data Snapshot
        // Ambil semua atribut dokumen, kecuali timestamps dan kolom yang tidak relevan
        $snapshotData = $document->getAttributes();

        // Hapus kolom yang tidak perlu disimpan dalam versi (misal: updated_at, created_at)
        unset($snapshotData['updated_at']);
        // Biarkan created_at jika itu adalah bagian dari data yang di-versi

        // Simpan Versi Baru
        $version = new DocumentVersion([
            // Relasi Polimorfik
            'document_type' => $document->getMorphClass(),
            'document_id' => $document->getKey(),
            'version' => $nextVersion,

            // Data & Audit
            'data' => $snapshotData,
            'changelog' => $changelog,
            'created_by' => $responsibleUser?->id, // Asumsi kolom created_by
            // created_at akan terisi otomatis oleh macro createdTimestamps()
        ]);

        $version->save();

        return $version;
    }

    /**
     * Memulihkan dokumen ke versi tertentu.
     * Catatan: Metode ini HANYA menerapkan data versi ke dokumen utama,
     * belum menyimpan perubahan ke database (ini memberi kesempatan untuk review).
     *
     * @param Model $document Model Eloquent utama
     * @param int $versionNumber Nomor versi yang akan dipulihkan
     * @return Model|null Dokumen dengan atribut versi yang dipulihkan, atau null jika versi tidak ditemukan.
     */
    public function restoreVersion(Model $document, int $versionNumber): ?Model
    {
        // Cari versi berdasarkan document_id, document_type, dan nomor versi
        $versionRecord = DocumentVersion::query()
            ->where('document_type', $document->getMorphClass())
            ->where('document_id', $document->getKey())
            ->where('version', $versionNumber)
            ->first();

        if (!$versionRecord) {
            return null;
        }

        // Terapkan data snapshot dari versi ke dokumen utama
        $document->fill($versionRecord->data);
        $document->save();

        return $document;
    }
}
