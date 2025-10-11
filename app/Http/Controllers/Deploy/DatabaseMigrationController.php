<?php

namespace App\Http\Controllers\Deploy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DatabaseMigrationController extends Controller
{
    public function migrate(Request $request)
    {
        $action = $request->input('action', 'migrate'); // hanya support 'migrate' by default
        Log::info('deploy.migrate.request', ['action' => $action, 'ip' => $request->ip()]);

        try {
            // enter maintenance (optional, but safer)
            Artisan::call('down', ['--no-interaction' => true, '--message' => 'Maintenance during deploy']);

            $migrateOutput = '';
            if ($action === 'migrate') {
                Artisan::call('migrate', ['--force' => true, '--step' => true]);
                $migrateOutput = Artisan::output();
            } elseif ($action === 'rollback') {
                Artisan::call('migrate:rollback', ['--force' => true]);
                $migrateOutput = Artisan::output();
            } else {
                // fallback to migrate
                Artisan::call('migrate', ['--force' => true, '--step' => true]);
                $migrateOutput = Artisan::output();
            }

            Artisan::call('up');
            $upOutput = Artisan::output();

            $full = "migrate:\n" . $migrateOutput . "\nup:\n" . $upOutput;
            Log::info('deploy.migrate.success', ['output' => substr($full, 0, 4000)]); // dont spam logs too huge

            return response()->json(['status' => 'ok', 'output' => $full], 200);
        } catch (\Throwable $e) {
            Log::error('deploy.migrate.error', ['message' => $e->getMessage()]);
            // attempt to lift maintenance if stuck
            try {
                Artisan::call('up');
            } catch (\Throwable $_) {
            }
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
