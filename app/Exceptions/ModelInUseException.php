<?php

namespace App\Exceptions;

use Exception;

class ModelInUseException extends Exception
{
    public function __construct(string $message = "Model sedang digunakan.")
    {
        parent::__construct($message, 400);
    }
}
