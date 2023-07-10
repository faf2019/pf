<?php
namespace ovensia\pf\Core;

use ovensia\pf\Core\Service;

abstract class Acl {

    const _ID_LEVEL_SYSTEM_ADMIN = 99;
    const _ID_LEVEL_WORKSPACE_MANAGER = 10;

    /**
     * @return boolean
     */
    static public function isManager($intIdWorkspace = null) {
        try {
            // Lecture workspace en session si non fourni
            if (empty($intIdWorkspace)) $intIdWorkspace = Service\Controller::getService('session')->get('workspace/id');
            // Vérifie le niveau d'accréditation dans l'espace de travail
            return Service\Controller::getService('session')->get("workspaces/{$intIdWorkspace}/adminlevel") > self::_ID_LEVEL_WORKSPACE_MANAGER;
        }
        catch(Core\Exception $e) {
            return false;
        }
    }

    /**
     * @return boolean
     */
    static public function isAdmin($intIdWorkspace = null) {
        try {
            // Lecture workspace en session si non fourni
            if (empty($intIdWorkspace)) $intIdWorkspace = Service\Controller::getService('session')->get('workspace/id');
            // Vérifie le niveau d'accréditation dans l'espace de travail
            return Service\Controller::getService('session')->get("workspaces/{$intIdWorkspace}/adminlevel") == self::_ID_LEVEL_SYSTEM_ADMIN;
        }
        catch(Core\Exception $e) {
            return false;
        }
    }

}
