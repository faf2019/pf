<?php
namespace ovensia\pf\Core\Service;

use ovensia\pf\Core;
use ovensia\pf\Core\Exception;

trait Controller {

    /**
     * Enregistre la définition d'un service
     */
    public static function register($strServiceName, Core\Service\Definition $objDefinition)
    {
        if (self::_Definitions()->exists($strServiceName)) throw new Exception("Service definition '{$strServiceName}' already exist");
        self::_Definitions()->set($strServiceName, $objDefinition);
    }

    /**
     * Retourne un service
     */
    public static function getService($strServiceName)
    {
        // Le service existe-t-il ?
        if (self::serviceExists($strServiceName)) return self::_Services()->get($strServiceName);

        // La définition du service existe-t-elle ?
        if (!self::definitionExists($strServiceName)) throw new Exception("Service definition '{$strServiceName}' does not exist");

        self::_Services()->set($strServiceName, $objService = self::_Definitions()->get($strServiceName)->getInstance());

        Core\Output::printDebug("Service chargé: {$strServiceName}");

        return $objService;
    }

    /**
     * Le service existe-t-il ?
     */
    public static function serviceExists($strServiceName)
    {
        return self::_Services()->exists($strServiceName);
    }

    /**
     * La définition existe-t-elle ?
     */
    public static function definitionExists($strServiceName)
    {
        return self::_Definitions()->exists($strServiceName);
    }

    /**
     * Accès aux services via le Registry
     */
    private static function _Services()
    {
        try { return Core\Registry::getRegistry('services'); }
        catch(Exception $e) { return Core\Registry::setRegistry('services'); }
    }

    /**
     * Accès aux définitions de services via le Registry
     */
    private static function _Definitions()
    {
        try { return Core\Registry::getRegistry('definitions'); }
        catch(Exception $e) { return Core\Registry::setRegistry('definitions'); }
    }
}
