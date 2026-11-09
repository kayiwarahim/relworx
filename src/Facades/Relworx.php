<?php

namespace KayiwaRahim\Relworx\Facades;

use Illuminate\Support\Facades\Facade;

class Relworx extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'relworx';
    }
}
