<?php
/*
    Copyright (c) 2009-2010 Ovensia
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
 * Gestion d'une collection d'objets de type "DataObject"
 *
 * @package pf
 * @subpackage data_object
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\DataObject;

use ovensia\pf\Core\Exception;
use ovensia\pf\Core\Query;
use ovensia\pf\Core\Builders;
use ovensia\pf\Core\Service;

/**
 * Classe permettant de gérer une collection d'objets de type "DataObject"
 *
 */
class Collection
{
    use Builders\Factory;

    /**
     * Nom de la classe gérée dans la collection
     *
     * @var string
     */
    private $strClassName;

    /**
     * Nom de la table liée
     *
     * @var string
     */
    private $_strTableName;

    /**
     * Connexion à la bdd
     *
     * @var resource
     */
    private $_objDb;

    /**
     * Requête
     *
     * @var Select
     */
    private $_objQuery;

    /**
     * Constructeur de la classe
     *
     * @param string $strClassName Nom de la classe gérée dans la collection (cette classe doit être héritée de data_object)
     * @param resource $objDb Connexion à la base de données
     */
    public function __construct($strClassName, $objDb = null)
    {
        $this->_strClassName = $strClassName;

        $this->_objDb = is_null($objDb) ? Service\Controller::getService('db') : $objDb;

        //On vérifie que la classe existe
        if (empty($this->_strClassName) || !class_exists($this->_strClassName)) throw new Exception("Unknown class &laquo; {$this->_strClassName} &raquo;");

        //On tente de créer une instance de la classe
        $objDoDescription = new $this->_strClassName();

        //On vérifie le type de l'objet obtenu et s'il hérite de "data_object"
        if (empty($objDoDescription) || !is_subclass_of($objDoDescription, __NAMESPACE__.'\DataObject')) throw new Exception("&laquo; {$this->_strClassName} &raquo; class is not herited from DataObject");

        $this->_strTableName = $objDoDescription->getTableName();

        $this->_objQuery = new Query\Select($this->_objDb);
        $this->_objQuery->addSelect("`{$this->_strTableName}`.*");
        $this->_objQuery->addFrom("`{$this->_strTableName}`");
    }

    /**
     * Ajoute une clause FROM à la collection
     *
     * @param string $strFrom clause from
     * @see ploopi_query
     */
    public function addFrom($strFrom)
    {
        $this->_objQuery->addFrom($strFrom);
        return $this;
    }

    /**
     * Ajoute une clause FROM à la collection
     *
     * @param string $strFrom clause from
     * @return DataObjectCollection l'objet (fluent)
     */
    public function addInnerJoin($strInnerJoin, $mixValues = null)
    {
        $this->_objQuery->addInnerJoin(str_replace('{tablename}', $this->_strTableName, $strInnerJoin), $mixValues);
        return $this;
    }

    /**
     * Ajoute une clause WHERE à la collection
     *
     * @param string $strWhere clause sql non préparée
     * @param mixed $mixValues tableau des variables ou variable seule à insérer dans la clause sql
     * @return DataObjectCollection l'objet (fluent)
     */
    public function addWhere($strWhere, $mixValues = null)
    {
        $this->_objQuery->addWhere(str_replace('{tablename}', $this->_strTableName, $strWhere), $mixValues);
        return $this;
    }

    /**
     * Ajoute une clause ORDER BY à la collection
     *
     * @param string $strOrderBy clause sql
     * @return DataObjectCollection l'objet (fluent)
     */
    public function addOrderby($strOrderBy)
    {
        $this->_objQuery->addOrderBy($strOrderBy);
        return $this;
    }

    /**
     * Définit la clause LIMIT de la collection
     *
     * @param string $strLimit
     * @return DataObjectCollection l'objet (fluent)
     */

    public function addLimit($strLimit)
    {
        $this->_objQuery->addLimit($strLimit);
        return $this;
    }


    /**
     * Retourne un iterateur sur la collection d'objets (Iterator)
     *
     * @return Iterator
     */
    public function execute()
    {
        return new Iterator($this->_objQuery->execute(), $this->_strClassName);
    }


    /**
     * Retourne le nom de la table associée à la collection d'objets
     *
     * @return string
     */
    public function getTableName() { return $this->_strTableName; }

}
