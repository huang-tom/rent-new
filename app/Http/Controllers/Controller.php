<?php

namespace App\Http\Controllers;

use App\Exceptions\ErrorException;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    protected function throwValidationException(Request $request, $validator)
    {
        $msg = $validator->errors()->first();

        throw new ErrorException($msg);
    }
}
