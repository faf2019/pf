<?php
/*
    Copyright (c) 2009,2010 Ovensia
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

namespace ovensia\pf\Core\Query;

use ovensia\pf\Core;
use ovensia\pf\Core\Db;
use ovensia\pf\Core\Builders;
use ovensia\pf\Core\Service;

/**
 * Gestion de requêtes SQL construites
 *
 * @package pf
 * @subpackage ploopi_query
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe permettant de construire une requête SQL
 */
abstract class Query
{
    use Builders\Factory;

    /**
     * Connexion à la BDD
     *
     * @var resource
     */

    protected $objDb;

    /**
     * Tableau de clauses SQL brutes
     *
     * @var array
     */

    protected $arrRaw;

    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */

    protected function __construct($objDb = null)
    {
        if (is_object($objDb) && $objDb instanceof Db\Model) $this->objDb = $objDb;
        else $this->objDb = Service\Controller::getService('db');
    }

    /**
     * Méthode "Factory"
     *
     * @param string $objDb Connexion à la BDD
     * @param resource $objDb Connexion à la BDD
     */

    public static function getInstance($strType = 'Select', $objDb = null)
    {
        $strType = ucfirst(strtolower($strType));
        $strClassName = __NAMESPACE__."\\{$strType}";

        // Vérification du type de la requête
        if (!in_array($strType, array('Select', 'Insert', 'Update', 'Delete', 'Replace')) || !class_exists($strClassName)) throw new Core\Exception("Query type &laquo; {$strType} &raquo; doesn't exists");

        return new $strClassName($objDb);
    }

    /**
     * Ajoute une clause SQL brute, non filtrée
     *
     * @param string $strRaw chaîne SQL
     * @return Query l'objet (fluent)
     */

    public function addRaw($strRaw)
    {
        $this->arrRaw[] = $strRaw;

        return $this;
    }

    /**
     * Retourne la clause Brute
     *
     * @return string
     */

    protected function getRaw() { return empty($this->arrRaw) ? false : ' '.implode(' ', $this->arrRaw); }

    /**
     * Exécute la requête SQL
     *
     * @return ploopi_recordset
     */

    public function execute() { return $this->objDb->query($this->getSql()); }

    /**
     * Permet de redéfinir la connexion à la BDD (utile notamment après désérialisation
     *
     * @param resource $objDb Connexion à la BDD
     * @return Query l'objet (fluent)
     */

    public function setDb($objDb) { $this->objDb = $objDb; return $this; }

    /**
     * Permet de redéfinir la connexion à la BDD au réveil de l'objet  (utile notamment après désérialisation)
     */

    public function __wakeup()
    {
        $this->objDb = ploopiKernel::getDb();
    }
}
