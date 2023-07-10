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

namespace ovensia\pf\Core\Session\Db;

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
     * @var Db
     */
    private $_objDb;

    /**
     * Ouverture de la session
     * Connexion à la base de données indiquée dans la config ou reprise de la connexion par défaut
     */

    public function cbOpen() {

        if ($this->getService('config')->_DB_SERVER != $this->getService('config')->_SESSION_DB_SERVER ||
            $this->getService('config')->_DB_DATABASE != $this->getService('config')->_SESSION_DB_DATABASE) {
            $this->_objDb = Core\Db\Factory::create($this->getService('config')->_SQL_LAYER);

            $this->_objDb->connect(
                $this->getService('config')->_SESSION_DB_SERVER,
                $this->getService('config')->_SESSION_DB_LOGIN,
                $this->getService('config')->_SESSION_DB_PASSWORD,
                $this->getService('config')->_SESSION_DB_DATABASE
            );
        }
        else $this->_objDb = $this->getService('db');

        return true;
    }

    /**
     * Fermeture de la session
     *
     */

    public function cbClose()
    {
        $this->stop();
        $this->_objArrData = null;
        $this->_objDb = null;

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
        $this->_writeLog('lecture');

        $this->_verifySession();

        if ($this->_objDb->isConnected())
        {
            Query\Query::getInstance('select', $this->_objDb)
                ->addSelect("GET_LOCK('ploopi_lock_{$strId}', 10)");

            $arrRecord = Query\Query::getInstance('select', $this->_objDb)
                ->addFrom('ploopi_session')
                ->addSelect('data')
                ->addWhere('id = %s', $strId)
                ->execute()
                ->fetchRow();
            try {
                $this->_objArrData->setArray(empty($arrRecord) ? null : $this->_uncompress($arrRecord['data']));
            }
            catch (Core\Exception $e) { $this->_objArrData->setArray(); }
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

        if ($this->_objDb->isConnected())
        {
            // Impératif de garder ces includes car l'autoload ne fonctionne pas toujours dans ce contexte
            include_once 'Core/Query/Update.php';
            include_once 'Core/Query/Replace.php';

            Query\Query::getInstance('replace', $this->_objDb)
                ->addFrom('ploopi_session')
                ->addSet('id = %s', $strId)
                ->addSet('access = %d', time())
                ->addSet('data = %s', $this->_compress())
                ->execute();

            Query\Query::getInstance('select', $this->_objDb)
                ->addSelect("RELEASE_LOCK('ploopi_lock_{$strId}')");

            $this->_writeLog('ecriture db');
        }

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

        if ($this->_objDb->isconnected())
        {
            Query\Query::getInstance('delete', $this->_objDb)
                ->addFrom('ploopi_session')
                ->addWhere('id = %s', $strId)
                ->execute();
        }

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
        $this->_verifySession();

        if ($this->_objDb->isconnected())
        {
            // Delete serialized vars
            Query\Query::getInstance('delete', $this->_objDb)
                ->addDelete('s')
                ->addFrom('ploopi_session s')
                ->addFrom('ploopi_serializedvar sv')
                ->addWhere('s.access < %d', time() - $intMax)
                ->addWhere('s.id = sv.id_session')
                ->execute();

            // Delete session vars
            Query\Query::getInstance('delete', $this->_objDb)
                ->addDelete('s')
                ->addFrom('ploopi_session s')
                ->addWhere('s.access < %d', time() - $intMax)
                ->execute();
        }

        return true;
    }
}
