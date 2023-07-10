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

/**
 * Gestion des recordsets
 *
 * @package pf
 * @subpackage DbMysql
 * @copyright Ovensia
 * @license GNU General var License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\Db\Recordset;

use ovensia\pf\Core;

/**
 * Classe MySQL de gestion des recordset.
 * Permet de parcourir un recordset.
 * 2 façons pour le parcourir.
 * Soit via l'iterateur (foreach directement sur l'objet)
 * Soit via fetchRow()
 *
 * @package pf
 * @subpackage DbMysql
 * @copyright Ovensia
 * @license GNU General var License (GPL)
 * @author Stéphane Escaich
 */

abstract class Common implements \Iterator
{
    /**
     * Ressource vers le résultat de la requête (type indéfini)
     *
     * @var mixed
     */

    private $_resQueryResult;

    /**
     * Clé de l'enregistrement courant
     *
     * @var int
     */

    private $_intKey;

    /**
     * Enregistrement courant
     *
     * @var array
     */

    private $_arrCurrentRow;

    /**
     * Constructeur
     *
     * @param mixed $resQueryResult ressource vers le résultat de la requête
     */

    public function __construct($resQueryResult)
    {
        // Lecture du nom de la classe appelante
        $strClassName = get_class($this);

        // Création d'un objet Reflexion de la classe
        $objReflection = new \ReflectionClass($strClassName);

        // On vérifie que l'objet implémente l'interface ploopiDbRecordSetInterface
        if (!$objReflection->implementsInterface(__NAMESPACE__.'\Model')) throw new Core\Exception("Class '{$strClassName}' does not implements '".__NAMESPACE__."\Model'");

        $this->_resQueryResult = $resQueryResult;
        $this->_intKey = -1;
        $this->_arrCurrentRow = null;
        // On garde la possibilité de l'utiliser en fetchrow classique (à l'ancienne), donc on ne se pré-positionne pas sur le 1er élément
        //$this->next();
    }

    /**
     * Destructeur de la classe.
     */

    public function __destruct()
    {
        unset($this->_resQueryResult);
    }

    /**
     * Retourne la ressource vers le résultat de requête
     * @return mixed ressource vers le résultat de la requête
     */
    protected function _getQueryResult() { return $this->_resQueryResult; }

    /**
     * Méthode rewind() de l'itérateur
     *
     */

    public function rewind()
    {
        $this->_intKey = -1;
        $this->_arrCurrentRow = null;
        $this->dataSeek(0);
        $this->next();
    }

    /**
     * Méthode next() de l'itérateur
     *
     */

    public function next()
    {
        $this->_arrCurrentRow = $this->fetchRow();
        $this->_intKey++;
    }

    /**
     * Méthode key() de l'itérateur
     *
     */

    public function key()
    {
        $this->_control();
        return $this->_intKey;
    }

    /**
     * Méthode current() de l'itérateur
     *
     */

    public function current()
    {
        $this->_control();
        return $this->_arrCurrentRow;
    }

    /**
     * Méthode valid() de l'itérateur
     *
     */

    public function valid()
    {
        $this->_control();
        return is_array($this->_arrCurrentRow);
    }


    private function _control()
    {
        // On vérifie qu'on est sur un état valide
        if ($this->_intKey == -1) {
            $this->_arrCurrentRow = $this->fetchRow();
            $this->_intKey++;
        }
    }

    /**
     * Retourne dans un tableau le contenu de la dernière requête ou du recordset passé en paramètre
     *
     * @param boolean $booFirstColKey true si la première colonne doit être utilisée comme clé
     * @return mixed un tableau indexé contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */

    public function getArray($booFirstColKey = false)
    {
        if ($this->isValid())
        {
            $arrData = array();

            if ($this->numRows())
            {
                $this->dataSeek(0);
                while ($row = $this->fetchRow())
                {
                    if ($booFirstColKey)
                    {
                        $strKey = current($row);
                        array_shift($row);

                        if (sizeof($row) == 0) $arrData[$strKey] = $strKey;
                        elseif (sizeof($row) == 1) $arrData[$strKey] = $row[key($row)];
                        else $arrData[$strKey] = $row;
                    }
                    else $arrData[] = $row;
                }
            }
            return $arrData;
        }
        else throw new Core\Exception("Invalid recordSet");
    }

    /**
     * Retourne dans au format JSON le contenu de la dernière requête ou du recordset passé en paramètre
     *
     * @param boolean $booFirstColKey true si la première colonne doit être utilisée comme clé
     * @return string une chaîne au format JSON contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */

    public function getJson($booFirstColKey = false)
    {
        if ($this->isValid())
        {
            $arrData = array();

            if ($this->numRows())
            {
                $this->dataSeek(0);
                while ($row = $this->fetchRow())
                {
                    if ($booFirstColKey)
                    {
                        $strKey = current($row);
                        array_shift($row);

                        if (sizeof($row) == 0) $arrData[$strKey] = $strKey;
                        elseif (sizeof($row) == 1) $arrData[$strKey] = $fields[key($row)];
                        else $$arrData[$strKey] = $row;
                    }
                    else $arrData[] = $row;
                }
            }
            return json_encode($arrData);
        }
        else throw new Core\Exception("RecordSet invalide");
    }
}
