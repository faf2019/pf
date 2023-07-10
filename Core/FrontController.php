<?php
/*
 * Classe ploopiFrontController
 * Initialisation les élément principaux du "noyau"
 * - Chargement du fichier de config
 * - Gestion du buffer de sortie
 * - Gestion des erreurs/exceptions
 * - Connexion à la base de données
 * - Gestion de la session
 * - Filtrage des donnée en entrée (Get,Post,Cookie)
 *
 *
 * @todo Gérer le rewrite, Gérer les constantes (?), les langues (?), le cache
 */


/**
 * Front Controller
 * Point d'entrée unique de l'application
 * Démarre les services
 * Gère la requête entrante
 *
 * @package Ploopi2
 * @subpackage FrontController
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core;

use ovensia\pf\Core\Service\Definition;

/**
 * Classe FrontController
 *
 * @package Ploopi2
 * @subpackage FrontController
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

final class FrontController
{
    use Builders\Singleton;
    use Service\Controller;

    /**
     * Définition et démarrage des services
     */

    protected function __construct()
    {
        try {
            /**
             * @TODO : uniformation des services, simplification des définitions
             */
            mb_internal_encoding("UTF-8");


            // Définition des services
            $this->register('timer', new Definition(__NAMESPACE__.'\\Tools\\Timer', array(), 'getInstance', array(array('method' => 'start'))));
            $this->register('config', new Definition(__NAMESPACE__.'\\Config\\Manager', array(), 'getInstance', array(array('method' => 'start'))));
            $this->register('buffer', new Definition(__NAMESPACE__.'\\Buffer', array(), 'getInstance', array(array('method' => 'start'))));
            $this->register('cache', new Definition(__NAMESPACE__.'\\Cache', array(), 'getInstance', array(array('method' => 'start'))));
            $this->register('kernel', new Definition(__NAMESPACE__.'\\Kernel'));
            $this->register('error_handler', new Definition(__NAMESPACE__.'\\ErrorHandler', array(), 'getInstance', array(array('method' => 'start'))));
            // $this->register('garbage_collector', new Definition(__NAMESPACE__.'\\GarbageCollector', array(), 'getInstance', array(array('method' => 'start'))));
            $this->register('router', new Definition(__NAMESPACE__.'\\Router\\Router', array(), 'getInstance', array(array('method' => 'start'))));
            $this->register('request', new Definition(__NAMESPACE__.'\\Request', array(), 'getInstance', array(array('method' => 'start'))));
            $this->register('response', new Definition(__NAMESPACE__.'\\Response', array(), 'getInstance', array(array('method' => 'start'))));
            $this->register('db', new Definition(__NAMESPACE__.'\\Initializers\Db'));
            $this->register('session', new Definition(__NAMESPACE__.'\\Initializers\Session'));

            // Instanciation et Démarrage des services primaires
            $this->getService('timer');
            $this->getService('error_handler');
            $this->getService('buffer');

            // Contrôle de validité
            // Mise à jour des données de base
            // Initialise la session au premier appel ou lors de la connexion d'un utilisateur
            // $this->getService('session')->control()->init();

        } catch (Exception $e) {
            // Affichage exception et STOP
            // On ne tolère pas d'exception ici,
            // donc on s'arrête si problème
            $e->show(true);
        }

        return $this;
    }


    /**
     * Dispatcher :
     * Analyse de la requête entrante
     * Appel du contrôleur d'action
     * Exécution du contrôleur principal
     * Appel du moteur de rendu
     * @return void
     */
    public function dispatch()
    {
        // Analyse de la requête entrante
        // Tentative de détection d'une route valide
        // Lecture des paramètres définis par la route (workspace, module, controller, action, etc...)
        $this->getService('request')->route();

        $this->getService('response')->setBody(
            Controller\Application::process()->render()
        )->printOut();

        // On tue le script (Argh)
        $this->getService('kernel')->kill();
    }


    /**
     * Destructeur. On détruit notamment la session et la connexion à la base de données.
     */
    public function __destruct()
    {
        // Ecriture et fermeture de la session
        if ($this->serviceExists('session')) $this->getService('session')->writeClose();

        // Fermeture connexion BDD
        if ($this->serviceExists('db')) $this->getService('db')->close();
    }

}
