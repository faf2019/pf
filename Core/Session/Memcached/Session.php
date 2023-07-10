<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Gestionnaire de sessions avec memcached
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\Session\Memcached;

use ovensia\pf\Core;
use ovensia\pf\Core\Query;

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
     * @var Mc
     */
    private $_objMc;

    /**
     * Ouverture de la session
     * Connexion au serveur Memcached
     */

    public function cbOpen() {

        $this->_objMc = new \Memcached();
        $this->_objMc->addServer(
            $this->getService('config')->_MEMCACHED_SERVER,
            $this->getService('config')->_MEMCACHED_PORT
        );

        return true;
    }

    /**
     * Fermeture de la session
     *
     */

    public function cbClose()
    {
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

        try {
            $data = $this->_objMc->get("session_{$strId}");
            $this->_writeLog('lecture memcached 2 ' .$strId.' - '.$data);

            $this->_objArrData->setArray(empty($data) ? null : $this->_uncompress($data));
        }
        catch (Core\Exception $e) { $this->_objArrData->setArray(); }


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

        $res = $this->_objMc->set("session_{$strId}", $this->_compress(), 0, $this->getService('config')->_SESSION_MAXLIFETIME);

        $this->_writeLog('ecriture memcached 2 '.$strId.' - '.$this->getService('config')->_SESSION_MAXLIFETIME.' '.($res == true ? 1 : 0));

        return true;
    }

    /**
     * Suppression de la session dans la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     */

    public function cbDestroy($strId)
    {
        $this->_verifySession();

        $this->_objMc->delete("session_{$strId}");

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
        return true;
    }
}
