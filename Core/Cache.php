<?php
/**
 * Gestion de la mise en cache
 *
 * @package Ploopi2
 * @subpackage Cache
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core;

/**
 * Classe de gestion du cache
 *
 * @package Ploopi2
 * @subpackage Cache
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Cache extends Service\Common
{

    /**
     * Cache activé ?
     */
    private $_booActivated;

    /**
     * Nombre d'écriture dans la page
     */
    private $_intWritings;

    /**
     * Nombre de lecture dans la page
     */
    private $_intReadings;

    private $_objCache = null;

    /**
     * Initialise le gestionnaire de cache
     */
    protected function __construct()
    {
        $this->_booActivated = $this->getService('config')->_CACHE_USE;
        $this->_intReadings = 0;
        $this->_intWritings = 0;

        if ($this->_booActivated) {

            // Nécessaire pour pouvoir utiliser PEAR (un peu oldschool...)
            $this->getService('kernel')->usePear();
            require_once 'Cache/Lite.php';

            $strPath = $this->getService('config')->_CACHE_PATH;
            if (substr($strPath, -1) != '/') $strPath .= '/';

            $this->_objCache = new \Cache_Lite(array(
                'lifeTime' => $this->getService('config')->_CACHE_DEFAULT_LIFETIME,
                'cacheDir' => $strPath
            ));
        }
    }

    /**
     * Retourne le nombre de lecture dans la page
     */
    public function getReadings() { return $this->_intReadings; }

    /**
     * Retourne le nombre d'écriture dans la page
     */
    public function getWritings() { return $this->_intWritings; }

    /**
     * Retourne true si le cache est activé
     */
    public function getActivated() { return $this->_booActivated; }


    public function get($strCacheId, $strGroupId = 'default', $booForceCache = false) {

        if ($this->_booActivated) {
            $strCacheContent = $this->_objCache->get($strCacheId, $strGroupId, $booForceCache);
            if ($strCacheContent) $this->_intReadings++;

            return $strCacheContent;
        }
        else return false;
    }

    public function save($strData)
    {
        if ($this->_booActivated) {
            $this->_objCache->save($strData);
            $this->_intWritings++;
        }
    }

    /**
     * On appelle les méthodes de la classe Cache_Lite_Output.
     * Voir la doc de PEAR.
     */

    public function __call($name, $arugments) {
        if ($this->_booActivated) return call_user_func_array(array($this->_objCache, $name), $arugments);
        else return null;
    }

}
