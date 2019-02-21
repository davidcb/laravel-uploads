<?php
namespace Davidcb\Uploads;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

class Facade extends IlluminateFacade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-uploads';
    }

    /**
     * Resolve a new instance
     */
    /*public static function __callStatic($method, $args)
    {
        $instance = static::$app->make(static::getFacadeAccessor());

        return $instance->$method();
    }*/

}
