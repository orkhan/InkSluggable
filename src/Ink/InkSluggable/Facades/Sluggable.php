<?php namespace Ink\InkSluggable\Facades;

use Illuminate\Support\Facades\Facade;

class Sluggable extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'sluggable'; }

}