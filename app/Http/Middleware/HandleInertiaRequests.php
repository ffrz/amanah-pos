<?php

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
                'name' => Setting::value('company_name', env('APP_NAME', 'Koperasiku')),
                'address' => Setting::value('company_address', ''),
                'phone' => Setting::value('company_phone', ''),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role, // ini biarin aja dulu, entah dipakai atau tidak, untuk backward compatibility

                    // spatie
                    // 'roles' => $request->user()->getRoleNames()->toArray(),
                    // 'permissions' => $request->user()->getAllPermissions()->pluck('name')->toArray(),
                ] : null,
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'username' => $customer->username,
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
