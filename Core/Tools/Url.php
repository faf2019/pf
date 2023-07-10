<?php
/*
    Copyright (c) 2010 Ovensia
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

namespace ovensia\pf\Core\Tools;

use ovensia\pf\Core\Builders;

/**
 * Gestion des URLs
 *
 * @package pf
 * @subpackage url
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Url 
{
	use Builders\Factory;
	
    private $_strScheme = null;
    private $_strUser = null;
    private $_strPass = null;
    private $_strHost = null;
    private $_intPort = null;
    private $_strPath = null;
    /**
     * @var ArrayObject
     */
    private $_objArrParams = null;
    private $_strFragment = null;

    /**
     * Constructeur de la classe
     *
     * @return ploopiUrl
     */

    public function __construct($strUrl = 'index.php')
    {
        $this->parseUrl($strUrl);
    }

    /**
     * Décompose une url et met à jour l'objet
     */

    public function parseUrl($strUrl)
    {
        // Init variables
        $this->_strScheme = null;
        $this->_strUser = null;
        $this->_strPass = null;
        $this->_strHost = null;
        $this->_intPort = null;
        $this->_strPath = null;
        $this->_objArrParams = ArrayObject::getInstance();
        $this->_strFragment = null;

        // Parse Url
        $arrParsedUrl = parse_url($strUrl);

        // Vérification de l'url
        if (!isset($arrParsedUrl['path'])) throw new Core\ploopiException("Invalid URL");

        $this->_strPath = $arrParsedUrl['path'];

        if (isset($arrParsedUrl['scheme'])) $this->_strScheme = $arrParsedUrl['scheme'];
        if (isset($arrParsedUrl['host'])) $this->_strHost = $arrParsedUrl['host'];
        if (isset($arrParsedUrl['port'])) $this->_intPort = $arrParsedUrl['port'];
        if (isset($arrParsedUrl['user'])) $this->_strUser = $arrParsedUrl['user'];
        if (isset($arrParsedUrl['pass'])) $this->_strPass = $arrParsedUrl['pass'];
        if (isset($arrParsedUrl['fragment'])) $this->_strFragment = $arrParsedUrl['fragment'];

        if (isset($arrParsedUrl['query']))
        {
            foreach(explode('&', $arrParsedUrl['query']) as $strParam)
            {
                $arrParam = explode('=', $strParam);
                if (sizeof($arrParam) > 0) $this->_objArrParams->set($arrParam[0], isset($arrParam[1]) ? $arrParam[1] : '');
            }
        }


        return $this;
    }

    public function setPath($strPath)
    {
        if (isset($strPath[0]) && $strPath[0] != '/') $strPath = '/' . $strPath;

        $this->_strPath = $strPath;
        return $this;
    }

    public function setScheme($strScheme)
    {
        $this->_strScheme = $strScheme;
        return $this;
    }

    public function setHost($strHost)
    {
        $this->_strHost = $strHost;
        return $this;
    }

    public function setPort($intPort)
    {
        if (!is_integer($intPort)) $intPort = intval($intPort, 10);
        if (empty($intPort)) throw new Core\ploopiException("Invalid port number");

        $this->_intPort = $intPort;
        return $this;
    }

    public function setUser($strUser)
    {
        $this->_strUser = $strUser;
        return $this;
    }

    public function setPass($strPass)
    {
        $this->_strPass = $strPass;
        return $this;
    }

    public function setParam($strParam, $strValue)
    {
        $this->_objArrParams->set($strParam, $strValue);
        return $this;
    }

    public function setFragment($strFragment)
    {
        $this->_strFragment = $strFragment;
        return $this;
    }

    public function deleteParam($strParam)
    {
        $this->_objArrParams->delete($strParam);
        return $this;
    }

    public function getPath() { return $this->_strPath; }

    public function getScheme() { return $this->_strScheme; }

    public function getHost() { return $this->_strHost; }

    public function getPort() { return $this->_intPort; }

    public function getUser() { return $this->_strUser; }

    public function getPass() { return $this->_strPass; }

    public function getParam($strParam) { return urldecode($this->_objArrParams->get($strParam)); }

    public function hasParam($strParam) { return $this->_objArrParams->exists($strParam); }

    public function getParams() { return $this->_objArrParams; }

    public function getFragment() { return $this->_strFragment; }

    /**
     * Génère la chaîne d'URL
     */

    public function getUrl()
    {
        $strUrl = '';

        if ($this->getHost() != '')
        {
            if ($this->getScheme() != '') $strUrl .= $this->getScheme() . '://';
            if ($this->getUser() != '') $strUrl .= $this->getUser() . ':' . $this->getPass(). '@';
            $strUrl .= $this->getHost();
            if ($this->getPort() != '') $strUrl .= ':' . $this->getPort();
        }

        $strUrl .= $this->getPath() == '' ? '/' : $this->getPath();
        if (!$this->_objArrParams->isEmpty()) $strUrl .= '?' . $this->getQuery();
        if ($this->getFragment() != '') $strUrl .= '#' . $this->getFragment();

        return $strUrl;
    }

    /**
     * Génère la chaîne de paramètres
     */

    public function getQuery()
    {
        $arrParams = new ArrayObject();
        foreach($this->_objArrParams->getIterator() as $strParam => $strValue)
            $arrParams->set($strParam, $strParam.'='.urlencode($strValue));

        return $arrParams->implode('&');
    }
}
