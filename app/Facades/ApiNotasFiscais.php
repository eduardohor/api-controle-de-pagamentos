<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ApiNotasFiscais extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'api-notas-fiscais';
    }
}