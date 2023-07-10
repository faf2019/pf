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

namespace ovensia\pf\Core\DataObject;


class Iterator implements \Iterator
{
    private $_objRs;
    private $_strClassName;
    private $_objCurrentObject;

    public function __construct($objRs, $strClassName)
    {

        // Vérifier que objRS : extends Core\Db\Recordset\Common implements Core\Db\Recordset\Model

        $this->_objRs = $objRs;
        $this->_strClassName = $strClassName;

        // Attention cet objet est accessible de 2 manières :
        // Soit comme un iterateur classique (foreach, current, next...)
        // Soit via la méthode fetchRow (à l'ancienne façon recordset)
        // $this->next();
    }

    private function _toObject($row) {

        $obj = new $this->_strClassName();
        return $obj->openRow($row);

    }

    public function fetchRow()
    {
        return ($row = $this->_objRs->fetchRow()) ? $this->_toObject($row) : false;
    }

    public function numRows()
    {
        return $this->_objRs->numRows();
    }

    public function dataSeek($intPos = 0)
    {
        return $this->_objRs->dataSeek($intPos);
    }


    public function rewind()
    {
        $this->_objRs->rewind();
    }

    /**
     * Méthode next() de l'itérateur
     *
     */

    public function next()
    {
        $this->_objRs->next();
    }

    /**
     * Méthode key() de l'itérateur
     *
     */

    public function key()
    {
        return $this->_objRs->key();
    }

    /**
     * Méthode current() de l'itérateur
     *
     */

    public function current()
    {
        // return ($row = $this->_objRs->current()) ? $this->_toObject($row) : false;
        return $this->_toObject($this->_objRs->current());
    }

    /**
     * Méthode valid() de l'itérateur
     *
     */

    public function valid()
    {
        return $this->_objRs->valid();
    }

    /**
     * Retourne le nombre d'objets
     */

    public function count()
    {
        return $this->_objRs->numRows();
    }













    /*

    public function rewind()
    {
        $this->_objRs->rewind();
        //$this->next();
    }


    public function next()
    {
        $this->_objRs->next();

        if ($this->_objRs->valid())
        {
            $arrCurrentRow = $this->_objRs->current();

            $this->_objCurrentObject = new $this->_strClassName();

            $this->_objCurrentObject->openRow($arrCurrentRow);
        }
        else $this->_objCurrentObject = null;

    }

    public function key()
    {
        return $this->_objRs->key();
    }

    public function current()
    {
        return $this->_objCurrentObject;
    }

    public function valid()
    {
        return is_object($this->_objCurrentObject);
    }

    public function count()
    {
        return $this->_objRs->numRows();
    }

    */


    /**
     * Retourne les objets de la collection dans un tableau
     *
     * @return array tableau d'objets du type demandé
     */

    public function getArray($booFirstColKey = false)
    {
        $arrResult = array();

        $this->rewind();

        foreach($this as $objDoRecord)
        {
            if ($booFirstColKey) $arrResult[$objDoRecord->getHash()] = $objDoRecord;
            else $arrResult[] = $objDoRecord;
        }

        return $arrResult;
    }

    /**
     * Retourne les données d'objets de la collection dans un tableau
     *
     * @return array tableau des données des objets du type demandé
     */

    public function getArrayValues($booFirstColKey = false)
    {
        $arrResult = array();

        $this->rewind();

        foreach($this as $objDoRecord)
        {
            if ($booFirstColKey) $arrResult[$objDoRecord->getHash()] = $objDoRecord->getValues();
            else $arrResult[] = $objDoRecord->getValues();
        }

        return $arrResult;
    }

}
