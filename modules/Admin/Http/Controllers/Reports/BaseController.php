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
use FFI\Exception;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request as HttpRequest;

class BaseController extends Controller
{
    protected function generatePdfReport($view, $orientation, $data, $response = 'pdf')
    {
        $filename = env('APP_NAME') . ' - ' . $data['title'];

        if (isset($data['start_date']) || isset($data['end_date'])) {
            if (empty($data['subtitles'])) {
                $data['subtitles'] = [];
            }
            $data['subtitles'][] = 'Periode: ' . format_date($data['start_date']) . ' s/d ' . format_date($data['end_date']);
        }

        if ($response == 'pdf') {
            return Pdf::loadView($view, $data)
                ->setPaper('a4', $orientation)
                ->download($filename . '.pdf');
        }

        if ($response == 'html') {
            return view($view, $data);
        }

        throw new Exception('Unknown response type!');
    }

    protected function getPageOrientation(HttpRequest $request): string
    {
        $orientation = $request->get('orientation');
        if ($orientation == 'auto') {
            $orientation = 'portrait';
            if (count($request->get('columns')) > 6) {
                $orientation = 'landscape';
            }
        }
        return $orientation;
    }
}
