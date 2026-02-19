<?php

namespace Cmsmaxinc\FilamentErrorPages\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cmsmaxinc\FilamentErrorPages\FilamentErrorPages
 */
class FilamentErrorPages extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Cmsmaxinc\FilamentErrorPages\FilamentErrorPages::class;
    }
}
