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
 * Gestion de la connexion à la base MySQL.
 *
 * @package Ploopi2
 * @subpackage Db
 * @copyright Ovensia
 * @license GNU General var License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\Db\Drivers\Mysql;

use ovensia\pf\Core;

/**
 * Classe MySQL d'accès aux données.
 * Permet de se connecter, d'exécuter des requêtes, etc...
 *
 * @package Ploopi2
 * @subpackage Db
 * @copyright Ovensia
 * @license GNU General var License (GPL)
 * @author Stéphane Escaich
 */

class Db extends Core\Db\Common implements Core\Db\Model
{

    /**
     * Resource de connexion à mysql
     *
     * @var resource
     */
    private $_resConnectionId;


    /**
     * Constructeur de la classe
     *
     * @return ploopiDbMysql;
     */
    public function __construct()
    {
        parent::__construct();
        $this->_resConnectionId = null;
    }

    /**
     * Constructeur de la classe. Connexion à une base de données, sélection de la base.
     *
     * @param string $strServer adresse du serveur mysql
     * @param string $strUser nom utilisateur pour la connexion à mysql
     * @param string $strPassword mot de passe utilisateur pour la connexion à mysql
     * @param string $strDatabase base à sélectionner
     * @param boolean $booPersistency true si connexion persistente, false sinon. Par défaut : false
     */

    public function connect($strServer, $strUser, $strPassword = '', $strDatabase = '', $booPersistency = false)
    {
        $this->booPersistency = $booPersistency;
        $this->strUser = $strUser;
        $this->strPassword = $strPassword;
        $this->strServer = trim($strServer);
        $this->strDatabase = trim($strDatabase);

        if($this->booPersistency)
        {
            $this->startTimer();
            $this->_resConnectionId = @mysql_pconnect($this->strServer, $this->strUser, $this->strPassword);
            $this->stopTimer();
        }
        else
        {
            $this->startTimer();
            $this->_resConnectionId = @mysql_connect($this->strServer, $this->strUser, $this->strPassword);
            $this->stopTimer();
        }

        if (mysql_errno() != 0) throw new Core\Exception(mysql_error());
        if(!$this->_resConnectionId) throw new Core\Exception('Invalid MySQL connection');

        if ($this->strDatabase != '') $this->selectDb($this->strDatabase);
        else throw new Core\Exception('No database selected');

        return $this;
    }

    /**
     * Choix d'une base
     *
     * @param string $strDatabase nom de la base de données
     * @return boolean true si sélection ok
     */

    public function selectDb($strDatabase)
    {
        if (!$this->isConnected()) return false;

        $this->strDatabase = $strDatabase;

        $this->startTimer();

        $objDbselect = @mysql_select_db($this->strDatabase, $this->_resConnectionId);

        $this->stopTimer();

        if(!$objDbselect) {
            @mysql_close();
            $this->_resConnectionId = null;
            throw new Core\Exception("Unknown database « {$strDatabase} »");
        }
        else {
            @mysql_set_charset('utf8', $this->_resConnectionId);
            // Ne fonctionne pas avec un UPDATE de caractères japonais....
            //$this->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
            //$this->query("SET CHARACTER SET 'utf8'");
        }
    }

    /**
     * Détermine si la connexion est active
     *
     * @return boolean true si la connexion est active, false sinon
     */

    public function isConnected()
    {
        return is_resource($this->_resConnectionId) && $this->_resConnectionId != 0;
    }

    /**
     * Ferme la connexion à la base de données
     *
     * @return boolean true si la connexion a été fermée
     */

    public function close()
    {
        if (!$this->isConnected()) return false;

        $this->startTimer();

        @mysql_close($this->_resConnectionId);

        $this->stopTimer();
    }

    /**
     * Exécute une requête SQL
     *
     * @param string $strQuery requête SQL à exécuter
     * @return mixed un pointeur sur le recordset (resource) ou false si la requête n'a pas pu être exécutée
     */

    public function query($strQuery)
    {
        if (!$this->isConnected()) return false;

        if($strQuery != '')
        {
            $this->intNumQueries++;

            $this->startTimer();

            @mysql_select_db($this->strDatabase, $this->_resConnectionId);

            $objRs = new Recordset(mysql_query($strQuery, $this->_resConnectionId));

            $this->addLog($strQuery, $this->stopTimer());

            if (mysql_errno() != 0) throw new Core\Exception("Sql Error : ".mysql_error()."<br /><strong>Query</strong>:".$strQuery);

            else return $objRs;
        }

        return false;
    }


    /**
     * Exécute plusieurs requêtes SQL
     *
     * @param string $strQueries requêtes
     * @return boolean true si les requêtes ont pu être exécutées, false sinon
     */
    public function multipleQueries($strQueries)
    {
        if (!$this->isConnected()) return false;

        $arrQueries = explode("\n",$strQueries);

        $strQuery = '';

        // on parse le contenu SQL
        foreach($arrQueries as $sql_line)
        {
            if(trim($sql_line) != "" && strpos($sql_line, "--") === false)
            {
                $strQuery .= $sql_line;

                // on verifie que la ligne est une requete valide
                if(preg_match("/(.*);/", $sql_line))
                {
                    $strQuery = substr($strQuery, 0, strlen($strQuery)-1);

                    // et on execute !
                    $this->query($strQuery);
                    $strQuery = '';
                }
            }
        }

        return true;
    }

    /**
     * Retourne le dernier id inséré
     *
     * @return mixed dernier id inséré ou false si la connexion n'est pas valide
     */

    public function insertId()
    {
        if (!$this->isConnected()) return false;
        return @mysql_insert_id($this->_resConnectionId);
    }

    /**
     * Renvoie la liste des tables de la base de données sélectionnée
     *
     * @return array tableau indexé contenant les tables de la base de données sélectionnée
     */

    public function listTables()
    {
        if (!$this->isConnected()) return false;

        $this->query("SHOW TABLES FROM `{$this->strDatabase}`")->getArray();
    }

    /**
     * Protège les caractères spéciaux d'une commande SQL
     *
     * @param mixed $mixVar variable à échapper
     * @return mixed variable échappée ou false si la connexion est fermée
     */

    public function addSlashes($mixVar) { return $this->isConnected() ? mysql_real_escape_string($mixVar) : false; }
}

