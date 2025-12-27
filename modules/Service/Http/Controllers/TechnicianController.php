<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Service\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ServiceTechnician;
use Illuminate\Http\Request;
use Modules\Service\Http\Requests\Technician\SaveRequest;
use Modules\Service\Http\Requests\Technician\GetDataRequest;
use Modules\Service\Services\TechnicianService;

class TechnicianController extends Controller
{
    public function __construct(
        protected TechnicianService $technicianService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', ServiceTechnician::class);

        return inertia('technician/Index');
    }

    public function detail($id = 0)
    {
        $item = $this->technicianService->find($id);

        $this->authorize('view', $item);

        return inertia('technician/Detail', [
            'data' => $item,
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', ServiceTechnician::class);

        $items = $this->technicianService->getData($request->validated())
            ->withQueryString();

        return JsonResponseHelper::success($items);
    }

    public function duplicate($id)
    {
        $this->authorize('create', ServiceTechnician::class);

        // Pastikan method duplicate sudah ditambahkan di service
        $item = $this->technicianService->find($id)->replicate([
            'user_id' // Biasanya user_id tidak ikut diduplikasi karena bersifat unik
        ]);

        return inertia('technician/Editor', [
            'data' => $item,
        ]);
    }

    public function editor(Request $request, $id = 0)
    {
        $item = $this->technicianService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        if ($request->expectsJson()) {
            return JsonResponseHelper::success($item);
        }

        return inertia('technician/Editor', [
            'data' => $item,
        ]);
    }

    public function save(SaveRequest $request)
    {
        $item = $this->technicianService->findOrCreate($request->id);

        $this->authorize($item->id ? 'update' : 'create', $item);

        $this->technicianService->save($item, $request->validated());

        if ($request->expectsJson()) {
            return JsonResponseHelper::success($item);
        }

        return redirect()
            ->route('service.technician.detail', $item->id)
            ->with('success', "Teknisi $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = $this->technicianService->find($id);

        $this->authorize('delete', $item);

        $this->technicianService->delete($item);

        return JsonResponseHelper::success($item, "Teknisi $item->name telah dihapus.");
    }
}
