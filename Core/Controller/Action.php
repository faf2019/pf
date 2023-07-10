<?php
namespace ovensia\pf\Core\Controller;

//use ovensia\pf\Core\Autoloader;
use ovensia\pf\Core\Exception;
use ovensia\pf\Core\Controller\Exception\Error404Exception;
use ovensia\pf\Core\Tools;
use ovensia\pf\Core\View\View;
use ovensia\pf\Core;

class Action extends \ovensia\pf\Core\Controller\Common implements \ovensia\pf\Core\Controller\Model
{
    protected $_objApplication;
    protected $_strApplication;
    protected $_strController;
    protected $_strControllerView;
    protected $_strApplicationView;
    protected $_strAction;
    protected $_objException;

    /**
     * Appelle le contrôleur (en fonction des paramètres de la requête entrante)
     */
    public static function process($objApplication)
    {
        //if (!is_a($objApplication,'ovensia\pf\Core\Controller\Application\Application')) throw new Exception('Incorrect type for Application');

        $strController = self::_getControllerNameFromRequest();
        $strAction = self::_getActionNameFromRequest();

        try {
            // Traitement de l'action
            return self::_processForward($objApplication, $strController, $strAction);
        }
        catch(Exception $e) {
            // Traitement de l'exception
            return self::_processException($objApplication, $e);
        }
    }

    /**
     * Déclenchement de l'action "Error" si l'application a rencontré une exception (application inconnue par exemple)
     */

    public static function processError($objApplication)
    {
        //if (!is_a($objApplication,'ovensia\pf\Core\Controller\Application\Application')) throw new Exception('Incorrect type for Application');

        $strController = 'error';
        $strAction = 'index';

        try {
            // Traitement de l'action
            return self::_processException($objApplication, $objApplication->getException());
        }
        catch(Exception $e) {
            // Traitement de l'exception
            return self::_processException($objApplication, $e);
        }
    }

    private static function _processForward($objApplication, $strController, $strAction) {

        // Classe "controleur" du module
        $strClassName = 'ovensia\\pf\\Applications\\'.
            implode('\\', array_map('ucfirst', explode(',', $objApplication->getName()))).'\\Controllers\\'.
            implode('\\', array_map('ucfirst', explode(',', $strController)));

        try {
            // Le contrôleur de l'action est un singleton.
            $objController = $strClassName::getInstance();
        }
        catch(Core\Exception $e) {
            throw new Error404Exception("Unknown controller '{$strController}' (Application '".$objApplication->getName()."')");
        }

        $objController->_launch($objApplication, $strController, $strAction);

        return $objController;
    }

    /**
     * Problème lors de l'exécution de l'action (404, 500)
     */
    private static function _processException($objApplication, Exception $e)
    {

        try {
            // On va tenter d'exécuter le contrôleur "error" de l'application s'il existe
            $strClassName = 'ovensia\\pf\\Applications\\'.
                implode('\\', array_map('ucfirst', explode(',', $objApplication->getName()))).'\\Controllers\\'.
                'Error';

            // Le contrôleur de l'action est un singleton.
            $objController = $strClassName::getInstance();
            $objController->_launchException($objApplication, $e);
        }
        catch(Core\Exception $e) {
            // Classe "controleur" générique
            $objController = new self();
            $objController->_launchException($objApplication, $e);
        }

        return $objController;
    }

    /**
     * Problème lors de l'exécution de l'action (404, 500)
     */
    public static function processException($objApplication, Exception $e)
    {
        // Classe "controleur" générique
        $objController = new self();
        $objController->_launchException($objApplication, $e);

        return $objController;
    }


    protected function _launch($objApplication, $strController, $strAction)
    {
        $this->_booRedirected = false;
        $this->_objApplication = $objApplication;
        $this->_strApplication = $objApplication->getName();
        $this->_strApplicationView = $objApplication->getName();
        $this->_strController = $strController;
        $this->_strControllerView = $strController;
        $this->_strAction = $strAction;

        // On vérifie l'existence de l'action, sinon pas la peine d'aller plus loin
        if (!$this->_actionExists($this->_strAction)) throw new Error404Exception("Unknown action '{$this->_strController}/{$this->_strAction}' (Application '".$this->_objApplication->getName()."')");

        // Vue basée par défaut sur l'action demandée
        $this->setView($this->_strAction);

        // if (!$this->_booRedirected) $this->_render();
    }

    protected function _launchException($objApplication, Exception $e)
    {
        $this->_objApplication = $objApplication;
        $this->_strApplication = $objApplication->getName();
        $this->_strApplicationView = $objApplication->getName();
        $this->_strController = 'error';
        $this->_strControllerView = 'error';

        // Variables pour la vue
        $this->objException = $e;
        $this->strException = $e->getMessage();

        if ($e instanceof Error404Exception) {
            $this->_strAction = 'err404';
            $this->getService('response')->setErrorHeader(404);

        } else {
            $this->_strAction = 'err500';
            $this->getService('response')->setErrorHeader(500);
        }

        // Vue basée par défaut sur l'action demandée
        $this->setView($this->_strAction);

        // Ici on n'exécute rien.
        // Voir si on peut exécuter une action spécifique de l'application pour traiter les erreurs ?
    }

    public function exec()
    {
        // Ici on exécute l'action du contrôleur
        $this->{$this->_strAction}();
    }


    public function render()
    {
        try {
            if (!$this->getRedirect() && $this->getView()) {

                return View::getInstance($this->_strApplicationView, $this->_strControllerView, $this->getView(), $this->getTemplate())
                        ->render($this->getVars());
            }
        }
        // Problème de rendu de la vue liée à l'action en cours d'exécution
        catch(Core\Exception $e) {
            // Il s'agit d'une action interne (erreur) ?
            if (get_class($this) == __NAMESPACE__.'\Action') {
                return $this->strException;
            }
            else {
                // Vue manquante
                // return $e->getMessage();
            }
        }

        return '';
    }

    /**
     * Définition de la vue à utiliser. En option on peut utilise une vue d'un autre controleur
     */
    public function setView($strView, $strControllerView = null, $strApplicationView = null) {
        $this->_strView = $strView;
        if (!empty($strControllerView)) $this->_strControllerView = $strControllerView;
        if (!empty($strApplicationView)) $this->_strApplicationView = $strApplicationView;

        return $this;
    }

    private function _actionExists($strAction)
    {
        try {
            $objReflectionMethod = new \ReflectionMethod(get_class($this), $strAction);
            return ($objReflectionMethod->isPublic() && !$objReflectionMethod->isConstructor());
        }
        catch (\Exception $e) { return false; }
    }

    public function getException()
    {
        return $this->_objException;
    }

    public function getApplication()
    {
        return $this->_objApplication;
    }

    /**
     * Retourne le nom du contrôleur
     */
    public function getControllerName()
    {
        return $this->_strController;
    }

    /**
     * Retourne le nom de l'action
     */
    public function getName()
    {
        return $this->_strAction;
    }


    /**
     * Lecture/formatage du contrôleur : "ControllerName"
     */
    private static function _getControllerNameFromRequest() {
        return self::getService('request')->exists('controller') ? implode(array_map('ucfirst', explode('_', strtolower(preg_replace('@[^\w]+@', '_', self::getService('request')->getParam('controller')))))) : 'Root';
    }

    /**
     * Lecture/formatage de l'action : "actionName"
     */
    private static function _getActionNameFromRequest() {
        return self::getService('request')->exists('action') ? lcfirst(implode(array_map('ucfirst', explode('_', strtolower(preg_replace('@[^\w]+@', '_', self::getService('request')->getParam('action'))))))) : 'index';
    }


    public function err404() {
        $this->getService('response')->setErrorHeader(404);
    }

    public function err500() {
        $this->getService('response')->setErrorHeader(500);
    }
}
