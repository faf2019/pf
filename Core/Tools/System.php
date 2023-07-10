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
 * Fonctions de base du coeur de Ploopi
 *
 * @package pf
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\Tools;

/**
 * Classe ploopi_system
 *
 * @package pf
 * @subpackage timer
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class System
{

    private function __construct() { /* non instanciable */ }

    public static function getVarSize($mixedVar)
    {
        $intSize = 0;

        if (is_array($mixedVar)) foreach($mixedVar as $mixedValue) $intSize += self::getVarSize($mixedValue);
        elseif (is_object($mixedVar)) foreach(get_object_vars($mixedVar) as $mixedValue) $intSize += self::getVarSize($mixedValue);
        else $intSize += strlen($mixedVar);

        return $intSize;
    }


    /**
     * Applique récursivement une fonction sur une variable
     * Les éléments peuvent être des tableaux récursifs ou des objets récursifs
     *
     * @param callback $cbFunc fonction à appliquer sur le tableau
     * @param mixed $mixedVar variable à modifier
     * @return mixed la variable modifiée
     *
     * @see array_map
     */

    public static function mapVar($cbFunc, $mixedVar)
    {
        if (is_array($mixedVar)) { foreach($mixedVar as $key => $value) $mixedVar[$key] = self::mapVar($cbFunc, $value); return $mixedVar; }
        elseif (is_object($mixedVar)) { foreach(get_object_vars($mixedVar) as $key => $value)  $mixedVar->$key = self::mapVar($cbFunc, $value); return $mixedVar; }
        else return call_user_func($cbFunc, $mixedVar);
    }

}
