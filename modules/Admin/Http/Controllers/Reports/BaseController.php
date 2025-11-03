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

namespace Modules\Admin\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class BaseController extends Controller
{
    protected string $default_title = '';

    protected array $templates = [];

    protected $primary_columns = [];

    protected $optional_columns = [];

    protected $all_columns = [];

    protected $initial_columns = [];

    public function __construct()
    {
        $this->all_columns = array_merge($this->primary_columns, $this->optional_columns);
    }

    public function generateIndexResponse($view, $data = [])
    {
        return inertia($view, [
            'primary_columns' => $this->primary_columns,
            'optional_columns' => $this->optional_columns,
            'initial_columns' => $this->initial_columns,
            'templates' => array_values($this->templates),
            ...$data
        ]);
    }

    protected function getDefaultValidationRules()
    {
        return [
            'template' => 'nullable|string:in:' . join(',', array_keys($this->templates)),
            'columns' => 'required|array|min:1|in:' . implode(',', array_keys($this->all_columns)),
            'sortOptions' => 'nullable|array',
            'sortOptions.*.column' => 'required_with:sortOptions|string|in:' . implode(',', array_keys($this->all_columns)),
            'sortOptions.*.order' => 'required_with:sortOptions|string|in:asc,desc',
            'orientation' => 'required|string|in:auto,portrait,landscape',
            'filter' => 'nullable|array',
        ];
    }

    protected function generateReport($view, $data, $q)
    {
        $data['title'] = !empty($data['template']) ? $this->templates[$data['template']]['label'] : $this->default_title;
        $data['items'] = $this->processQuery($q, $data['query_columns'] ?? $data['columns'], $data);
        return $this->_generateReport(
            $view,
            $data
        );
    }

    private function _generateReport($view, $data)
    {
        $format = request('format', 'pdf');
        $data['orientation'] = $this->getPageOrientation();
        $data['pdf'] = $format == 'pdf';
        $data['all_columns'] = $this->all_columns;

        if (isset($data['filter']['start_date']) || isset($data['filter']['end_date'])) {
            $data['subtitles'][] = 'Periode: ' . format_date($data['filter']['start_date']) . ' s/d ' . format_date($data['filter']['end_date']);
        }

        if ($format == 'pdf') {
            $filename = env('APP_NAME') . ' - ' . $data['title'];
            $invalidChars = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];
            $filename = str_replace($invalidChars, '_', $filename);
            $filename = trim(preg_replace('/\s+/', ' ', $filename));

            return Pdf::loadView($view, $data)
                ->setPaper('a4', $data['orientation'])
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true)
                ->download($filename . '.pdf');
        }

        if ($format == 'html') {
            return view($view, $data);
        }

        throw new \Exception('Unknown output format!');
    }

    protected function getPageOrientation(): string
    {
        $orientation = request('orientation', '');
        if ($orientation == 'auto') {
            $orientation = 'portrait';
            if (count(request('columns')) > 6) {
                $orientation = 'landscape';
            }
        }
        return $orientation;
    }

    protected function processQuery(\Illuminate\Database\Eloquent\Builder $q, $columns, $data)
    {
        $sortOptions = request('sortOptions');

        foreach ($sortOptions as $option) {
            $q->orderBy($option['column'], $option['order']);
        }

        return $q->select($columns)->get();
    }
}
