<?php
namespace ovensia\pf\Core;

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
 * Gestionnaire d'erreurs
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */


/**
 * Classe permettant de remplacer le gestionnaire d'erreur par défaut
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ErrorHandler extends Service\Common
{

    public function start()
    {
        if ($this->getService('config')->_ERROR_HANDLER)
        {
            parent::start();
            // Handler d'erreurs standards
            set_error_handler(array($this, 'cbError'));
            // Callback shutdown php, permet de catcher les erreurs fatales
            register_shutdown_function(array($this, 'cbShutdown'));
            // Désactive l'affichage par défaut des erreurs
            ini_set('display_errors', false);
            // Rapporte toutes les erreurs
            error_reporting(E_ALL);
        }

        return $this;
    }

    /**
     * Détermine si une erreur doit être affichée en fonction du paramètre de config : _ERROR_REPORTING
     *
     * @param unknown_type $intCode
     * @return unknown
     */
    private function _reportable($intCode)
    {
        // error_reporting() != 0 => permet d'éviter les erreurs "prévues" (fonctions précédées de "@")
        if (error_reporting() == 0) return false;
        // Transformation du niveau d'erreur en tableau interprétable
        $intErrorReporting = $this->getService('config')->_ERROR_REPORTING;
        $arrErrorLevels = array();

        while ($intErrorReporting > 0)
        {
           for($intI = 0, $intN = 0; $intI <= $intErrorReporting; $intI = 1 * pow(2, $intN), $intN++) $intEnd = $intI;

           $arrErrorLevels[] = $intEnd;
           $intErrorReporting = $intErrorReporting - $intEnd;
        }

        return in_array($intCode, $arrErrorLevels);
    }

    /**
     * Handler permettant de gérer les erreurs (non fatales !)
     *
     * @param int $intCode code de l'erreur
     * @param string $strMsg message d'erreur (court)
     * @param string $strFile nom du fichier d'origine
     * @param int $intLine numéro de la ligne d'origine
     * @param array $arrContext variables présentes au moment de l'erreur
     */
    public function cbError($intCode, $strMsg, $strFile, $intLine, $arrContext)
    {
        // L'erreur doit être traitée ?
        if ($this->_reportable($intCode))
        {
            // Affichage / log
            ErrorMessage::getInstance($intCode, $strMsg, debug_backtrace())
                ->writeLog()
                ->show();

            // Kill si erreur critique
            if ($intCode == E_ERROR || $intCode == E_PARSE || $intCode == E_USER_ERROR) Ploopi::Kernel()->kill();
        }
    }

    /**
     * Handler de détection des erreurs fatales
     * @todo améliorer l'affichage de l'erreur
     */

    public function cbShutdown()
    {
        if ($e = error_get_last()) {
            // L'erreur doit être traitée ?
            if ($this->_reportable($e['type']))
            {
                // Affichage / log
                // ATTENTION ces includes sont nécessaires car l'autoload ne fonctionne pas ici !
                include_once 'ErrorMessage.php';
                include_once 'LogFile.php';
                include_once 'Tools/Ustring.php';
                include_once 'Tools/Timestamp.php';

                ErrorMessage::getInstance($e['type'], $e['message'], array($e))
                    ->writeLog()
                    ->show();
            }
        }
    }

}
