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

namespace ovensia\pf\Core\Module;

use ovensia\pf\Core;
use ovensia\pf\Core\Service;

class Manager extends Service\Common
{
    const _SYSTEM_MODULE_ID = '1';

    /**
     * @var array tableau des modules déjà chargés/connus
     */
    private $_arrObjModule = array();

    /**
     * Détermine le type de module en fonction du nom de la classe (ex: ploopiModuleNews => news)
     * @param string $strClassName Nom de la classe
     * @return type du module
     */
    public static function convertClassToType($strClassName)
    {
        // 12 = strlen('ploopiModule');
        return strtolower(substr($strClassName, 12 - strlen($strClassName)));
    }

    /**
     * Détermine la classe du module en fonction du type (ex: news => ploopiModuleNews)
     * @param string $strType Type du module
     * @return classe du module
     */
    public static function convertTypeToClass($strType)
    {
        return 'ovensia\\pf\\Modules\\'.ucfirst(strtolower($strType)).'\\Module';
    }

    /**
     * Retourne l'instance du module d'après son identifiant.
     * Le module doit avoir été "identifié" en session et doit proposer une classe héritée de ploopiModuleAbstract
     * @param int $intIdModule identifiant du module
     * @throws Core\ploopiException
     */

    public function getModule($intIdModule)
    {
        // Objet connu ?
        if (isset($this->_arrObjModule[$intIdModule])) return $this->_arrObjModule[$intIdModule];
        if ($this->getService('session')->exists("modules/{$intIdModule}"))
        {
            $strModuleType = $this->getService('session')->get("modules/{$intIdModule}/type");
            $strModuleClassName = self::convertTypeToClass($strModuleType);

            if (!Core\Autoloader::existsClass($strModuleClassName)) throw new Core\Exception("Invalid module '{$strModuleType}'. Class '{$strModuleClassName}' not found.");


            $this->_arrObjModule[$intIdModule] = new $strModuleClassName($intIdModule);
        }
        else throw new Core\Exception("Module id '{$intIdModule}' not found.");

        return $this->_arrObjModule[$intIdModule];
    }
}
