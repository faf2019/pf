<?php
namespace ovensia\pf\Core\Initializers;

use ovensia\pf\Core;

class Session
{
    public static function getInstance()
    {
        return
            Core\Session\Factory::getInstance(
                Core\Service\Controller::getService('config')->_SESSION_LAYER
            )->start();
    }
}
