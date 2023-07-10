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


/**
 * Gestion de l'affichage
 * @todo à garder ?
 */

abstract class Output
{
    private static $_arrDebugVars = array();

    private function __construct() { /* non instanciable */ }

    /**
     * Affiche des informations lisibles pour une variable php (basé sur la fonction php print_r())
     *
     * @param mixed $var variable à afficher
     * @param boolean $return true si le contenu doit être retourné, false si le contenu doit être affiché (false par défaut)
     * @return mixed rien si $return = false, sinon les informations lisibles de la variable (html)
     */

    public static function printR($mixVar, $booReturn = false)
    {
        $strFormattedText = '<pre style="text-align:left;">'.print_r($mixVar, true).'</pre>';
        if ($booReturn) return $strFormattedText;
        else echo $strFormattedText;
    }

    /**
     * @todo remplacer timer par un accès au registre
     */
    public static function printDebug($mixVar, $strVarName = null)
    {
        $arrLastCall = current(debug_backtrace());

        if (is_null($strVarName)) self::$_arrDebugVars[0][] = array('trace' => $arrLastCall, 'var' => &$mixVar, 'ts' => round(Service\Controller::getService('timer')->getExecTime(),5));
        else self::$_arrDebugVars[$strVarName][] = array('trace' => $arrLastCall, 'var' => &$mixVar, 'ts' => round(Service\Controller::getService('timer')->getExecTime(),5));
    }

    /**
     * Retourne les variables à afficher
     * @static
     * @return array variables à afficher
     */
    public static function getDebugVars() { return self::$_arrDebugVars; }

    /**
     * Vide les buffers de sortie ouverts en préservant le buffer principal
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see ploopi_ob_callback
     */

    public static function obClean()
    {
        while (ob_get_level() > 1) @ob_end_clean();
        if (ob_get_level() == 1) ob_clean();
    }

    /**
     * Redirige le script vers une url et termine le script courant
     *
     * @param string $url URL de redirection
     * @param boolean $urlencode true si l'URL doit être chiffrée (true par défaut)
     * @param boolean $internal true si la redirection est interne au site (true par défaut)
     * @param int $refresh durée en seconde avant la redirection (0 par défaut)
     */

    /*

    public static function redirect($strUrl = 'admin.php', $booUrlEncode = true, $booInternal = true, $intRefresh = 0)
    {
        if ($booInternal) $strUrl = Ploopi::Kernel()->getBasePath().'/'.$strUrl;
        if ($booUrlEncode) $strUrl = self::encodeUrl($strUrl);

        if (empty($intRefresh) || !is_numeric($intRefresh)) header("Location: {$strUrl}");
        else header("Refresh: {$intRefresh}; url={$strUrl}");

        Ploopi::Kernel()->kill();
    }

    */


    public static function encodeUrl($strUrl = 'admin.php', $booAddEnvParams = true)
    {
        return $strUrl;

        $objUrl = ploopiUrl::getInstance($strUrl);

        $strUrl = '';

        if (!$objUrl->hasParam('ploopiOp'))  $objUrl->setParam('ploopiOp', 'ploopiDefault');
        // throw new Core\ploopiException("Missing parameter 'ploopiOp' in URL");

        /**
         * Ajout automatique des paramètres d'environnement en fonction des données de la session
         */
        if ($booAddEnvParams)
        {
            if (!$objUrl->hasParam('ploopiWsp')) $objUrl->setParam('ploopiWsp', ploopiSession::get('workspace/id'));
            if (!$objUrl->hasParam('ploopiMod')) $objUrl->setParam('ploopiMod', ploopiSession::get('module/id'));
        }

        if ($objUrl->getHost() != '')
        {
            if ($objUrl->getScheme() != '') $strUrl .= $objUrl->getScheme() . '://';
            if ($objUrl->getUser() != '') $strUrl .= $objUrl->getUser() . ':' . $objUrl->getPass(). '@';
            $strUrl .= $objUrl->getHost();
            if ($objUrl->getPort() != '') $strUrl .= ':' . $objUrl->getPort();
        }

        $strUrl .= $objUrl->getPath() == '' ? '/' : $objUrl->getPath();
        if (!$objUrl->getParams()->isEmpty()) $strUrl .= '?' . self::encodeQuery($objUrl->getQuery());
        if ($objUrl->getFragment() != '') $strUrl .= '#' . $objUrl->getFragment();

        return $strUrl;
    }

    public static function encodeQuery($strQuery)
    {
        /*switch (Config::get('_URL_ENCODING'))
        {
            case 'hash':
                // on va générer une clé aléatoire pour générer un hash unique asymétrique de l'url
                // puis enregistrer cette url en base de données en lui affectant un hash
                // cette url possède une durée de vie limitée

                $strQuery = 'Query='.urlencode(ploopiHashedQuery::getInstance()->save($strQuery)->getValue('hash'));
            break;

            case 'crypt':
                // on va chiffrer l'url avec un algorithme de chiffrement symétique
                $strQuery = 'Query='.urlencode(ploopiCipher::getInstance()->crypt($strQuery));
            break;

            case 'none':
            default:
                // Rien à faire
            break;
        }*/

        return $strQuery;
    }
}

