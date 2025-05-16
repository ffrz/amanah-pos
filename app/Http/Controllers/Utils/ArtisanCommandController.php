<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Support\Facades\Artisan;

class ArtisanCommandController extends BaseController
{
    public function migrate()
    {
        Artisan::call('migrate', ['--force' => true]);
        return 'Migrations ran successfully!';
    }
}
