<?php
/*
    Copyright (c) 2009-2010 Ovensia
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
 * Gestion des paramètres de Ploopi.
 *
 * @package pf
 * @subpackage config
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\Config;

use ovensia\pf\Config;
use ovensia\pf\Core\Service;
use ovensia\pf\Core\Exception;

/**
 * Classe d'accès aux paramètres de Ploopi.
 *
 * @package pf
 * @subpackage config
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Manager extends Service\Common
{
    private $_arrConfig = array();


    protected function __construct()
    {
        parent::__construct();
        $this->_arrConfig = array();
    }

    public function start()
    {
        parent::start();

        // Chargement configuration principale
        Config\Config::load($this);

        try {
            // Chargement de la configuration des applications
            foreach($this->_APPLICATIONS as $strApp) {

                $strConfigClass = "ovensia\\pf\\Applications\\{$strApp}\\Config\\Config";
                $strConfigClass::load($this);
            }
        }
        catch(Exception $e) {
            // Pas de fichier de config, ou erreur dans la config
        }
    }


    /**
     * Méthode d'accès au paramètres de configuration de l'application
     *
     * @param string $strName nom du paramètre
     * @return mixed valeur du paramètre
     */
    public function get($strName)
    {
        if (isset($this->_arrConfig[$strName])) return $this->_arrConfig[$strName];
        else throw new Exception("Config variable '{$strName}' unknown");
    }

    /**
     * Accesseur magique pour les paramètres de configuration de l'application
     *
     * @param string $strName nom du paramètre
     * @return mixed valeur du paramètre
     */
    public function __get($strName) { return $this->get($strName); }

    /**
     * Méthode de modification des paramètres de configuration de l'application
     *
     * @param string $strName nom du paramètre
     * @param mixed $mixValue valeur du paramètre
     */
    public function set($strName, $mixValue)
    {
        $this->_arrConfig[$strName] = $mixValue;
        return $this;
    }

    /**
     * Mutateur magique pour les paramètres de configuration de l'application
     *
     * @param string $strName nom du paramètre
     * @param mixed $mixValue valeur du paramètre
     */
    public function __set($strName, $mixValue) { $this->set($strName, $mixValue); }

    /**
     * Méthode de modification groupée des paramètres de configuration de l'application
     *
     * @param array $arrParams tableau associatif des paramètres
     */
    public function setParams($arrParams)
    {
        foreach($arrParams as $strName => $mixValue) {
            $this->_arrConfig[$strName] = $mixValue;
        }

        return $this;
    }

    /**
     * Retourne le chemin web de l'application
     * Ex: /projets/ploopi
     *
     * @return string chemin web de l'application
     */
    public function getSelfPath()
    {
        return php_sapi_name() == 'cli' ? '' : rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    }

    /**
     * Retourne l'url de l'application
     * Ex: http://serveur/projets/ploopi
     *
     * @return string url de l'application
     */
    public function getBasePath()
    {
        return php_sapi_name() == 'cli' ? '' : ((!empty($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_SSL_REQUEST']) && ($_SERVER['HTTP_X_SSL_REQUEST'] == 1 || $_SERVER['HTTP_X_SSL_REQUEST'] == true || $_SERVER['HTTP_X_SSL_REQUEST'] == 'on'))) ? 'https://' : 'http://').((!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']).((!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80' && empty($_SERVER['HTTP_HOST'])) ? ":{$_SERVER['SERVER_PORT']}" : '').rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    }

    /**
     * Retourne l'empreinte (unique) de l'application
     *
     * @return string empreinte MD5 de l'application
     */
    public function getFingerPrint()
    {
        return md5(self::getBasePath().'/'.self::get('_DB_SERVER').'/'.self::get('_DB_DATABASE'));
    }
}
