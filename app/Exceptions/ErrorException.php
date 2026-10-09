<?php

declare(strict_types=1);


namespace App\Exceptions;

use Exception;
use Throwable;

class ErrorException extends Exception
{
    public $error = '';

    function __construct($msg = '', $code = 200, Throwable $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }
}
