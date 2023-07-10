<?php
namespace ovensia\pf\Core;

/**
 * Genres d'alias pour des trucs un peu lourds
 */
 
abstract class Ploopi {

    /**
     * @return Registry
     */
    public static function Registry($strRegistryInstanceName = 'ploopi') { return Registry::getRegistry($strRegistryInstanceName); }

    /**
     * @return Buffer
     */
    public static function Buffer() { return self::Registry()->buffer; }

    /**
     * @return Config
     */
    public static function Config() { return self::Registry()->config; }

    /**
     * @return DbInterface
     */
    public static function Db() { return self::Registry()->db; }

    /**
     * @return ErrorHandler
     */
    public static function ErrorHandler() { return self::Registry()->error_handler; }

    /**
     * @return Kernel
     */
    public static function Kernel() { return self::Registry()->kernel; }

    /**
     * @return Request
     */
    public static function Request() { return self::Registry()->request; }

    /**
     * @return Response
     */
    public static function Response() { return self::Registry()->response; }

    /**
     * @return Router
     */
    public static function Router() { return self::Registry()->router; }

    /**
     * @return Session
     */
    public static function Session() { return self::Registry()->session; }

    /**
     * @return ploopiSkin
     */
    public static function Skin() { return self::Registry()->skin; }

    /**
     * @return ploopiTimer
     */

    public static function Timer() { return self::Registry()->timer; }

    
    
    
    public static function DOC($strClassName, $objDb = null) { return DataObject\Collection::getInstance($strClassName, $objDb); }

}
