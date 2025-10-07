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

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use Modules\Admin\Http\Requests\DocumentVersion\GetDataRequest;
use Modules\Admin\Services\DocumentVersionService;

class DocumentVersionController extends Controller
{
    public function __construct(
        protected DocumentVersionService $documentVersionService,
    ) {}

    public function data(GetDataRequest $request)
    {
        $items = $this->documentVersionService->getData($request->validated());
        return JsonResponseHelper::success($items);
    }
}
