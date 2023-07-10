<?php
namespace ovensia\pf\Core\Initializers;

use ovensia\pf\Core;

class Skin
{
    public static function getInstance()
    {
        return Core\Skin\Factory::getInstance(
            // Core\Service\Controller::getService('session')->get('template'),
            Core\Service\Controller::getService('config')->_DEFAULT_TEMPLATE,
            Core\Service\Controller::getService('config')->_CACHE_PATH
        );
    }
}
