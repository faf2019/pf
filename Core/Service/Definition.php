<?php
namespace ovensia\pf\Core\Service;

use ovensia\pf\Core;
use ovensia\pf\Exception;

class Definition {

    private $_strClassname = null;

    private $_arrArguments = array();

    private $_strFactory = null;

    private $_arrInitializers = array();

    public function __construct($strClassName, $arrArguments = array(), $strFactory = 'getInstance', $arrInitializers = array()) {
        $this->_strClassname = $strClassName;
        $this->_arrArguments = $arrArguments;
        $this->_strFactory = $strFactory;
        $this->_arrInitializers = $arrInitializers;
    }

    /**
     * Crée un service selon sa définition et l'initialise
     */

    public function getInstance() {
        // Création d'un objet Reflexion de la classe
        $objReflection = new \ReflectionClass($this->_strClassname);

        $objInstance = null;

        // Factory ?
        if (!empty($this->_strFactory)) { //  && $objReflection->hasMethod($this->_strFactory)
            $objMethod = $objReflection->getMethod($this->_strFactory);

            $intRequiredParams = $objMethod->getNumberOfRequiredParameters();
            if (sizeof($this->_arrArguments) < $intRequiredParams) throw new Exception("The definition for &laquo; {$this->_strClassname} &raquo; requires at least {$intRequiredParams} parameter(s) : \n".implode(', ', $objMethod->getParameters()));

            $objInstance = $objMethod->invokeArgs(null, $this->_arrArguments);
        }
        // Constructeur public ?
        else {
            // Lecture du constructeur de la classe
            $objMethod = $objReflection->getConstructor();

            // Vérification de l'existence d'un constructeur public (pour l'appeler)
            if ($objMethod instanceof \ReflectionMethod && $objMethod->isPublic())
            {
                // On vérifie le nombre de paramètres attendus par le constructeur de la classe appelante (héritée)
                $intRequiredParams = $objMethod->getNumberOfRequiredParameters();
                if (sizeof($this->_arrArguments) < $intRequiredParams) throw new Exception("The definition for &laquo; {$this->_strClassname} &raquo; requires at least {$intRequiredParams} parameter(s) : \n".implode(', ', $objMethod->getParameters()));

                $objInstance = $objReflection->newInstanceArgs($this->_arrArguments);
            }
        }

        // Exécution des initiliaseurs
        foreach($this->_arrInitializers as $row) {
            if (isset($row['method'])) {
                $objMethod = $objReflection->getMethod($row['method']);
                if ($objMethod->isPublic()) {
                    if (isset($row['args'])) $objMethod->invokeArgs($objInstance, $row['args']);
                    else $objMethod->invoke($objInstance);
                }
           }
        }

        return $objInstance;

    }

}
