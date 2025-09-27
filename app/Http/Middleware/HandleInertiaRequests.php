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

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function rootView(Request $request)
    {
        $module_root_view = $request->attributes->get('module_root_view', null);
        if (!$module_root_view) return $this->rootView;
        return "modules/" . $module_root_view . '/app';
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $customer = Auth::guard('customer')->user();
        return [
            ...parent::share($request),
            'company' => [
                'name' => Setting::value('company.name', env('APP_NAME', 'Koperasiku')),
                'address' => Setting::value('company.address', ''),
                'phone' => Setting::value('company.phone', ''),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'type' => $user->type,
                    'roles' => $request->user()->getRoleNames()->toArray(),
                ] : null,
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                ] : null,
            ],
            'flash' => [
                'info' => $request->session()->get('message'),
                'success' => $request->session()->get('success'),
                'warning' => $request->session()->get('warning'),
                'error' => $request->session()->get('error')
            ]
        ];
    }
}
