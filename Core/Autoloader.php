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
 * Autoloader
 *
 * @package Ploopi2
 * @subpackage Autoload
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core;

use ovensia\pf;

/**
 * Autoloader
 *
 * @package Ploopi2
 * @subpackage Autoloader
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class Autoloader
{
    /**
     * @var bool Autoloader déjà initialisé ?
     */
    private static $_booLoaded = false;

    private static $_strDefaultPath = '/';

    private static $_arrPath = array();

    /**
     * @var array classes déjà chargées
     */
    private static $_arrLoaded = array();


    private function __construct() { /* non instanciable */ }

    /**
     * Détermine le chemin physique du fichier d'une classe
     * @static
     * @param  $strClassName nom de la classe dont on veut déterminer le chemin
     * @return string
     */
    private static function _classToFile($strClassName) {
        return implode('/', array_slice(explode('\\', $strClassName),2)).'.php';
    }

    private static function _getBackTrace() {
        foreach(debug_backtrace() as $row) {
            if (isset($row['file'])) return $row;
        }

        return false;
    }

    private static function _autoload($strClassName)
    {
        // echo '<br />'.$strClassName;

        // Classe déjà chargée
        if (in_array($strClassName, self::$_arrLoaded)) return true;
        if (class_exists($strClassName, false)) return true;

        // Sinon, on essaye d'inclure le fichier selon les règles de nommage
        // echo '<br />called: '.$strClassName; // Désactiver le buffer !
        $strClassFile = self::_classToFile($strClassName);

        if (empty($strClassFile)) return false;

        if (!is_readable($strClassFile)) {
            $strMsg = "Class file not found '{$strClassFile}'";
            foreach(debug_backtrace() as $row) if (isset($row['file'])) $strMsg .= "\n at {$row['file']} line {$row['line']}";
            //if (($rowBt = self::_getBackTrace()) !== false) $strMsg .= " at {$rowBt['file']} line {$rowBt['line']}";
            throw new Exception($strMsg);
        }

        // Inclusion du fichier de classe
        include_once $strClassFile;

        // L'idée est de détecter les fichiers de classe erronés (fichier existant mais classe non définie malgré tout)
        // pour éviter les erreurs fatales et générer une exception
        if (!class_exists($strClassName) && !trait_exists($strClassName) && !interface_exists($strClassName)) {
            $strMsg = "Class not found '{$strClassFile}'";
            foreach(debug_backtrace() as $row) if (isset($row['file'])) $strMsg .= "\n at {$row['file']} line {$row['line']}";
            //if (($rowBt = self::_getBackTrace()) !== false) $strMsg .= " at {$rowBt['file']} line {$rowBt['line']}";
            throw new Exception($strMsg);
        }

        self::$_arrLoaded[] = $strClassName;

        return true;
    }

    public static function init()
    {
        if (!self::$_booLoaded)
        {
            self::addPath(self::$_strDefaultPath);
            spl_autoload_register(null, false);
            spl_autoload_extensions('.php');
            include_once './vendor/autoload.php';
            spl_autoload_register(array(__CLASS__, '_autoload'));
            self::$_booLoaded = true;
        }
    }

    /**
     * Ajoute un chemin à l'include path global permettant le chargement automatique de classes
     * @param string $strPath chemin à ajouter
     */

    public static function addPath($strPath)
    {
        // Suppression du dernier /
        if ($strPath[strlen($strPath)-1] == '/') $strPath = substr($strPath, 0, -1);

        // Test que le dossier n'a pas déjà été chargé
        if (!isset(self::$_arrPath[$strPath]))
        {
            // Test d'existence du dossier
            if (is_readable(pf\DIRNAME.$strPath)) {
                self::$_arrPath[$strPath] = 1;
                set_include_path(get_include_path().PATH_SEPARATOR.pf\DIRNAME.$strPath);
            }
            else trigger_error('Folder \''.pf\DIRNAME.$strPath.'\' does not exists');
        }
    }

    /**
     * Vérifie la disponibilité d'une classe dans le classpath de l'autoloader.
     * Attention, vérifie juste la présence du fichier, pas le fait qu'il contienne effectivement la classe.
     * @param string $strClassName
     */

    public static function existsClass($strClassName)
    {
        foreach(explode(PATH_SEPARATOR, get_include_path()) as $strClassPath) {
            if (file_exists($strClassPath.'/'.self::_classToFile($strClassName))) return true;
        }

        return false;
    }

}
