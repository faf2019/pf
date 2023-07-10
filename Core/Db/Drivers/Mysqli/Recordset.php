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

namespace ovensia\pf\Core\Db\Drivers\Mysqli;

use ovensia\pf\Core;


/**
 * Classe MySQLi de gestion des recordset
 * Permet de parcourir un recordset
 *
 * @package pf
 * @subpackage DbMysqli
 * @copyrightOvensia
 * @license GNU General var License (GPL)
 * @author Stéphane Escaich
 */

class Recordset extends Core\Db\Recordset\Common implements Core\Db\Recordset\Model
{
    /**
     * Retourne true si le recordset est valide
     *
     * @return boolean
     */

    protected function isValid() // Attention doit rester protected !
    {
        return is_object($this->_getQueryResult()) && $this->_getQueryResult() instanceof \mysqli_result;
    }

    /**
     * Renvoie le nombre d'enregistrement du recordset
     *
     * @return mixed nombre de lignes dans le recordset ou false si le recordset n'est pas valide
     */

    public function numRows()
    {
        if ($this->isValid()) return $this->_getQueryResult()->num_rows; else throw new Core\Exception("RecordSet invalide");
    }

    /**
     * Renvoie l'enregistrement courant de la dernière requête ou du recordset passé en paramètre
     *
     * @return mixed l'enregistrement courant (sous forme d'un tableau associatif) ou false si le recordset n'est pas valide
     */

    public function fetchRow()
    {
        if ($this->isValid()) return $this->_getQueryResult()->fetch_assoc(); else throw new Core\Exception("RecordSet invalide");
    }

    /**
     * Déplace le pointeur interne sur un enregistrement de la dernière requête ou du recordset passé en paramètre
     *
     * @param integer $intPos position dans le recordset
     * @return boolean true si le déplacement a été effectué sinon false
     */

    public function dataSeek($intPos = 0)
    {
        if ($this->isValid()) return $this->_getQueryResult()->data_seek($intPos); else throw new Core\Exception("RecordSet invalide");
    }
}
