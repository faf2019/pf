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

namespace ovensia\pf\Core\Db\Drivers\Mysqli;

use ovensia\pf\Core;


class Db extends Core\Db\Common implements Core\Db\Model
{
    /**
     * Object de connexion mysqli
     *
     * @var mysqli
     */

    protected $objMysqli;


    /**
     * Constructeur de la classe
     *
     * @return ploopiDbMysqli
     */
    public function __construct()
    {
        parent::__construct();
        $this->objMysqli = null;
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
        $this->strServer = $strServer;
        $this->strDatabase = $strDatabase;

        $this->startTimer();
        $this->objMysqli = @new \mysqli($this->strServer, $this->strUser, $this->strPassword);
        $this->stopTimer();

        if (mysqli_connect_errno() != 0) throw new Core\Exception(mysqli_connect_error(), E_USER_ERROR);

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
        $this->strDatabase = $strDatabase;

        if (!$this->objMysqli->select_db($this->strDatabase)) {
            throw new Core\Exception("Unknown database « {$strDatabase} »");
        }
        else {
            $this->objMysqli->set_charset("utf8");
            // Ne fonctionne pas avec un UPDATE de caractères japonais....
            //$this->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
            //$this->query("SET CHARACTER SET 'utf8'");
        }
    }

    public function getObject() {
        return $this->objMysqli;
    }

    /**
     * Détermine si la connexion est active
     *
     * @todo à terminer
     * @return boolean true si la connexion est active, false sinon
     */

    public function isConnected()
    {
        return true;
    }

    /**
     * Ferme la connexion à la base de données
     */

    public function close()
    {
        $this->startTimer();
        if (!$this->objMysqli->close()) throw new Core\Exception("Mysql Error : fermeture de connexion impossible", E_USER_ERROR);
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
        $this->intNumQueries++;

        $this->startTimer();
        $objMySqliRs = $this->objMysqli->query($strQuery);
        if ($objMySqliRs == false) throw new Core\Exception("Sql Error : ".$this->objMysqli->error."<br /><strong>Query</strong>:".$strQuery);
        $objRs = new Recordset($objMySqliRs);
        $this->addLog($strQuery, $this->stopTimer());

        return $objRs;
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

        return (true);
    }

    /**
     * Retourne le dernier id inséré
     *
     * @return mixed dernier id inséré ou false si la connexion n'est pas valide
     */

    public function insertId()
    {
        return $this->objMysqli->insert_id;
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

    public function addSlashes($mixVar) { return $this->isConnected() ? Core\Tools\System::mapVar(array($this->objMysqli, 'real_escape_string'), $mixVar) : false; }
}
