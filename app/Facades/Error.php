<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Error extends Facade
{
    protected static function getFacadeAccessor ()
    {
        return 'api_error';
    }
}
