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

        if (isset($metaData['new_data']) && isset($metaData['old_data'])) {
            $output[] = [
                'label' => 'Perubahan Data',
                'value' => $this->processChanges($metaData['old_data'], $metaData['new_data'], $mapping),
                'type' => 'comparison'
            ];
        } else if (isset($metaData['data'])) {
            foreach ($metaData['data'] as $key => $value) {
                if (!isset($mapping[$key])) continue;

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

        return $value ?? '-';
    }


    protected function processChanges(array $old, array $new, array $mapping): array
    {
        $changes = [];
        foreach ($new as $key => $newValue) {
            if (!isset($mapping[$key])) continue;

            $oldValue = $old[$key] ?? null;

            if ($oldValue === $newValue) continue;

            $changes[] = [
                'field' => $mapping[$key],
                'old_value' => $this->formatValue($key, $oldValue),
                'new_value' => $this->formatValue($key, $newValue),
            ];
        }

        return $changes;
    }
}
