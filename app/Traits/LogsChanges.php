<?php

namespace App\Traits;

trait LogsChanges
{
    /**
     * Dapatkan label yang dapat dibaca manusia dari nama field database.
     * Trait ini mengasumsikan model yang menggunakannya memiliki konstanta
     * publik bernama 'FIELD_LABELS' (misalnya public const FIELD_LABELS = [...]).
     *
     * @param string $field Nama field database.
     * @param string|null $default Nilai default jika label tidak ditemukan.
     * @return string Label yang sudah dikonversi atau nama field itu sendiri.
     */
    protected function getFieldLabel(string $field, string $default = null): string
    {
        // Pastikan konstanta Field_Labels tersedia di model yang menggunakan trait ini
        $labels = defined('static::Field_Labels') ? static::Field_Labels : [];

        // Menggunakan Null Coalescing Operator untuk fallback yang bersih
        return $labels[$field] ?? $default ?? $field;
    }

    /**
     * Membuat array metadata yang rapi dari atribut yang berubah,
     * dengan kunci (keys) berupa label yang mudah dibaca.
     *
     * @return array Metadata log dengan kunci label dan nilai [old, new].
     */
    public function getFormattedDirtyMetadata(): array
    {
        // Ambil atribut yang berubah dan pastikan tidak kosong
        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return [];
        }

        $metadata = [];
        $original = $this->getOriginal();

        // Iterasi field yang berubah dan ubah kuncinya menjadi label
        foreach ($dirty as $field => $newValue) {
            // Dapatkan label untuk field ini
            $label = $this->getFieldLabel($field);

            // Jika field tidak ada di $original (misal, ini field baru),
            // set nilai lamanya ke null atau string kosong
            $oldValue = $original[$field] ?? null;

            $metadata[$label] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $metadata;
    }
}
