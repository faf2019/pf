<?php
namespace ovensia\pf\Core\Controller;

use ovensia\pf\Core\Tools;

class Common
{
    use \ovensia\pf\Core\Service\Controller;
    use \ovensia\pf\Core\Builders\Singleton;

    /**
     * Variables spécifiques du contrôleur
     * @var ArrayObject
     */
    private $_objVars = null;
    private $_strTemplate = '';
    protected $_strView = '';

    protected function __construct() {
        $this->_objVars = Tools\ArrayObject::getInstance();
    }

    /**
     * Retourne une variable spécifique du contrôleur
     * @param string $strKey variable demandée
     * @return mixed contenu de la variable
     */

    public function & get($strKey) { return $this->_objVars->get($strKey); }

    /**
     * Retourne toutes les variables spécifiques du contrôleur
     * @return ArrayObject tableau contenant les variables
     */

    public function getVars() { return $this->_objVars; }

    /**
     * Met à jour une variable spécifique du contrôleur
     * @param string $strKey variable
     * @param mixed $mixValue valeur
     */

    public function set($strKey, $mixValue) { $this->_objVars->set($strKey, $mixValue); return $this; }

    /**
     * Retourne une variable spécifique du contrôleur ($objC->var)
     * @param  $strKey variable demandée
     * @return mixed contenu de la variable
     */

    public function & __get($strKey) { return $this->get($strKey); }

    /**
     * Met à jour une variable spécifique du contrôleur ($objC->var)
     * @param string $strKey variable
     * @param mixed $mixValue valeur
     */

    public function __set($strKey, $mixValue) { $this->set($strKey, $mixValue); }


    /**
     * Met à jour une liste de variables spécifiques du contrôleur
     * @param array $arrVars
     */

    public function setVars($arrVars)
    {
        foreach($arrVars as $strKey => $mixValue) $this->set($strKey, $mixValue);

        return $this;
    }

    public function getView() { return $this->_strView; }

    public function getTemplate() { return $this->_strTemplate; }

    public function setTemplate($strTemplate) { return $this->_strTemplate = $strTemplate; }


    /**
     * Indique si une redirection a été demandée
     * @return bool true si une redirection a été demandée
     */
    public function getRedirect() { return $this->getService('response')->getRedirect(); }

    /**
     * Demande une redirection
     */
    public function redirect($strUrl = '', $intErrorCode = 301)
    {
        $this->getService('response')->redirect($strUrl, $intErrorCode);
    }

}
