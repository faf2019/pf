<?php
/**
 Role du routeur :

 analyser une url entrante et trouver un motif à traduire

 ce motif doit indiquer un chemin unique qui va déterminer
 un module, un controleur, une action (les 3 obligatoires)
 Ex : webedit/article/modify/32 ou webedit/artmod/32 (peu importe la tête du motif, il faut au moins rendre obligatoire le module)

 Le routeur va permettre également "d'extraire" des variables du chemin
 Dans l'exemple précédent 32 = id ou id_user

 Alimente donc ploopiRequest (params)


 Il va aussi générer l'url en fonction du nom de la route appelée.
 Il va reconstruire le chemin.

 Pouvoir gérer plusieurs types de routes

 Basic/Regexp

Le routeur doit demander les routes de chaque module
Chaque module pourrait fournir un fichier de "routes" (routes.php)



Chemins

/admin/workspace/module/controller/action/param/value/param/value

/index



\/w-[a-z\-]+ : Espace seul

\/w-[a-z][a-z\-]*\/m-[a-z][a-z\-]* : Espace + Module

\/w-[a-z][a-z\-]*\/m-[a-z][a-z\-]*\/[a-z]+ : Espace + Module + Controller

\/w-[a-z][a-z\-]*\/m-[a-z][a-z\-]*\/[a-z]+/[a-z]+ : Espace + Module + Controller + Action

\/m-[a-z][a-z\-]*\/[a-z]+ : Module + Controller

\/m-[a-z][a-z\-]*\/[a-z]+/[a-z]+ : Module + Controller + Action


Ex :

/w-espace-principal
/w-espace-principal/m-doc/document/download/?id=12
/w-espace-principal/m-doc/document/download/id/12



/home

/news/index
     /view
     /email

/tutorials/index
          /view

/forum/index
      /category
      /view
      /add
      /update
      /reply
      /search
      /report - report des abus etc...

/support/index
        /view
        /search
        /submit
        /confirmation -
        /comment - ajouter un commentaire

/login/index - gestion de l'authentification

/logout/index - détruis l'instance courante de Auth

/error/noroute - gère toutes les erreurs 404
      /failure - gère les erreurs du site
      /privileges - gère les erreurs de privilèges

/admin - un cms pour gérer le site

http://alain-sahli.developpez.com/tutoriels/php/zend-framework/acl/






(Ordre LIFO pour les routes)

*/

namespace ovensia\pf\Core\Router;

use ovensia\pf\Config;
use ovensia\pf\Core;
use ovensia\pf\Core\Service;
use ovensia\pf\Core\Tools;
use ovensia\pf\Core\Exception;

class Router extends Service\Common
{
    /**
     * @var ArrayObject
     */
    private $_objRoutes;

    /**
     * @var string
     */
    private $_strRouteName;


    protected function __construct()
    {
        parent::__construct();
        $this->_objRoutes = Tools\ArrayObject::getInstance();
        $this->_strRouteName = null;
    }

    /**
     * @return Router
     */
    public function start()
    {
        parent::start();

        // Définitions particulières des routes
        Config\Routes::load($this);

        try {
            // Définition des routes par les applications
            foreach($this->getService('config')->_APPLICATIONS as $strApp) {
                $strRoutesClass = "ovensia\\pf\\Applications\\{$strApp}\\Config\\Routes";
                $strRoutesClass::load($this);
            }
        }
        catch(Exception $e) {
            // Pas de fichier de routes, ou erreur dans les routes
            $e->show();
        }

        // Routes par défaut du framework

        $this

            // Route par défaut d'une action d'application
            ->addRoute('ofAction', Route\Route::getInstance(
                '/:application/:controller/:action',
                array(), array(
                    'application' => '\w[\w,]*',
                    'controller' => '\w[\w,]*'
                )
            ))

            // Route par défaut d'un contrôleur d'application
            ->addRoute('ofController', Route\Route::getInstance(
                '/:application/:controller',
                array(), array(
                    'application' => '\w[\w,]*',
                    'controller' => '\w[\w,]*'
                )
            ))

            // Route par défaut d'une application
            ->addRoute('ofApplication', Route\Route::getInstance(
                '/:application',
                array(), array(
                    'application' => '\w[\w,]*',
                )
            ))

        ;

        return $this;

    }

    public function addRoute($strRouteName, Route\Model $objRoute)
    {
        $this->_objRoutes->set($strRouteName, $objRoute);
        return $this;
    }

    public function getRoute($strRouteName)
    {
        if (!$this->_objRoutes->exists($strRouteName)) throw new Exception("Unknown route '{$strRouteName}'");

        return $this->_objRoutes->get($strRouteName);
    }

    /**
     * Analyse une url et retourne les paramètres trouvés si une route existe...
     * @param  $strPath
     * @return bool
     */
    public function route($strPath)
    {
        foreach($this->_objRoutes->getIterator() as $strRouteName => $objRoute)
        {
            if(($arrParams = $objRoute->check($strPath)) !== false)  {
                $this->_strRouteName = $strRouteName;
                return $arrParams;
            }
        }

        return false;
    }

    /**
     * Retourne la route utilisée
     * @return string
     */
    public function getRouteName()
    {
        return $this->_strRouteName;
    }

    /**
     * Construit une url selon une route prédéterminée
     * @throws Exception
     * @param string $strRouteName
     * @param array $arrVariables
     * @param array $arrParams
     * @param array $arrOptions
     * @return string
     */
    public function rewrite($strRouteName, $arrVariables = array(), $arrParams = array(), $arrOptions = array())
    {
        if (!$this->_objRoutes->exists($strRouteName)) throw new Exception("Unknown route '{$strRouteName}'");

        // Options par défaut
        $arrOptions = array_merge(array(
            'restful' => false,
            'urlify' => true
        ), $this->_objRoutes->get($strRouteName)->getOptions(), $arrOptions);

        // Nécessité de modifier le basepath par rapport à celui d'origine
        if (!empty($arrOptions['domain']) || !empty($arrOptions['scheme'])) {
            // Scheme
            $strBasePath = (empty($arrOptions['scheme']) ? $this->getService('kernel')->getScheme() : $arrOptions['scheme']).'://';
            // Domain
            $strBasePath .= empty($arrOptions['domain']) ? $this->getService('kernel')->getDomain() : $arrOptions['domain'];
        }
        else $strBasePath = $this->getService('kernel')->getBasePath();

        return $strBasePath.$this->_objRoutes->get($strRouteName)->rewrite($arrVariables, $arrParams, $arrOptions);
    }
}
