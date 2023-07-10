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

namespace ovensia\pf\Core;

final class Input
{
    private static $_booImportVarsDone = false;

    private static $_arrUrl = null;

    private function __construct() { /* non instanciable */ }

    /**
     * Filtre les superglobales $_GET / $_POST / $_REQUEST / $_COOKIE / $_SERVER
     * Déchiffre l'URL si elle est chiffrée.
     *
     * @package pf
     * @subpackage ploopiInput
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see ploopiSecurity
     * @see ploopiCipher
     * @see Query
     */

    public static function importVars()
    {
        if (self::$_booImportVarsDone) return;

        // Si l'encodage d'url est activé, on supprime toute variable GET autre que Query
        if (in_array(Config::get('_URL_ENCODING'), array('hash', 'crypt')) || Config::get('_URL_FORCE_ENCODING'))
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
        self::_translateRewrite();

        // On traite les variables contenues dans les superglobales POST et GET
        foreach(array('POST', 'GET') as $strGlobalVar)
        {
            // Détection d'une url codée
            if (!empty($GLOBALS["_{$strGlobalVar}"]['Query']))
            {
                $GLOBALS["_{$strGlobalVar}"]['Query'] = self::_filterVar($GLOBALS["_{$strGlobalVar}"]['Query']);

                // Décodage de l'url
                $strQuery = '';

                switch (Config::get('_URL_ENCODING'))
                {
                    case 'hash':
                        try {
                            $objHashedQuery = ploopiHashedQuery::getInstance()->open($GLOBALS["_{$strGlobalVar}"]['Query']);
                            $strQuery = $objHashedQuery->getQuery();
                        }
                        catch (Core\ploopiException $e) {}
                    break;

                    case 'crypt':
                        $strQuery = ploopiCipher::getInstance()->decrypt($GLOBALS["_{$strGlobalVar}"]['Query']);
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

                    $_REQUEST[$strKey] = $GLOBALS["_{$strGlobalVar}"][$strKey] = urldecode($strValue);
                }

            }
        }

        $_GET = self::_filterVar($_GET);
        $_POST = self::_filterVar($_POST, null, !empty($_POST['ploopi_xhr']));
        $_REQUEST = self::_filterVar($_REQUEST);
        $_COOKIE = self::_filterVar($_COOKIE);
        $_SERVER = self::_filterVar($_SERVER);

        self::$_booImportVarsDone = true;
    }

    /**
     * Filtre le contenu d'une variable.
     * Gère les tableaux multi-dimensionnels.
     * Enlève les quotes si get_magic_quotes_gpc est activé.
     *
     * @param mixed $var variable à filtrer
     * @param string $varname nom de la variable (permet notamment de traiter un cas particulier avec les variables préfixées fck_)
     * @param boolean $booUtf8
     * @return mixed variable filtrée
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    protected static function _filterVar($mixVar, $strVarName = null, $booUtf8 = false)
    {
        // Traitement récursif de la variable
        if (is_array($mixVar))
        {
            foreach($mixVar as $strKey => $mixValue)
            {
                $mixVar[$strKey] = self::_filterVar($mixValue, $strKey, $booUtf8);
            }
        }
        else // Cas général, filtrage de la variable
        {
            if (get_magic_quotes_gpc()) $mixVar = stripslashes($mixVar);

            // Décodage UTF-8 (notamment utile dans le cas de certaines requêtes ajax)
            if ($booUtf8) $mixVar = utf8_decode($mixVar);

            if (substr($strVarName, 0, 5) != 'safe_') $mixVar = ploopiInputFilter::getInstance()->process($mixVar);
        }

        return $mixVar;
    }

    private static function _parseUrl()
    {
        if (is_null(self::$_arrUrl))
        {
            // Attention ! $_SERVER['REQUEST_URI'] peut contenir une url complète avec le nom de domaine
            $arrParsedURI = parse_url($_SERVER['REQUEST_URI']);
            $strRequestURI = $arrParsedURI['path'].(empty($arrParsedURI['query']) ? '' : "?{$arrParsedURI['query']}");

            $strSelfPath = ploopiKernel::getSelfPath();

            if ($strSelfPath == '' || strpos($strRequestURI, $strSelfPath) === 0) $strRequestURI = substr($strRequestURI, strlen($strSelfPath) - strlen($strRequestURI));

            $arrParsedURI = parse_url($strRequestURI);

            self::$_arrUrl = $arrParsedURI;
        }
    }

    public static function getUrl()
    {
        self::_parseUrl();

        return self::$_arrUrl;
    }

    public static function getUrlPath()
    {
        self::_parseUrl();

        return isset(self::$_arrUrl['path']) ? self::$_arrUrl['path'] : '';
    }

    public static function getUrlQuery()
    {
        self::_parseUrl();

        return isset(self::$_arrUrl['query']) ? self::$_arrUrl['query'] : '';
    }

    private static function _translateRewrite()
    {
        if (isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == '200')
        {
            self::_parseUrl();
            $booRewriteRuleFound = false;

            if (self::$_arrUrl['path'] != '')
            {
                if ($booRewriteRuleFound = (self::$_arrUrl['path'] == '/robots.txt'))
                {
                    $_REQUEST['ploopiOp'] = $_GET['ploopiOp'] = 'ploopiRobots';
                }
            }
        }
    }
}

