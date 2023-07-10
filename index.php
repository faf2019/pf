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

/**
 * Point d'entrée
 *
 * @package pf
 * @subpackage index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf {

    /**
     * Détermination du chemin racine de Ploopi
     */
    define(__NAMESPACE__.'\DIRNAME',  dirname(__FILE__));

    /**
     * Compatibilité PHP-FPM
     */
    require_once DIRNAME.'/Libs/fpm.php';

    /**
     * Chargement de la classe d'autoload
     */
    require_once DIRNAME.'/Core/Autoloader.php';

    /**
     * Initialisation de l'autoloader
     */
    Core\Autoloader::init();

    /**
     * Appel du front controller
     * Démarrage des services
     * Traitement de la requête entrante
     */
    Core\FrontController::getInstance()->dispatch();
}
