<?php

namespace Nicolasliu\Wxbizmsgcrypt\Facades;

use Illuminate\Support\Facades\Facade;

class Wxcrypt extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wxcrypt';
    }
}
