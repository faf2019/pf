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

namespace ovensia\pf\Core\Db;
use ovensia\pf\Core;

class Factory
{
    /**
     *
     * @todo vérifier le type de la classe "$strClassName"
     * @throws Core\ploopiException
     */
    public static function getInstance($strSqlLayer = 'mysql', $booLog = false)
    {
        $strClassName = __NAMESPACE__.'\\Drivers\\'.ucfirst(strtolower($strSqlLayer)).'\\Db';

        if (!class_exists($strClassName)) throw new Core\Exception("No class '{$strClassName}' found for {$strSqlLayer} layer");

        // Création d'un objet Reflexion de la classe
        $objReflection = new \ReflectionClass($strClassName);

        // On vérifie que la classe implémente l'interface ploopiDbInterface
        if (!$objReflection->implementsInterface(__NAMESPACE__.'\Model')) throw new Core\Exception("Class '{$strClassName}' does not implements 'ploopiDbInterface'");

        $objDb = $objReflection->newInstanceArgs();
        $objDb->setLog($booLog);

        return $objDb;
    }
}
