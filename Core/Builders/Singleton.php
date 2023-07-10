<?php
namespace ovensia\pf\Core\Builders;

use ovensia\pf\Core;

trait Singleton {

    /**
     * Empêche la création directe de l'objet
     */
    protected function __construct() {
    }

    /**
     * Empêche de cloner l'instance
     */
    final private function __clone() {}

    /**
     * Empêche de réveiller l'instance
     */
    final private function __wakeup() {}

    /**
     * Crée/retourne le singleton (PHP >= 5.4)
     */
    final static public function getInstance()
    {
        static $objSingleton = null;

        if ($objSingleton === null) {

            $strClassName = get_called_class();

            $objReflection  = new \ReflectionClass(get_called_class());
            $objConstructor = $objReflection->getConstructor();

            // On détourne exceptionnellement le constructeur protégé
            $objConstructor->setAccessible(true);

            $objSingleton = $objReflection->newInstanceWithoutConstructor();
            $objConstructor->invokeArgs($objSingleton, func_get_args());
        }

        return $objSingleton;
    }
}
