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

namespace ovensia\pf\Core\Skin;
use ovensia\pf\Core;

final class Factory
{
    /**
     * Factory pour la classe Skin
     * On cherche le skin d'un template qui implémente Model
     * @throws Core\Exception
     */
    final public static function getInstance($strTemplateName, $strCachePath)
    {
        $strPath = '/Templates/'.ucfirst($strTemplateName);

        // Nom de la classe à charger
        $strClassName = "ovensia\\pf".str_replace('/', "\\", $strPath)."\\Skin";

        if (!class_exists($strClassName)) throw new Core\Exception("No class '{$strClassName}' found for {$strTemplateName} skin");

        // Création d'un objet Reflexion de la classe
        $objReflection = new \ReflectionClass($strClassName);

        // On vérifie que la classe implémente l'interface
        if (!$objReflection->implementsInterface(__NAMESPACE__.'\Model')) throw new Core\Exception("Class '{$strClassName}' does not implements 'Model'");

        // On contourne exceptionnellement le constucteur privé
        $objConstructor = $objReflection->getConstructor();
        $objConstructor->setAccessible(true);

        $objSkin = $objReflection->newInstanceWithoutConstructor();
        $objConstructor->invokeArgs($objSkin, array($strTemplateName, ".{$strPath}", $strCachePath));

        return $objSkin;
    }
}
