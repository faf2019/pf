<?php

namespace ovensia\pf\Core;

use ovensia\pf\Core\InputFilter;

class Request extends Service\Common
{
    /**
     * @var ArrayObject
     */
    private $_objTaintedParams = null;

    private $_arrUrl;

    protected function __construct()
    {
        parent::__construct();
        $this->_objTaintedParams = Tools\ArrayObject::getInstance();
        $this->_arrUrl = array();
    }

    /**
     * @return ploopiRequest
     */
    public function start()
    {
        parent::start();

        $this->_parseUrl();

        // Si l'encodage d'url est obligatoire, on supprime toute variable GET autre que Query
        if ($this->getService('config')->_URL_FORCE_ENCODING)
        {
            foreach($_GET as $strKey => $strValue)
            {
                if ($strKey != 'Query')
                {
                    unset($_GET[$strKey]);
                    unset($_REQUEST[$strKey]);
                }
            }
        }

        // Interprétation de l'url rewritée
        // $this->_translateRewrite();

        // On traite les variables contenues dans les superglobales POST et GET
        foreach(array('POST', 'GET') as $strGlobalVar)
        {
            foreach($GLOBALS["_{$strGlobalVar}"] as $strKey => $strValue)
            {
                $this->_objTaintedParams->set($strKey, $strValue);

                if ($strKey == 'Query')
                {
                    // Décodage de l'url
                    $strQuery = '';

                    switch ($this->getService('config')->_URL_ENCODING)
                    {
                        case 'hash':
                            try {
                                $strQuery = Tools\HashedQuery::getInstance()->open($strValue)->getQuery();
                            }
                            catch (ploopiException $e) {}
                        break;

                        case 'crypt':
                            $strQuery = Tools\Cipher::getInstance()->decrypt($strValue);
                        break;

                        case 'none':
                        default:
                            // Rien à faire
                        break;
                    }

                    // On parse l'url décodée
                    foreach(explode('&', $strQuery) as $strParam)
                    {
                        if (strstr($strParam, '=')) list($strKey, $strValue) = explode('=',$strParam);
                        else {$strKey = $strParam; $strValue = '';}

                        $this->_setParam($strKey, $strValue);
                        //$this->_objTaintedParams->set($strKey, urldecode($strValue));
                    }

                }
            }

            // Suppression des superglobales non protégées
            unset($GLOBALS["_{$strGlobalVar}"]);
        }

        // Suppression de REQUEST non protégé
        unset($GLOBALS["_REQUEST"]);

        /*
        $_COOKIE = $this->_filterVar($_COOKIE);
        $_SERVER = $this->_filterVar($_SERVER);
        */

        return $this;
    }

    public function getPath() { return $this->_arrUrl['path']; }

    public function getUri() { return $_SERVER['REQUEST_URI']; }

    public function __get($strKey) { return $this->getParam($strKey); }

    /**
     * Retourne un paramètre filtré
     * @return mixed
     */
    public function getParam($strKey)
    {
        $mixVar = $this->getTaintedParam($strKey);
        if (is_array($mixVar)) array_walk_recursive($mixVar, function(&$mixVar, $key) { $mixVar = InputFilter::getInstance()->process($mixVar); });
        else $mixVar = InputFilter::getInstance()->process($this->getTaintedParam($strKey));

        return $mixVar;
    }

    /**
     * Retourne un paramètre non filtré
     * @return mixed
     */
    public function getTaintedParam($strKey)
    {
        if ($this->_objTaintedParams->exists($strKey)) return $this->_objTaintedParams->get($strKey);
        else throw new Exception("Unkown param '{$strKey}'");
    }

    /**
     * @return ArrayObject
     */
    public function getParams()
    {
        $objParams = Tools\ArrayObject::getInstance();
        foreach($this->_objTaintedParams->getIterator() as $strKey => $strValue) $objParams->set($strKey, $this->getParam($strKey));
        return $objParams;
    }

    /**
     * Retourne les paramètres non filtrés
     */
    public function getTaintedParams()
    {
        return $this->_objTaintedParams;
    }

    public function __isset($strKey) { return $this->exists($strKey); }

    public function exists($strKey) { return $this->_objTaintedParams->exists($strKey); }

    /**
     * Route la requête entrante et lit les paramètres
     * @return void
     */
    public function route()
    {
        if (($arrParams = $this->getService('router')->route($this->getPath())) !== false)
        {
            foreach($arrParams as $strKey => $strValue) $this->_setParam($strKey, $strValue);
        }
    }

    private function _setParam($strKey, $strValue = null) { $this->_objTaintedParams->set($strKey, urldecode($strValue)); }

    private function _parseUrl()
    {
        if (empty($this->_arrUrl))
        {
            // Attention ! $_SERVER['REQUEST_URI'] peut contenir une url complète avec le nom de domaine
            $arrParsedURI = parse_url($_SERVER['REQUEST_URI']);
            $strRequestURI = $arrParsedURI['path'].(empty($arrParsedURI['query']) ? '' : "?{$arrParsedURI['query']}");

            $strSelfPath = $this->getService('kernel')->getSelfPath();

            if ($strSelfPath == '' || strpos($strRequestURI, $strSelfPath) === 0) $strRequestURI = substr($strRequestURI, strlen($strSelfPath) - strlen($strRequestURI));

            $arrParsedURI = parse_url($strRequestURI);

            $this->_arrUrl = $arrParsedURI;
        }
    }
}
