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

use ovensia\pf\Core;
use ovensia\pf\Core\Tools;


abstract class Common
{
    /**
     * Détermine si la connexion est permanente
     *
     * @var boolean
     */

    protected $booPersistency;

    /**
     * Nom d'utilisateur pour la connexion à la BDD
     *
     * @var string
     */

    protected $strUser;

    /**
     * Mot de passe pour la connexion à la BDD
     *
     * @var string
     */

    protected $strPassword;

    /**
     * Nom du serveur (hôte, ip) pour la connexion à la BDD
     *
     * @var string
     */

    protected $strServer;

    /**
     * Nom de la base de données pour la connexion à la BDD
     *
     * @var string
     */

    protected $strDatabase;

    /**
     * Compteur de requêtes exécutées
     *
     * @var int
     */

    protected $intNumQueries;

    /**
     * Temps d'exécution SQL global depuis le début du script (en ms)
     *
     * @var int
     */
    protected $intExectimeQueries;

    /**
     * Timer d'exécution
     *
     * @var timer
     */

    protected $objTimer;

    /**
     * Gestion du log
     *
     * @var boolean
     */

    protected $_booLog;

    /**
     * Log des requêtes exécutées par l'instance
     *
     * @var array
     */

    private $_arrLog;


    protected function __construct()
    {
        // On vérifie que l'objet implémente l'interface ploopiDbInterface
        if (!($this instanceof Model)) throw new Core\Exception("Your class does not implements ".__NAMESPACE__.'\Model');

        $this->booPersistency = false;
        $this->strUser = null;
        $this->strPassword = null;
        $this->strServer = null;
        $this->strDatabase = null;
        $this->intNumQueries = 0;
        $this->intExectimeQueries = 0;

        $this->_booLog = false;

        $this->flushLog();
    }

    /**
     * Indique si le log doit être activé
     * @param  boolean $booLog true si le log est actif
     * @return ploopiDbAbstract
     */
    public function setLog($booLog)
    {
        $this->_booLog = $booLog ? true : false;
        return $this;
    }

    public function getNumQueries() { return($this->intNumQueries); }

    public function getExectimeQueries() { return($this->intExectimeQueries); }

    /**
     * Retourne le log des requêtes exécutées
     * @return array log des requêtes exécutées
     */
    public function getLog() { return $this->_arrLog; }


    /**
     * Démarre le timer
     *
     * @see timer
     * @see timer::start
     */

    protected function startTimer()
    {
        $this->objTimer = new Tools\Timer();
        $this->objTimer->start();
    }

    /**
     * Met à jour le temps d'exécution global avec le timer en cours
     *
     * @return int temps écoulé en microsecondes
     *
     * @see timer
     * @see timer::getexectime
     */

    protected function stopTimer()
    {
        $intExt = $this->objTimer->getExecTime();
        $this->intExectimeQueries += $intExt;

        return $intExt;
    }

    /**
     * Ajoute une entrée dans le log
     * @param  $strQuery requête à ajouter dans le log
     * @param int $intT durée d'exécution de la requête
     * @return ploopiDbAbstract
     */
    protected function addLog(&$strQuery, $intT = 0)
    {
        if ($this->_booLog)
        {
            $arrTrace = array();
            foreach(debug_backtrace() as $row)
            {
                if (isset($row['file'])) $arrTrace[] = $row['file'].' at line '.$row['line'];
            }

            $this->_arrLog[] = array ('q' => $strQuery, 't' => $intT, 'd' => $arrTrace);
        }

        return $this;
    }

    /**
     * Vide le log de requêtes
     */
    public function flushLog()
    {
        $this->_arrLog = array();
        return $this;
    }

    /**
     * Vide le cache de requêtes (spécifique MySQL)
     */
    public function flushQueries()
    {
        $this->query("FLUSH QUERY CACHE");
        return $this;
    }

}
