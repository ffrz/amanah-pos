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
    protected $primary_columns = [];

    protected $optional_columns = [];

    protected $all_columns = [];

    public function __construct()
    {
        $this->all_columns = array_merge($this->primary_columns, $this->optional_columns);
    }

    protected function getDefaultValidationRules()
    {
        return [
            'columns' => 'required|array|min:1|in:' . implode(',', array_keys($this->all_columns)),
            'sortOptions' => 'nullable|array',
            'sortOptions.*.column' => 'required_with:sortOptions|string|in:' . implode(',', array_keys($this->all_columns)),
            'sortOptions.*.order' => 'required_with:sortOptions|string|in:asc,desc',
            'orientation' => 'required|string|in:auto,portrait,landscape',
        ];
    }

    protected function generatePdfReport($view, $data)
    {
        $format = request('format', 'pdf');
        $data['orientation'] = $this->getPageOrientation();
        $data['pdf'] = $format == 'pdf';
        $data['all_columns'] = $this->all_columns;
        $filename = env('APP_NAME') . ' - ' . $data['title'];

        if (isset($data['filter']['start_date']) || isset($data['filter']['end_date'])) {
            $data['subtitles'][] = 'Periode: ' . format_date($data['filter']['start_date']) . ' s/d ' . format_date($data['filter']['end_date']);
        }

        // dd($data);

        if ($format == 'pdf') {
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

    protected function processQuery(\Illuminate\Database\Eloquent\Builder $q, $columns)
    {
        $sortOptions = request('sortOptions');

        foreach ($sortOptions as $option) {
            $q->orderBy($option['column'], $option['order']);
        }

        return $q->select($columns)->get();
    }
}
