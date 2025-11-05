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

namespace App\Helpers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request; // Menggunakan Facade Request
use Illuminate\View\ViewException;
use Inertia\Response as InertiaResponse;

/**
 * Class AutoResponseHelper
 *
 * Menyediakan metode statis untuk secara otomatis menentukan tipe respons
 * (Inertia, JSON, atau Blade) berdasarkan header request yang masuk.
 *
 * Ini membantu menjaga controller tetap DRY (Don't Repeat Yourself) saat menangani
 * endpoint yang bisa diakses oleh Inertia (kunjungan halaman penuh),
 * AJAX (misalnya, dialog 'quick create'), dan (opsional) Blade fallback.
 *
 * @package App\Helpers
 */
class AutoResponseHelper
{
    /**
     * Merender respons hybrid (Inertia, atau JSON) secara otomatis.
     *
     * Metode ini memeriksa header request untuk menentukan format respons:
     * 1. Jika header 'X-Inertia' ada, kembalikan respons Inertia.
     * 2. Jika header 'Accept: application/json' ada (wantsJson), kembalikan respons JSON.
     *
     * @param string $inertiaComponent Nama komponen Inertia.js yang akan dirender (misal: 'customer/Editor').
     * @param array $data Data (props) yang akan dikirimkan ke komponen, view, atau respons JSON.
     *
     * @return InertiaResponse|JsonResponse|View
     *
     * @throws ViewException Jika request adalah web standar (non-Inertia/non-JSON)
     *                       namun $bladeView tidak disediakan (dibiarkan kosong).
     */
    public static function response(string $inertiaComponent, $data = null): InertiaResponse|JsonResponse
    {
        // Deteksi Request JSON (AJAX / Axios)
        if (Request::wantsJson()) {
            // Asumsi Anda memiliki JsonResponseHelper di scope yang sama
            return JsonResponseHelper::success($data);
        }

        return inertia($inertiaComponent, $data);
    }
}
