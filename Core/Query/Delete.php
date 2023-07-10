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
 * Classe permettant de construire une requête SQL de type DELETE
 */
class Delete extends QuerySud
{
    /**
     * Tableau de la clause DELETE
     *
     * @var array
     */
    private $_arrDelete;

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
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */
    public function __construct($objDb = null)
    {
        $this->_arrDelete = array();
        $this->_arrInnerJoin = array();
        $this->_arrLeftJoin = array();

        return parent::__construct('delete', $objDb);
    }

    /**
     * Ajoute une clause DELETE à la requête
     *
     * @param string $strDelete
     * @return Delete l'objet (fluent)
     */

    public function addDelete($strDelete)
    {
        if (!in_array($strDelete, $this->_arrDelete)) $this->_arrDelete[] = $strDelete;

        return $this;
    }

    /**
     * Ajoute une clause LEFT JOIN à la requête
     *
     * @param string $strLeftJoin Clause LEFT JOIN
     * @param mixed $mixValues Valeur(s) de remplacement
     * @return Delete l'objet (fluent)
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
     * @return Delete l'objet (fluent)
     */

    public function addInnerJoin($strInnerJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->_arrInnerJoin[] = array('rawsql' => $strInnerJoin, 'values' => $mixValues);

        return $this;
    }

    /**
     * Retourne la clause DELETE
     *
     * @return string
     */
    protected function getDelete()
    {
        return 'DELETE '.(empty($this->_arrDelete) ? '' : implode(', ', $this->_arrDelete));
    }

    /**
     * Retourne la clause LEFT JOIN
     *
     * @return string
     */
    protected function getLeftJoin()
    {
        $arrLeftJoin = array();
        foreach($this->_arrLeftJoin as $arrLeftJoinDetail) $arrLeftJoin[] = QuerySqlFormat::replace($arrLeftJoinDetail, $this->objDb);

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
        foreach($this->_arrInnerJoin as $arrInnerJoinDetail) $arrInnerJoin[] = QuerySqlFormat::replace($arrInnerJoinDetail, $this->objDb);

        return empty($arrInnerJoin) ? '' : ' INNER JOIN '.implode(' INNER JOIN ', $arrInnerJoin);
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
                $this->getDelete().
                $this->getFrom().
                $this->getInnerJoin().
                $this->getLeftJoin().
                $this->getWhere().
                $this->getOrderby().
                $this->getLimit().
                $this->getRaw();
        }

        return $strSql;
    }

}

