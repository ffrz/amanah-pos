<?php

namespace App\Exceptions;

use Exception;

class ModelNotModifiedException extends Exception
{
    public function __construct(string $message = "Tidak ada perubahan pada model.")
    {
        parent::__construct($message, 422);
    }
}
