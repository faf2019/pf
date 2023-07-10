<?php
namespace ovensia\pf\Core\Initializers;

use ovensia\pf\Core;

class Db
{
    public static function getInstance()
    {
        return
            Core\Db\Factory::getInstance(
                Core\Service\Controller::getService('config')->_SQL_LAYER,
                Core\Service\Controller::getService('config')->_DEBUG
            )->connect(
                Core\Service\Controller::getService('config')->_DB_SERVER,
                Core\Service\Controller::getService('config')->_DB_LOGIN,
                Core\Service\Controller::getService('config')->_DB_PASSWORD,
                Core\Service\Controller::getService('config')->_DB_DATABASE
            );
    }
}
