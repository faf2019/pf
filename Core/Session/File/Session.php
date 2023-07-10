<?php
/*
    Copyright (c) 2007-2010 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Gestionnaire de sessions avec une base de données
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\Session\File;

use ovensia\pf\Core;
use ovensia\pf\Core\Tools;
use ovensia\pf\Core\Query;
use ovensia\pf\Core\Exception;

/**
 * Classe permettant de remplacer le gestionnaire de session par défaut.
 * Les sessions sont stockées dans la base de données.
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Session extends Core\Session\Common implements Core\Session\Model
{

    /**
     * Pointeur sur le fichier Lock
     */

    private static $_fpLock = null;

    /**
     * Ouverture de la session
     * Connexion à la base de données indiquée dans la config ou reprise de la connexion par défaut
     */

    public function cbOpen()
    {
        return true;
    }

    /**
     * Fermeture de la session
     *
     */

    public function cbClose()
    {
        $this->stop();
        return true;
    }

    /**
     * Chargement de la session depuis la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $strId identifiant de la session
     */

    public function cbRead($strId)
    {
        $this->_verifySession();

        $this->_writeLog('lecture');

        $strPath = $this->getPath();
        //if (!file_exists($this->_getBasePath())) mkdir($this->_getBasePath(), 0700, true);

        //self::$_fpLock = fopen($strPath.'.lock', "w");
        //flock(self::$_fpLock, LOCK_EX);

        try {
            if (file_exists($this->getPath().'/Data')) $this->_objArrData->setArray($this->_uncompress(file_get_contents($this->getPath().'/Data')));
            elseif (file_exists($this->getPath())) $this->_objArrData->setArray($this->_uncompress(file_get_contents($this->getPath())));
            else $this->_objArrData->setArray(array());
        }
        catch(Exception $e) {
        }

        return '';
    }

    /**
     * Ecriture de la session dans la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $strId identifiant de la session
     * @param string $mixData données de la session
     */

    public function cbWrite($strId, $mixData)
    {
        $this->_verifySession();

        $strPath = $this->getPath();

        $this->_writeLog($strPath);

        if (!file_exists($this->_getBasePath())) mkdir($this->_getBasePath(), 0700, true);

        $resHandle = fopen($strPath, 'wb');
        if ($resHandle) {

            fwrite($resHandle, $this->_compress());
            fclose($resHandle);

            $this->_writeLog('ecriture file');
        }

        if (self::$_fpLock) {
            flock(self::$_fpLock, LOCK_UN);
            fclose(self::$_fpLock);
            if (file_exists($this->getPath().'.lock')) unlink($this->getPath().'.lock');
        }

        return true;
    }

    /**
     * Suppression de la session
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     */

    public function cbDestroy($strId)
    {
        $this->_verifySession();

        if (file_exists($this->getPath())) unlink($this->getPath());
        if (file_exists($this->getPath().'.lock')) unlink($this->getPath().'.lock');
        //Tools\FileSystem::deleteDir($this->getPath());

        return true;
    }

    /**
     * Suppression des sessions périmées (Garbage collector).
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param int $intMax durée d'une session en secondes
     */

    public function cbGc($intMax)
    {
        /*
        $this->_verifySession();

        $resFolder = opendir($this->_getBasePath());

        $intDeletetime = time() - $intMax;

        while ($strIdSession = readdir($resFolder))
        {
            if (!in_array($strIdSession, array('.', '..')))
            {
                $strSessionData = $this->_getBasePath()."/{$strIdSession}/Data";
                if (file_exists($strSessionData) && filemtime($strSessionData) < $intDeletetime)
                {
                    Tools\FileSystem::deleteDir($this->_getBasePath()."/{$strIdSession}");
                }
            }
        }
        */

        return true;
    }

    private function _getBasePath() { return $this->getService('config')->_PATHDATA.'/Session'; }

    public function getPath() { return $this->_getBasePath().'/'.$this->getId(); }

}
