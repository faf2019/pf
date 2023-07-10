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

namespace ovensia\pf\Core\Db;


/**
 * Interface de la classe ploopiDb
 *
 */
interface Model
{
    public function __construct();
    public function connect($strServer, $strUser, $strPassword = '', $strDatabase = '', $booPersistency = false);
    public function selectDb($strDatabase);
    public function close();
    public function isConnected();
    public function query($strQuery);
    public function multipleQueries($strQueries); // On garde ?
    public function insertId();
    public function listTables(); // On garde ?
    public function addSlashes($mixVar);
    public function getNumQueries();
    public function getExectimeQueries();
    public function getLog();
    public function flushLog();
    public function setLog($booLog);
}

