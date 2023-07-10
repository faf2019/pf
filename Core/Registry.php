<?php
namespace ovensia\pf\Core;
use ovensia\pf\Core\Tools;

/**
 * Le registre contient des instances nommées de lui même.
 */

class Registry 
{
    /**
     * @var ArrayObject Contenu du registre
     */
    private $_arrRegistry = null;

    /**
     * @var ArrayObject Tableau des registres
     * 
     */
    private static $_objArrayInstance = null;

    protected function __construct()
    {
        $this->_arrRegistry = new Tools\ArrayObject();
    }

    /**
     * @param string $strRegistryInstanceName
     * @throws Core\ploopiException
     * @return Registry
     */
    public static function getRegistry($strRegistryInstanceName = 'of')
    {
        if (empty(self::$_objArrayInstance) || !self::$_objArrayInstance->exists($strRegistryInstanceName)) throw new Exception("Registry instance '{$strRegistryInstanceName}' does not exist");

        return self::$_objArrayInstance->get($strRegistryInstanceName);
    }

    /**
     *
     * Enter description here ...
     * @param $strRegistryInstanceName
     * @param $objRegistry
     * @return Registry
     */
    public static function setRegistry($strRegistryInstanceName, $objRegistry = null)
    {
        if (self::$_objArrayInstance == null) self::$_objArrayInstance = Tools\ArrayObject::getInstance();

        if ($objRegistry == null) $objRegistry = new self();

        self::$_objArrayInstance->set($strRegistryInstanceName, $objRegistry);

        return $objRegistry;
    }

    public static function deleteRegistry($strRegistryInstanceName)
    {
        if (!self::$_objArrayInstance->exists($strRegistryInstanceName)) throw new Exception("Registry instance '{$strRegistryInstanceName}' does not exist");

        self::$_objArrayInstance->delete($strRegistryInstanceName);
    }

    public function __set($strKey, $mixedValue)
    {
        return $this->set($strKey, $mixedValue);
    }

    /**
     * @param $strKey
     * @param $mixedValue
     * @return Registry
     */
    public function set($strKey, $mixedValue)
    {
        $this->_arrRegistry->set($strKey, $mixedValue);
        return $this;
    }

    public function & __get($strKey)
    {
        return $this->get($strKey);
    }

    public function & get($strKey)
    {
        return $this->_arrRegistry->get($strKey);
    }

    public function exists($strKey)
    {
        return $this->_arrRegistry->exists($strKey);
    }
}
