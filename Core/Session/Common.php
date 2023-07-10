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

namespace ovensia\pf\Core\Session;

use ovensia\pf\Core;
use ovensia\pf\Core\Service;
use ovensia\pf\Core\Entities;
use ovensia\pf\Core\Tools;
use ovensia\pf\Core\Query;
use ovensia\pf\Core\LogFile;

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

abstract class Common extends Service\Common
{
    private $_booCompress;

    private $_strRootName;

    /**
     * @var ArrayObject
     */
    protected $_objArrData;

    /**
     * Tableau contenant les variables serialisées
     */
    private $_arrSv;

    private $_objLogFile = null;

    /**
     * Initialise la session
     */

    public function __construct()
    {
        $this->_booCompress = true;
        $this->_objArrData = null;
        $this->_strRootName = '';
        $this->_arrSv = null;

        ini_set('session.save_handler', 'user');
        ini_set('session.gc_probability', self::getService('config')->_SESSION_GC_PROBABILITY);
        ini_set('session.gc_maxlifetime', self::getService('config')->_SESSION_MAXLIFETIME);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        //ini_set('session.save_path', $this->getService('config')->_PATHDATA.'/Session');

        session_set_save_handler(
            array($this, 'cbOpen'),
            array($this, 'cbClose'),
            array($this, 'cbRead'),
            array($this, 'cbWrite'),
            array($this, 'cbDestroy'),
            array($this, 'cbGc')
        );
    }

    public function getId() { return session_id(); }

    public function __get($strKey) { return $this->get($strKey); }

    public function __set($strKey, $mixValue) { return $this->set($strKey, $mixValue); }

    public function __isset($strKey) { return $this->exists($strKey); }

    public function __unset($strKey) { return $this->delete($strKey); }

    /**
     * Vérification de l'existence d'une variable en session
     *
     * @param string/array $mixKey chemin vers la variable
     * @return boolean true si la variable existe
     */

    public function exists($mixKey = '')
    {
        $this->_verifySession();
        return $this->_objArrData->exists($mixKey);
    }

    /**
     * Retourne true si la variable est vide
     *
     * @param string/array $mixKey chemin vers la variable
     * @return boolean true si la variable est vide
     */

    public function isEmpty($miKey = '')
    {
        $this->_verifySession();
        return $this->_objArrData->isEmpty($miKey);
    }

    /**
     * Lecture d'une variable en session
     *
     * @param string/array $mixKey chemin vers la variable
     * @return mixed contenu de la variable ou null
     */
    public function & get($mixKey = null)
    {
        $this->_verifySession();
        return $this->_objArrData->get($mixKey);
    }

    /**
     * Stockage d'une variable en session
     *
     * @param string/array $mixKey chemin vers la variable
     * @param mixed $mixValue variable à stocker
     * @return ploopiSession
     */
    public function set($mixKey, $mixValue)
    {
        $this->_verifySession();
        $this->_objArrData->set($mixKey, $mixValue);
        return $this;
    }

    /**
     * Mise à jour d'une variable en session
     *
     * @param string/array $mixKey chemin vers la variable
     * @param mixed $mixValue variable à stocker
     * @return ploopiSession
     */
    public function merge($mixKey, $mixValue)
    {
        $this->_verifySession();
        $this->_objArrData->merge($mixKey, $mixValue);
        return $this;
    }

    /**
     * Supprime une variable en session
     *
     * @param string/array $mixKey chemin vers la variable
     * @return boolean true si la variable a été supprimée
     */

    public function delete($mixKey = '')
    {
        $this->_verifySession();
        $this->_objArrData->delete($mixKey);
        return $this;
    }


    /**
     * Retourne true si la session est valide (non vide et empreinte correcte)
     */

    public function isValid()
    {
        $this->_verifySession();
        return !$this->isEmpty() && $this->get('security/fingerprint') == $this->getService('config')->_FINGERPRINT;
    }


    /**
     * Démarre la session
     * @return Session
     */
    public function start()
    {
        parent::start();

        $this->_objArrData = new Core\Tools\ArrayObject();
        session_start();

        return $this;
    }

    public function writeClose() { session_write_close(); }

    /**
     * Détruit la session et regénère un identifiant de session
     *
     * @see session_regenerate_id
     */

    public function destroyId()
    {
        $this->_verifySession();

        $strPreviousId = session_id();
        session_regenerate_id(false);
        session_destroy();
    }


    /**
     * Retourne la taille de la session en octets
     */

    public function getSize()
    {
        $this->_verifySession();
        return Core\Tools\System::getVarSize($this->_objArrData->getArray());
    }


    /**
     * Affichage du contenu de la session de Ploopi
     */
    public function printR()
    {
        $this->_verifySession();
        $this->_objArrData->printR();
    }

    /**
     * Affichage du contenu de la session de Ploopi pour debug
     */
    public function printDebug()
    {
        $this->_verifySession();
        Core\Output::printDebug($this->_objArrData, 'Session');
    }


    protected function _compress()
    {
        $strData = serialize($this->_objArrData->getArray());
        //Core\LogFile::getInstance($this->getService('config')->_PATHDATA.'/session.log')->write("COMPRESS\n".$strData);
        return $this->_booCompress ? base64_encode(@gzcompress($strData)) : base64_encode($strData);
    }

    protected function _uncompress($strData)
    {
        //Core\LogFile::getInstance($this->getService('config')->_PATHDATA.'/session.log')->write("UNCOMPRESS\n".$strData);
        return $this->_booCompress && $strData != '' ? unserialize(@gzuncompress(base64_decode($strData))) : unserialize(base64_decode($strData));
    }

    /**
     * Ecriture du log
     */
    protected function _writeLog($str) {
        if (!($this->_objLogFile instanceof LogFile)) $this->_objLogFile = LogFile::getInstance($this->getService('config')->_PATHDATA.'/Log/session.log');
        $this->_objLogFile->write($str);
    }

    /**
     * Contrôle l'intégrité de la session
     * @return void
     */
    protected function _verifySession() { if (!$this->isStarted()) throw new Core\Exception("Session is not started"); }


}
