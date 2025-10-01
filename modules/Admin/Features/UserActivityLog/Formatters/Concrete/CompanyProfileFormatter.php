<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

use Modules\Admin\Features\UserActivityLog\Contracts\MetaDataFormatterInterface;
use Carbon\Carbon;

class CompanyProfileFormatter implements MetaDataFormatterInterface
{
    public function format(array $metaData): array
    {
        // 1. Definisikan pemetaan (Mapping) khusus untuk model ini.
        $mapping = [
            'id' => 'ID Kategori',
            'name' => 'Nama Kategori',
            'description' => 'Deskripsi',
            'created_at' => 'Waktu Dibuat',
            'created_by' => 'ID Pembuat',
            'is_active' => 'Status Aktif',
        ];

        $output = [];

        // 2. Logika untuk log UPDATE (Kompleks)
        if (isset($metaData['new_data']) && isset($metaData['old_data'])) {
            // Logika pemrosesan perubahan yang detail (membandingkan old vs new)
            // Hasilnya dikembalikan dalam format yang mudah di-render client
            $output[] = [
                'label' => 'Perubahan Data',
                'value' => $this->processChanges($metaData['old_data'], $metaData['new_data'], $mapping),
                'type' => 'comparison'
            ];
        } else {
            // Logika untuk log CREATE/DELETE (Sederhana)
            foreach ($metaData as $key => $value) {
                if (!isset($mapping[$key])) continue; // Lewati field yang tidak ingin ditampilkan

                $output[] = [
                    'label' => $mapping[$key],
                    'value' => $this->formatValue($key, $value),
                    'type' => 'simple'
                ];
            }
        }

        return $output;
    }

    // Metode pemformatan nilai (Tanggal, Boolean, Null)
    protected function formatValue(string $key, $value)
    {
        if (in_array($key, ['created_at', 'updated_at']) && $value) {
            return Carbon::parse($value)->format('d/m/Y H:i:s');
        }
        if ($key === 'is_active') {
            return $value ? 'Ya' : 'Tidak';
        }
        return $value ?? '-';
    }

    // Metode pemrosesan perubahan (disederhanakan)
    protected function processChanges(array $old, array $new, array $mapping): array
    {
        $changes = [];
        foreach ($new as $key => $newValue) {
            if (!isset($mapping[$key]) || ($old[$key] ?? null) === $newValue) continue;

            $changes[] = [
                'field' => $mapping[$key],
                'old_value' => $this->formatValue($key, $old[$key] ?? null),
                'new_value' => $this->formatValue($key, $newValue),
            ];
        }
        return $changes;
    }
}
