<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

use Modules\Admin\Features\UserActivityLog\Contracts\MetaDataFormatterInterface;
use Carbon\Carbon;

class BaseFormatter implements MetaDataFormatterInterface
{
    protected $mapping = [];
    protected $data = [];

    public function format(array $metaData): array
    {
        $this->data = $metaData;
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
                $value = null;
                if (!isset($data[$key])) {
                    $data[$key] = null;
                }

                $value = $data[$key];

                // Pengecekan mapping sudah tidak diperlukan lagi karena kita loop berdasarkan $mapping

                $output[] = [
                    'label' => $mapping[$key],
                    'value' => $this->formatValue($key, $value, $data),
                    'type' => 'simple'
                ];
            }
        }

        return $output;
    }

    protected function formatValue(string $key, $value, $data)
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
            if (!isset($old[$key]) && !isset($new[$key])) {
                $data[$key] = null;
            }

            $oldValue = $old[$key] ?? null;
            $newValue = $new[$key] ?? null;

            $changes[] = [
                'field' => $mapping[$key],
                'old_value' => $this->formatValue($key, $oldValue, $old),
                'new_value' => $this->formatValue($key, $newValue, $new),
            ];
        }

        return $changes;
    }
}
