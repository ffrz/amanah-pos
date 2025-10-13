<?php

namespace App\Http\Controllers\Deploy;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class MaintenanceController extends Controller
{
    public function down()
    {
        $this->updateEnvValue('APP_MAINTENANCE_STATUS', 'down');
        return response()->json(['message' => 'Custom maintenance mode enabled']);
    }

    public function up()
    {
        $this->updateEnvValue('APP_MAINTENANCE_STATUS', 'up');
        return response()->json(['message' => 'Custom maintenance mode disabled']);
    }

    public function status()
    {
        return response()->json([
            'status' => env('APP_MAINTENANCE_STATUS', 'up')
        ]);
    }

    protected function updateEnvValue($key, $value)
    {
        $path = base_path('.env');
        if (!File::exists($path)) {
            return;
        }

        $env = File::get($path);
        $pattern = "/^{$key}=.*/m";

        if (preg_match($pattern, $env)) {
            $env = preg_replace($pattern, "{$key}={$value}", $env);
        } else {
            $env .= PHP_EOL . "{$key}={$value}";
        }

        File::put($path, $env);
    }
}
