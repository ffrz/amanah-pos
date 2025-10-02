<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

use Modules\Admin\Features\UserActivityLog\Contracts\MetaDataFormatterInterface;
use Carbon\Carbon;

class BaseFormatter implements MetaDataFormatterInterface
{
    protected $mapping = [];

    public function format(array $metaData): array
    {
        $mapping = $this->mapping;
        $output = [];

        // Ambil semua kunci yang akan diproses dari mapping, ini akan menjadi urutan master
        $mappedKeys = array_keys($mapping);

        if (isset($metaData['new_data']) && isset($metaData['old_data'])) {
            $output[] = [
                'label' => 'Perubahan Data',
                'value' => $this->processChanges($metaData['old_data'], $metaData['new_data'], $mapping, $mappedKeys),
                'type' => 'comparison'
            ];
        } else if (isset($metaData['data']) || isset($metaData['new_data'])) {
            $data = isset($metaData['data']) ? $metaData['data'] : $metaData['new_data'];

            // Perulangan menggunakan $mappedKeys untuk menjamin urutan sesuai $mapping
            foreach ($mappedKeys as $key) {
                // Pastikan kunci (field) ada di data yang sedang diproses
                if (!isset($data[$key])) continue;

                $value = $data[$key];

                // Pengecekan mapping sudah tidak diperlukan lagi karena kita loop berdasarkan $mapping

                $output[] = [
                    'label' => $mapping[$key],
                    'value' => $this->formatValue($key, $value),
                    'type' => 'simple'
                ];
            }
        }

        return $output;
    }

    protected function formatValue(string $key, $value)
    {
        if (in_array($key, ['created_at', 'updated_at']) && $value) {
            return Carbon::parse($value)->format('d/m/Y H:i:s');
        }

        // Jika nilainya adalah array atau objek (seperti array roles), kita bisa 
        // mengonversinya menjadi string JSON agar tetap ditampilkan.
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return $value ?? '-';
    }


    protected function processChanges(array $old, array $new, array $mapping, array $mappedKeys): array
    {
        $changes = [];

        // Perulangan menggunakan $mappedKeys untuk menjamin urutan sesuai $mapping
        foreach ($mappedKeys as $key) {

            // Pastikan kunci (field) ada di data baru (new)
            if (!isset($new[$key])) continue;

            $oldValue = $old[$key] ?? null;
            $newValue = $new[$key];

            // if ($oldValue === $newValue) continue; // Baris ini sengaja dikomentari, tidak diubah

            $changes[] = [
                'field' => $mapping[$key],
                'old_value' => $this->formatValue($key, $oldValue),
                'new_value' => $this->formatValue($key, $newValue),
            ];
        }

        return $changes;
    }
}
