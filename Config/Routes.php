<?php
namespace ovensia\pf\config;

use ovensia\pf\Core;
use ovensia\pf\Core\Router\Router;
use ovensia\pf\Core\Router\Route;

abstract class Routes implements Core\Router\Routes\Model
{
    /**
     * Configuration des routes spécifiques
     */

    public static function load(Router $objRouter)
    {
    }
}
