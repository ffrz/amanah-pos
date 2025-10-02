<?php

namespace App\Exceptions;

use Exception;

class BusinessRuleViolationException extends Exception
{
    public function __construct(string $message = "Aturan bisnis dilanggar.", int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
