<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function __construct()
    {

        if (request('key') !== env('APP_RUN_COMMAND_SECRET_KEY')) {
            abort(403, 'Please provide your secret key!');
        }
    }

}
