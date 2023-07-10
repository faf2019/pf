<?php
namespace ovensia\pf\Core\Router\Routes;

use ovensia\pf\Core\Router\Router;

interface Model
{
    public static function load(Router $objRouter);
}
