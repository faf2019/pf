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
 * Classe permettant de construire une requête SQL de type SELECT
 */
class Select extends QuerySud
{
    /**
     * Tableau de la clause SELECT
     *
     * @var array
     */
    private $_arrSelect;

    /**
     * Tableau de la clause INNER JOIN
     *
     * @var array
     */
    private $_arrInnerJoin;

    /**
     * Tableau de la clause LEFT JOIN
     *
     * @var array
     */
    private $_arrLeftJoin;

    /**
     * Tableau de la clause GROUP BY
     *
     * @var array
     */
    private $_arrGroupBy;

    /**
     * Tableau de la clause HAVING
     *
     * @var array
     */
    private $_arrHaving;

    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */

    public function __construct($objDb = null)
    {
        $this->_arrSelect = array();
        $this->_arrInnerJoin = array();
        $this->_arrLeftJoin = array();
        $this->_arrGroupBy = array();
        $this->_arrHaving = array();

        return parent::__construct('select', $objDb);
    }

    /**
     * Ajoute une clause SELECT à la requête
     *
     * @param string $strSelect Clause SELECT
     * @param mixed $mixValues Valeur(s) de remplacement
     * @return QuerySelect l'objet (fluent)
     */

    public function addSelect($strSelect, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->_arrSelect[] = array('rawsql' => $strSelect, 'values' => $mixValues);

        return $this;
    }

    /**
     * Ajoute une clause LEFT JOIN à la requête
     *
     * @param string $strLeftJoin Clause LEFT JOIN
     * @param mixed $mixValues Valeur(s) de remplacement
     * @return QuerySelect l'objet (fluent)
     */

    public function addLeftJoin($strLeftJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->_arrLeftJoin[] = array('rawsql' => $strLeftJoin, 'values' => $mixValues);

        return $this;
    }

    /**
     * Ajoute une clause INNER JOIN à la requête
     *
     * @param string $strInnerJoin Clause INNER JOIN
     * @param mixed $mixValues Valeur(s) de remplacement
     * @return QuerySelect l'objet (fluent)
     */

    public function addInnerJoin($strInnerJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->_arrInnerJoin[] = array('rawsql' => $strInnerJoin, 'values' => $mixValues);

        return $this;
    }

    /**
     * Ajoute une clause GROUP BY à la requête
     * Si plusieurs clauses GROUP BY sont ajoutées, elles sont séparées par ","
     *
     * @param string $strGroupBy Clause ORDER BY
     * @return QuerySelect l'objet (fluent)
     */

    public function addGroupBy($strGroupBy)
    {
        if (!in_array($strGroupBy, $this->_arrGroupBy)) $this->_arrGroupBy[] = $strGroupBy;

        return $this;
    }

    /**
     * Ajout d'une clause HAVING à la requête
     * Si plusieurs clauses HAVING sont ajoutées, elles sont séparées par AND
     *
     * @param string $strWhere Clause SQL brute
     * @param mixed $mixValues Valeur(s) de remplacement
     * @return QuerySelect l'objet (fluent)
     *
     * @see addWhere
     */

    public function addHaving($strHaving, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->_arrHaving[] = array('rawsql' => $strHaving, 'values' => $mixValues);

        return $this;
    }

    /**
     * Supprime la clause SELECT
     */
    public function removeSelect() { $this->_arrSelect = array(); }

    /**
     * Supprime la clause LEFT JOIN
     */
    public function removeLeftJoin() { $this->_arrLeftJoin = array(); }

    /**
     * Supprime la clause INNER JOIN
     */
    public function removeInnerJoin() { $this->_arrInnerJoin = array(); }

    /**
     * Supprime la clause GROUP BY
     */
    public function removeGroupBy() { $this->_arrGroupBy = array(); }

    /**
     * Supprime la clause HAVING
     */
    public function removeHaving() { $this->_arrHaving = array(); }

    /**
     * Retourne la clause SELECT
     *
     * @return string
     */
    protected function getSelect()
    {
        $arrSelect = array();
        foreach($this->_arrSelect as $arrSelectDetail) $arrSelect[] = Sql\Format::replace($arrSelectDetail, $this->objDb);

        return 'SELECT '.(empty($arrSelect) ? '*' : implode(', ', $arrSelect));
    }

    /**
     * Retourne la clause LEFT JOIN
     *
     * @return string
     */
    protected function getLeftJoin()
    {
        $arrLeftJoin = array();
        foreach($this->_arrLeftJoin as $arrLeftJoinDetail) $arrLeftJoin[] = Sql\Format::replace($arrLeftJoinDetail, $this->objDb);

        return empty($arrLeftJoin) ? '' : ' LEFT JOIN '.implode(' LEFT JOIN ', $arrLeftJoin);
    }

    /**
     * Retourne la clause INNER JOIN
     *
     * @return string
     */
    protected function getInnerJoin()
    {
        $arrInnerJoin = array();
        foreach($this->_arrInnerJoin as $arrInnerJoinDetail) $arrInnerJoin[] = Sql\Format::replace($arrInnerJoinDetail, $this->objDb);

        return empty($arrInnerJoin) ? '' : ' INNER JOIN '.implode(' INNER JOIN ', $arrInnerJoin);
    }

    /**
     * Retourne la clause GROUP BY
     *
     * @return string
     */
    protected function getGroupBy() { return empty($this->_arrGroupBy) ? '' : ' GROUP BY '.implode(', ', $this->_arrGroupBy); }

    /**
     * Retourne la clause HAVING
     *
     * @return string
     */
    protected function getHaving()
    {
        $arrHaving = array();
        foreach($this->_arrHaving as $arrHavingDetail) $arrHaving[] = Sql\Format::replace($arrHavingDetail, $this->objDb);

        return empty($arrHaving) ? '' : ' HAVING '.implode(' AND ', $arrHaving);
    }

    /**
     * Génération de la requête SQL
     *
     * @return string Chaîne contenant la requête SQL générée
     */
    public function getSql()
    {
        $strSql = '';

        if ($this->getFrom() !== false)
        {
            $strSql =
                $this->getSelect().
                $this->getFrom().
                $this->getInnerJoin().
                $this->getLeftJoin().
                $this->getWhere().
                $this->getGroupBy().
                $this->getHaving().
                $this->getOrderBy().
                $this->getLimit().
                $this->getRaw();
        }

        return $strSql;
    }
}
