<?php
namespace ovensia\pf\Core\Controller;

//use ovensia\pf\Core\Autoloader;
//use ovensia\pf\Core\Exception;
//use ovensia\pf\Core\Controller\Application\Exception;
use ovensia\pf\Core;
use ovensia\pf\Core\Tools;
use ovensia\pf\Core\View\View;
use ovensia\pf\Core\Exception;
use ovensia\pf\Core\Controller\Action;
use ovensia\pf\Core\Controller\Exception\Error404Exception;

class Application extends \ovensia\pf\Core\Controller\Common implements \ovensia\pf\Core\Controller\Model
{

    protected $_strApplication;
    protected $_strApplicationView;
    protected $_objAction;
    protected $_objException;

    private static $_strApplicationStatic;

    /**
     * Chemin de base de l'application
     */
    protected $_strPath;

    /**
     * Traitement de l'application
     * Démarre le processus
     */
    final public static function process()
    {

        // On démarre l'application
        try {
            return self::_processForward(self::_getNameFromRequest());
        }
        // Application a rencontré une erreur
        catch(Exception $e) {
            return self::_processException($e);
        }
    }

    /**
     * Permet de récupérer l'instance en cours de manière complètement générique, sans passer par les classes filles
     */
    public static function getApplication() {
        try {
            $strClassName = 'ovensia\\pf\\Applications\\'.implode('\\', array_map('ucfirst', explode(',', self::$_strApplicationStatic))).'\\Application';
            return $strClassName::getInstance();
        }
        catch(Exception\ApplicationException $e) {
            throw new Exception("Unknown application '".self::$_strApplicationStatic."'");
            return null;
        }
    }

    private static function _processForward($strApplication)
    {
        // Classe controleur de l'application
        $strClassName = 'ovensia\\pf\\Applications\\'.implode('\\', array_map('ucfirst', explode(',', $strApplication))).'\\Application';

        try {
            // Le contrôleur de l'application est un singleton.
            $objController = $strClassName::getInstance();
        }
        catch(Exception\ApplicationException $e) {
            throw new Exception("Unknown application '{$strApplication}'");
            return null;
        }
        catch(Exception $e) {
            throw new Error404Exception("Unknown application '{$strApplication}'");
            return null;
        }

        $objController->_launch($strApplication);

        return $objController;
    }

    /**
     * Application non valide
     * On se rabat sur l'application par défaut
     */
    private static function _processException(Exception $e)
    {
        // On teste l'application par défaut
        try {
            $objController = self::getInstance();
        }
        catch(Exception $e) {
            // Erreur générale, rien ne fonctionne !?
            throw new Exception\ApplicationException("No application found");
            return null;
        }

        $objController->_launchException($e);

        return $objController;

        /*

        // Classe "controleur" générique
        $objController = new self();
        $objController->_launchException($e);

        return $objController;
        */
    }

    protected function _launchException(Core\Exception $e)
    {
        $this->_strApplication = 'Default';
        $this->_strPath = '/Applications/'.$this->_strApplication;
        $this->_objAction = null;

        // Variables pour la vue
        $this->objException = $e;
        $this->strException = $e->getMessage();
        $this->intErrorCode = 500; // Code d'erreur par défaut

        // Vue par défaut de l'application
        $this->setView('index');

        if ($e instanceof Error404Exception) {
            $this->intErrorCode = 404;
        }

        $this->getService('response')->setErrorHeader($this->intErrorCode);
    }


    /**
     * $e : Exception éventuellement générée lors du lancement de l'application
     */
    protected function _launch($strApplication)
    {
        self::$_strApplicationStatic = $this->_strApplication = $strApplication;
        $this->_strApplicationView = $strApplication;
        $this->_strPath = '/Applications/'.implode('/', array_map('ucfirst', explode(',', $strApplication)));
        $this->_objAction = null;


        // Vue par défaut de l'application
        $this->setView('index');

        // Préparation de l'action (vérifications, initialisation)
        // Permet d'identifier l'action avant de rentrer dans le processus de l'application
        $this->_objAction = Action::process($this);

        // Début du traitement de l'application
        $this->preprocess();

        if (!$this->getRedirect()) {

            try {
                $this->_objAction->exec();
                // Exécution de l'action
                // $this->_objAction = Action::process($this);
            }
            catch(Exception\Error404Exception $e) {
                // Erreur 404
                // Annulation du redirect éventuellement déclenché
                $this->redirect();
                // Il faut relancer l'action spécifique qui gère les erreurs 404
                $this->_objAction = Action::processException($this, $e);
            }
            catch(Exception\Error500Exception $e) {
                // Problème dans l'exécution de l'action
                // Annulation du redirect éventuellement déclenché
                $this->redirect();
                $this->_objAction = Action::processException($this, $e);
            }
            catch(\Exception $e) {
                // Autre erreur
                // Annulation du redirect éventuellement déclenché
                $this->redirect();
                // Problème dans l'exécution de l'action
                $this->_objAction = Action::processException($this, $e);
            }
            /*
            catch(Exception $e) {
                // Erreur 500
                // Problème dans l'exécution de l'action
                $this->_objAction = Action::processException($this, $e);
            }
            */

            // Fin du traitement de l'application
            if (!$this->_objAction->getRedirect()) $this->postprocess();
        }

        $this->endprocess();
    }


    public function render()
    {
        if (!$this->getRedirect()) {
            // Action à rendre ?
            if (!empty($this->_objAction)) {

                if (!$this->_objAction->getRedirect()) {
                    // Rendu basic sans vue applicative (uniquement vue de l'action)
                    if (empty($this->getView())) return $this->_objAction->render();
                }

                // Rendu de la vue de l'action pour insertion dans la vue de l'application
                $this->strActionView = $this->_objAction->render();
            }

            // Rendu global
            try {
                return View::getInstance($this->_strApplicationView, null, $this->getView(), $this->getTemplate())
                    ->render($this->getVars());
            }
            // Impossible de faire le rendu global !
            catch(Exception $e) {
                return '';
                // Non traité actuellement
                // $e->show();
            }
        }

        /*

        if (!$this->getRedirected() && !$this->_objAction->getRedirected()) {

            // Rendu basic sans vue applicative (uniquement vue de l'action)
            if (empty($this->getView())) return $this->_objAction->render();
            else {
                // Rendu de la vue de l'action pour insertion dans la vue de l'application
                $this->strActionView = $this->_objAction->render();

                try {

                    // Rendu global
                    return View::getInstance($this->_strApplication, null, $this->getView(), $this->getTemplate())
                        ->render($this->getVars());
                }
                // Problème de rendu de la vue liée à l'action en cours d'exécution
                // @todo Envoi vers 404 ?
                catch(Exception $e) {}
                // $e->show();
            }

        }
        */

        return '';
    }

    public function setView($strView, $strApplicationView = null) {
        $this->_strView = $strView;
        if (!empty($strApplicationView)) $this->_strApplicationView = $strApplicationView;
        return $this;
    }

    public function preprocess() {}

    public function postprocess() {}

    public function endprocess() {}

    public function getException() { return $this->_objException; }

    public function getAction() { return $this->_objAction; }

    /**
     * Retourne le nom de l'application
     */
    public function getName()
    {
        return $this->_strApplication;
    }

    private static function _getNameFromRequest() {
        // On vérifie l'application demandée...
        if (!isset(self::getService('request')->application)) throw new Exception("No application found in request");

        return strtolower(preg_replace('@[^\w,]+@', '_', self::getService('request')->application));
    }

    public function getResourcesPath() {
        $strPath = '/Applications/'.implode('/', array_map('ucfirst', explode(',', $this->_strApplicationView)));
        return '.'.$strPath.'/Resources';
    }

    public function getResourcesFullPath() {
        $strPath = '/Applications/'.implode('/', array_map('ucfirst', explode(',', $this->_strApplicationView)));
        return $this->getService('kernel')->getBasePath().$strPath.'/Resources';
    }
}
