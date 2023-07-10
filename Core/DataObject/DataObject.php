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
 * Gestion de l'accès aux données.
 *
 * @package pf
 * @subpackage database
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\DataObject;

use ovensia\pf;
use ovensia\pf\Core\Exception;
use ovensia\pf\Core\Query;
use ovensia\pf\Core\Builders;
use ovensia\pf\Core\Service\Controller;

/**
 * Classe générique d'accès aux données.
 * Permet la manipulation d'enregistrements de base de données sous forme d'objets.
 *
 * @package pf
 * @subpackage database
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class DataObject
{
    use Builders\Factory;

    /**
     * Nom de la classe
     *
     * @var string
     */

    private $_strClassName;

    /**
     * Nom de la table
     *
     * @var string
     */

    private $_strTableName;

    /**
     * Tableau indexé des champs qui composent la clé primaire
     *
     * @var array
     */

    private $_arrPrimaryFields;

    /**
     * Tableau associatif des valeurs qui composent la clé primaire
     *
     * @var array
     */

    private $_arrPrimaryKey;

    /**
     * Objet de connexion à la base de données
     *
     * @var ploopiDb
     * @see ploopiDb
     */

    private $_objDb;

    /**
     * Requête SQL générée
     *
     * @var string
     * @see data_objet::getsql
     */

    private $_strSql;

    /**
     * Contenu d'un enregistrement de la table dans un tableau associatif : champ => valeur
     *
     * @var array
     */

    private $_arrFields;

    /**
     * Indique s'il s'agit d'un nouvel enregistrement (true) ou d'un enregistrement existant (false)
     *
     * @var boolean
     */

    private $_booNew;

    /**
     * Constructeur de la classe
     *
     * @param string $strTableName
     * @param array $arrPrimaryFields tableau des champs qui composent la clé primaire (chaîne autorisé si champ unique)
     * @param ploopiDb $objDb
     * @return DataObject
     */

    public function __construct($strTableName, $arrPrimaryFields = null, &$objDb = null)
    {
        $this->_strClassName = get_class($this) ;
        $this->_strTableName = $strTableName;
        $this->_arrPrimaryFields = array();
        $this->_arrPrimaryKey = array();
        $this->_arrFields = array();

        $this->_booNew = true;

        // On travaille par défaut sur l'instance de BDD proposée par le framework
        $this->setDb($objDb);

        // Traitement du cas particulier où un seul champ est passé en paramètre (sous forme d'une chaine)
        if (is_string($arrPrimaryFields)) $arrPrimaryFields = array($arrPrimaryFields);

        if (is_array($arrPrimaryFields)) $this->_arrPrimaryFields = $arrPrimaryFields;
        else
        {
            $this->_arrPrimaryFields[0] = 'id';
            $this->_arrFields['id'] = null;
        }

        // Init de la clé primaire
        foreach($this->_arrPrimaryFields as $strFieldName)
        {
            $this->_arrFields[$strFieldName] = null;
            $this->_arrPrimaryKey[$strFieldName] = null;
        }
    }

    /**
     * Permet de modifier la connexion à la base de données
     *
     * @param ploopiDb $objDb objet de connexion à la base de données
     * @return DataObject l'objet (fluent)
     */

    public function setDb(&$objDb = null)
    {
        $this->_objDb = null;

        if (is_object($objDb) && $objDb instanceof ploopiDbInterface) $this->_objDb = $objDb;
        else $this->_objDb = Controller::getService('db');

        return $this;
    }

    /**
     * Retourne la connexion à la base de données
     *
     * @return ploopiDb l'objet de connexion à la base de données
     */

    public function getDb() { return $this->_objDb; }


    /**
     * Mutateur magique pour les propriétés de l'objet
     * @param string $strFieldName nom du champ
     * @param string $strValue valeur du champ
     * @todo s'assurer de l'existence du champ (par init_description + mise en cache du modele ?)
     */
    public function __set($strFieldName, $strValue) { $this->setValue($strFieldName, $strValue); }

    /**
     * Met à jour une propriété de l'objet
     *
     * @param string $strFieldName Nom du champ
     * @param string $strValue Valeur
     * @return DataObject l'objet (fluent)
     * @todo s'assurer de l'existence du champ (par init_description + mise en cache du modele ?)
     */
    public function setValue($strFieldName, $strValue)
    {
        $this->_arrFields[$strFieldName] = $strValue;

        return $this;
    }

    /**
     * Permet de mettre à jour les propriétés de l'objet (les champs de la table)
     *
     * @param array $arrValues tableau associatif contenant les valeurs tel que "prefixe_nomduchamp" => "valeur"
     * @param string $strPrefix préfixe utilisé pour le nommage des champs dans le tableau
     * @return DataObject l'objet (fluent)
     */

    public function setValues($arrValues, $strPrefix = '')
    {
        $intPrefixLength = strlen($strPrefix);

        foreach ($arrValues as $strKey => $strValue)
        {
            if ($intPrefixLength == 0) $this->_arrFields[$strKey] = $strValue;
            else
            {
                $strPref = substr($strKey, 0, $intPrefixLength);
                if ($strPref == $strPrefix) $this->_arrFields[substr($strKey, $intPrefixLength)] = $strValue;
            }
        }

        return $this;
    }

    /**
     * Méthode magique pour vérifier l'existence d'un champ
     * @param string $strFieldName nom du champ
     */
    public function __isset($strFieldName) { return isset($this->_arrFields[$strFieldName]); }

    /**
     * Méthode magique pour supprimer un champ
     * @param string $strFieldName nom du champ
     */
    public function __unset($strFieldName)
    {
        unset($this->_arrFields[$strFieldName]);
        return $this;
    }

    /**
     * Accesseur magique pour les propriétés de l'objet
     * @param string $strFieldName nom du champ
     */
    public function __get($strFieldName) { return $this->getValue($strFieldName); }

    /**
     * Retourne la valeur d'une propriété
     *
     * @param string $strFieldName nom de la propriété
     * @return string valeur de la propriété
     */
    public function getValue($strFieldName)
    {
        if (array_key_exists($strFieldName, $this->_arrFields)) return $this->_arrFields[$strFieldName];
        else throw new Exception("Unknown object property &laquo; {$strFieldName} &raquo; for &laquo; {$this->_strClassName} &raquo;");
    }

    /**
     * Retourne les valeurs de toutes les propriétés
     *
     * @return array tableau de valeur des propriétés
     */
    public function getValues() { return $this->_arrFields; }

    /**
     * Retourne les champs qui composent la clé primaire
     *
     * @return array tableau des champs qui composent la clé primaire
     */
    protected function getPrimaryFields() { return $this->_arrPrimaryFields; }

    /**
     * Ouvre un enregistrement de la table et met à jour l'objet
     *
     * @param mixed clé
     *
     * @return int nombre d'enregistrements
     */

    public function open($mixKey)
    {
        if (!is_array($mixKey)) $mixKey = array($mixKey);

        if (sizeof($mixKey) == sizeof($this->_arrPrimaryFields))
        {
            // Requête de sélection
            $objQuery = Query\Query::getInstance('select')->addFrom($this->_strTableName);

            $i = 0;
            foreach($mixKey as $strValue) {
                $objQuery->addWhere("`{$this->_arrPrimaryFields[$i]}` = %s", $strValue);
                $i++;
            }

            $objRs = $objQuery->execute();

            if ($objRs->numRows() > 0)
            {
                if ($objRs->numRows() > 1) throw new Exception('Mutliple objects found in database');

                $this->_booNew = false;
                $this->_arrFields = $objRs->fetchRow();

                $i = 0;
                foreach($mixKey as $i => $strValue) {
                    $this->_arrPrimaryKey[$this->_arrPrimaryFields[$i]] = $this->_arrFields[$this->_arrPrimaryFields[$i]] = $strValue;
                }

            }
            else throw new Exception('Object '.$this->_strClassName.'('.implode(',', $mixKey).') not found in database');
        }
        else throw new Exception('Uncomplete primary key');

        return $this;
    }

    /**
     * Méthode d'ouverture spéciale pour "convertir" une ligne de recordset en objet
     *
     * @param array $arrRow ligne de recordset
     */

    public function openRow($arrRow)
    {
        $this->_arrFields = $arrRow;

        foreach($this->_arrPrimaryFields as $strFieldName) $this->_arrPrimaryKey[$strFieldName] = $arrRow[$strFieldName];

        $this->_booNew = false;

        return $this;
    }

    /**
     * Insère ou met à jour l'enregistrement dans la base de données
     *
     * @return mixed valeur de la clé primaire
     */

    public function save()
    {
        if ($this->_booNew) // insert
        {
            // Requête d'insertion
            $objQuery = new Query\Insert();
            $objQuery->setTable($this->_strTableName);

            foreach ($this->_arrFields as $strKey => $strValue)
            {
                if (is_null($strValue)) $objQuery->addSet("`{$this->_strTableName}`.`{$strKey}` = null");
                else $objQuery->addSet("`{$this->_strTableName}`.`{$strKey}` = %s", $strValue);
            }

            $objRs = $objQuery->execute();

            // get "static" key
            foreach($this->_arrPrimaryFields as $strFieldName) if (isset($this->_arrFields[$strFieldName])) $this->_arrPrimaryKey[$strFieldName] = $this->_arrFields[$strFieldName];

            // get insert id from insert (if 1 field primary key and autokey)
            if (sizeof($this->_arrPrimaryFields) >= 1 && $objQuery->getInsertId() !== 0)
            {
                $this->_arrPrimaryKey[$this->_arrPrimaryFields[0]] = $this->_arrFields[$this->_arrPrimaryFields[0]] = $objQuery->getInsertId();
            }

            $this->_booNew = false;
        }
        else // update
        {
            // Requête de mise à jour
            $objQuery = new Query\Update();
            $objQuery->addFrom($this->_strTableName);

            foreach ($this->_arrFields as $strKey => $strValue)
            {
                if (is_null($strValue)) $objQuery->addSet("`{$this->_strTableName}`.`{$strKey}` = null");
                else $objQuery->addSet("`{$this->_strTableName}`.`{$strKey}` = %s", $strValue);
            }

            for ($i = 0; $i < sizeof($this->_arrPrimaryFields); $i++)
            {
                $objQuery->addWhere("`{$this->_strTableName}`.`{$this->_arrPrimaryFields[$i]}` = %s", $this->_arrPrimaryKey[$this->_arrPrimaryFields[$i]]);
            }

            $objRs = $objQuery->execute();
        }

        return $this;
    }

    /**
     * Supprime l'enregistrement dans la base de données
     */

    public function delete()
    {
        // Requête de suppression
        $objQuery = new Query\Delete();
        $objQuery->addFrom($this->_strTableName);

        for ($i = 0; $i < sizeof($this->_arrPrimaryFields); $i++) $objQuery->addWhere("`{$this->_strTableName}`.`{$this->_arrPrimaryFields[$i]}` = %s", $this->_arrFields[$this->_arrPrimaryFields[$i]]);

        return $objQuery->execute();
    }


    public static function deleteFromKey($mixedKey)
    {
        $strClassName = get_called_class();

        if ($strClassName == 'DataObject') throw new Exception("Can't call deleteFromKey() on 'DataObject' class");

        if (!class_exists($strClassName)) throw new Exception("Unknown class '{$strClassName}'");

        /**
         * On instance un objet de cette classe
         */
        $objDO = new $strClassName();

        /**
         * On récupére les champs qui composent la clé primaire
         */
        $arrPrimaryFields = $objDO->getPrimaryFields();

        // Conversion de la clé en tableau, quelque soit le format passé en paramètre
        if (!is_array($mixedKey)) $mixedKey = array($mixedKey);

        $intNumArgs = sizeof($mixedKey);

        if ($intNumArgs == sizeof($arrPrimaryFields))
        {
            // Requête de suppression
            $objQuery = new Query\Delete();

            $objQuery->addFrom($objDO->getTableName());

            for ($i = 0; $i < sizeof($arrPrimaryFields); $i++) if (isset($mixedKey[$i])) $objDO->setValue($arrPrimaryFields[$i], $mixedKey[$i]);

            $objDO->delete();
        }
        else throw new Exception('Uncomplete primary key');
    }

    /**
     * Initialise les propriétés de l'objet avec la structure de la table
     *
     * @return DataObject l'objet (fluent)
     */

    public function initDescription()
    {
        $objRs = $this->_objDb->query($this->_strSql = "DESCRIBE `{$this->_strTableName}`");
        while ($row = $objRs->fetchRow()) $this->_arrFields[$row['Field']] = '';

        return $this;
    }

    /**
     * Met à jour les propriétés id_user, id_workspace, id_module de l'objet avec le contenu de la session
     *
     * @return DataObject l'objet (fluent)
     */

    public function setUwm()
    {
        $this->_arrFields['id_user'] = $_SESSION['ploopi']['userid'] ;
        $this->_arrFields['id_workspace'] = $_SESSION['ploopi']['workspaceid'];
        $this->_arrFields['id_module'] = $_SESSION['ploopi']['moduleid'];

        return $this;
    }

    /**
     * Retourne la dernière requête SQL exécutée
     *
     * @return string
     */

    public function getSql() { return $this->_strSql; }

    /**
     * Retourne la table associée à l'instance
     *
     * @return string nom de la table
     */

    public function getTableName() { return $this->_strTableName; }

    /**
     * Retourne true si l'enregistrement n'existe pas encore dans la base de données
     *
     * @return boolean
     */

    public function isNew() { return $this->_booNew; }

    /**
     * Retourne un hash de la clé de l'enregistrement
     *
     * @return string
     */
    public function getHash()
    {
        $arrHash = array();
        foreach($this->_arrPrimaryFields as $strFieldName) if (isset($this->_arrPrimaryKey[$strFieldName])) $arrHash[] = $this->_arrPrimaryKey[$strFieldName];
        return(implode(',', $arrHash));
    }

    /**
     * Génère des variables templates à partir des propriétés de l'objet
     *
     * @param Template $objTpl template
     * @param string $strPrefix préfixe à ajouter (optionnel)
     */

    public function toTemplate(&$objTpl, $strPrefix = '')
    {
        $arrVars = array();
        foreach($this->_arrFields as $strKey => $strValue) $arrVars[strtoupper("{$strPrefix}{$strKey}")] = $strValue;
        $objTpl->assign_vars($arrVars);
    }
}
