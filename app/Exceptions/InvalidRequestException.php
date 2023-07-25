<?php

namespace App\Exceptions;

use Exception;

class InvalidRequestException extends Exception
{
    public function render ($request) {
        return response()->json('Invalid request', 403, [],);
    }
}
