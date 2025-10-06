<?php

namespace Modules\Admin\Features\UserActivityLog\Formatters\Concrete;

class DefaultFormatter extends BaseFormatter
{
    protected $mapping = [];

    public function format(array $metaData): array
    {
        $mapping = $this->mapping;

        $output = [];

        if (isset($metaData['new_data']) && isset($metaData['old_data'])) {
            $output[] = [
                'label' => 'Perubahan Data',
                'value' => $this->_processChanges($metaData['old_data'], $metaData['new_data'], $mapping),
                'type' => 'comparison'
            ];
        } else if (isset($metaData['data'])) {
            foreach ($metaData['data'] as $key => $value) {
                $output[] = [
                    'label' => $key,
                    'value' => $this->formatValue($key, $value, $metaData['data']),
                    'type' => 'simple'
                ];
            }
        }

        return $output;
    }

    private function _processChanges(array $old, array $new, array $mapping): array
    {
        $changes = [];
        foreach ($new as $key => $newValue) {
            $oldValue = $old[$key] ?? null;

            if ($oldValue === $newValue) continue;

            $changes[] = [
                'field' => $key,
                'old_value' => $this->formatValue($key, $oldValue, $old),
                'new_value' => $this->formatValue($key, $newValue, $new),
            ];
        }

        return $changes;
    }
}
