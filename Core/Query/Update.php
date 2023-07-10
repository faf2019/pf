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
 * Classe permettant de construire une requête SQL de type UPDATE
 */
class Update extends QuerySud
{
    /**
     * Tableau de la clause SET
     *
     * @var array
     */

    private $_arrSet;

    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */

    public function __construct($objDb = null)
    {
        $this->_arrSet = array();

        return parent::__construct('update', $objDb);
    }

    /**
     * Ajout d'une clause SET à la requête
     * Si plusieurs clauses SET sont ajoutées, elles sont séparées par ,
     *
     * @param string $strSet Clause SQL brute
     * @param mixed $mixValues Valeurs
     * @return Update l'objet (fluent)
     */

    public function addSet($strSet, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->_arrSet[] = array('rawsql' => $strSet, 'values' => $mixValues);

        return $this;
    }

    /**
     * Supprime la clause SET
     */
    public function removeSet() { $this->_arrSet = array(); }


    /**
     * Retourne la clause SET
     *
     * @return string
     */

    protected function getSet()
    {
        $arrSet = array();
        foreach($this->_arrSet as $arrSetDetail) $arrSet[] = Sql\Format::replace($arrSetDetail, $this->objDb);

        return empty($arrSet) ? '' : ' SET '.implode(', ', $arrSet);
    }

    /**
     * Retourne la clause FROM
     *
     * @return string
     */

    protected function getFrom()
    {
        return empty($this->_arrFrom) ? false : ' '.implode(', ', $this->_arrFrom);
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
            $strSql = 'UPDATE'.
                $this->getFrom().
                $this->getSet().
                $this->getWhere().
                $this->getOrderby().
                $this->getLimit();
        }

        return $strSql;
    }
}

