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
use ovensia\pf\Core\Sql;

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
abstract class QuerySud extends Query
{
    /**
     * Tableau de la clause FROM
     *
     * @var array
     */

    protected $_arrFrom;

    /**
     * Tableau de la clause WHERE
     *
     * @var array
     */

    private $_arrWhere;

    /**
     * Tableau de la clause ORDER BY
     *
     * @var array
     */

    private $_arrOrderBy;

    /**
     * Clause LIMIT
     *
     * @var string
     */

    private $_strLimit;

    /**
     * Différents types acceptés pour un élément
     *
     * @var array
     */


    /**
     * Constructeur de la classe
     *
     * @param string $strType type de requête
     * @param resource $objDb Connexion à la BDD
     */

    protected function __construct($strType = 'select', $objDb = null)
    {
        parent::__construct($objDb);

        $this->_arrFrom = array();
        $this->_arrWhere = array();
        $this->_arrOrderBy = array();
        $this->_strLimit = null;

        $strType = strtolower($strType);

        if (!in_array($strType, array('select', 'update', 'delete'))) throw new Core\Exception("Query type &laquo; {$strType} &raquo; doesn't exists");
    }

    /**
     * Ajout d'une clause WHERE à la requête
     * Si plusieurs clauses WHERE sont ajoutées, elles sont séparées par AND
     *
     * Format supportés :
     * %d int
     * %f float
     * %s string
     * %e int list
     * %g float list
     * %t string list
     * %r raw
     *
     * Numérotation des arguments possible : %1$f, %2$d, %4$r
     *
     * @param string $strWhere Clause SQL brute
     * @param mixed $mixValues Valeurs
     * @return QuerySud l'objet (fluent)
     */

    public function addWhere($strWhere, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->_arrWhere[] = array('rawsql' => $strWhere, 'values' => $mixValues);

        return $this;
    }

    /**
     * Ajoute une clause FROM à la requête (select/delete/update uniquement)
     * Si plusieurs clauses FROM sont ajoutées, elles sont séparées par ","
     *
     * @param string $strFrom Clause FROM
     * @return QuerySud l'objet (fluent)
     */

    public function addFrom($strFrom)
    {
        if (!in_array($strFrom, $this->_arrFrom)) $this->_arrFrom[] = $strFrom;

        return $this;
    }

    /**
     * Ajoute une clause ORDER BY à la requête
     * Si plusieurs clauses ORDER BY sont ajoutées, elles sont séparées par ","
     *
     * @param string $strOrderBy Clause ORDER BY
     * @return QuerySud l'objet (fluent)
     */

    public function addOrderBy($strOrderBy)
    {
        if (!in_array($strOrderBy, $this->_arrOrderBy)) $this->_arrOrderBy[] = $strOrderBy;

        return $this;
    }

    /**
     * Définit la clause LIMIT de la requête
     *
     * @param string $strLimit
     * @return QuerySud l'objet (fluent)
     */

    public function addLimit($strLimit)
    {
        $this->_strLimit = implode(', ', array_map('intval', explode(',', $strLimit)));

        return $this;
    }

    /**
     * Supprime la clause FROM
     */

    public function removeFrom()
    {
        $this->_arrFrom = array();
        return $this;
    }

    /**
     * Supprime la clause WHERE
     */

    public function removeWhere()
    {
        $this->_arrWhere = array();
        return $this;
    }

    /**
     * Supprime la clause ORDER BY
     */

    public function removeOrderby()
    {
        $this->_arrOrderBy = array();
        return $this;
    }

    /**
     * Supprime la clause LIMIT
     */

    public function removeLimit()
    {
        $this->_strLimit = null;
        return $this;
    }

    /**
     * Retourne la clause FROM
     *
     * @return string
     */

    protected function getFrom()
    {
        return empty($this->_arrFrom) ? false : ' FROM '.implode(', ', $this->_arrFrom);
    }

    /**
     * Retourne la clause WHERE
     *
     * @return string
     */

    protected function getWhere()
    {
        $arrWhere = array();
        foreach($this->_arrWhere as $arrWhereDetail) $arrWhere[] = Sql\Format::replace($arrWhereDetail, $this->objDb);

        return empty($arrWhere) ? '' : ' WHERE '.implode(' AND ', $arrWhere);
    }

    /**
     * Retourne la clause ORDER BY
     *
     * @return string
     */

    protected function getOrderby() { return empty($this->_arrOrderBy) ? '' : ' ORDER BY '.implode(', ', $this->_arrOrderBy); }

    /**
     * Retourne la clause LIMIT
     *
     * @return string
     */

    protected function getLimit() { return empty($this->_strLimit) ? '' : " LIMIT {$this->_strLimit}"; }

}
