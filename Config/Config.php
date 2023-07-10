<?php
/*
    Copyright (c) 2014 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @package pf
 * @subpackage config
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Config;

use ovensia\pf\Core;

/**
 * Classe de chargement des paramètres de PLOOPI
 *
 * @package pf
 * @subpackage config
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class Config implements Core\Config\Model
{
    public static function load(Core\Config\Manager $objConfig)
    {

        // Environnement de développement
        /**
         * APP CONF
         */

        $objConfig

            /**
             * Liste des applications.
             * La première application est l'application par défaut (qui gèrera donc les erreurs, par exemple si aucune application demandée)
             */
            ->set('_APPLICATIONS', [])

        ;

        /**
         * DATABASE CONF
         */

        $objConfig

            /**
             * Type de SGBD utilisé. Seul MySQL est supporté pour le moment.
             */
            ->set('_SQL_LAYER', 'Mysqli') // Mysql, Mysqli

            /**
             * Le serveur de base de données.
             * Il peut aussi inclure le numéro de port.
             * C'est-à-dire "hostname:port" ou le chemin vers le socket local, c'est-à-dire ":/path/to/socket" pour localhost.
             */
            ->set('_DB_SERVER', 'localhost')

            /**
             * Le nom de l'utilisateur pour la connexion à la base de données
             */
            ->set('_DB_LOGIN', 'pf')

            /**
             * Le mot de l'utilisateur pour la connexion à la base de données
             */
            ->set('_DB_PASSWORD', 'pf')

            /**
             * Le nom de la base de données
             */
            ->set('_DB_DATABASE', 'pf')

        ;

        /**
         * MEMCACHED CONF
         */

        $objConfig

            /**
             * Le serveur memcached utilisé
             */
            ->set('_MEMCACHED_SERVER', 'localhost')

            /**
             * Le port utilisé pour la connexion au serveur memcached
             */
            ->set('_MEMCACHED_PORT', '11211')

        ;

        /**
         * SESSION CONF
         */

        $objConfig

            /**
             * Active ou non le gestionnaire interne de session utilisant la base de données.
             * Attention, l'activation de cette fonctionnalité peut dégrader les performances de l'application (impact faible)
             */
            ->set('_SESSION_LAYER', 'file') // 'db', 'file', 'memcached'


            /**
             * Durée maximum d'une session en secondes.
             * Si vous utilisez le gestionnaire de session interne de Ploopi, c'est la seule valeur prise en compte.
             * Si vous n'utilisez pas le gestionnaire interne, vérifiez la valeur de la directive session.gc_maxlifetime du fichier php.ini
             *
             * @link http://fr.php.net/manual/fr/session.configuration.php#ini.session.gc-maxlifetime
             */

            ->set('_SESSION_MAXLIFETIME', 3600) // time in second

            ->set('_SESSION_GC_PROBABILITY', 10)

            /**
             * Le serveur de base de données pour les sessions.
             * Il peut aussi inclure le numéro de port.
             * C'est-à-dire "hostname:port" ou le chemin vers le socket local, c'est-à-dire ":/path/to/socket" pour localhost.
             */

            ->set('_SESSION_DB_SERVER', $objConfig->get('_DB_SERVER'))

            /**
             * Le nom de l'utilisateur pour la connexion à la base de données des sessions
             */

            ->set('_SESSION_DB_LOGIN', $objConfig->get('_DB_LOGIN'))

            /**
             * Le mot de passe pour la connexion à la base de données des sessions
             */

            ->set('_SESSION_DB_PASSWORD', $objConfig->get('_DB_PASSWORD'))

            /**
             * Le nom de la base de données des sessions
             */

            ->set('_SESSION_DB_DATABASE', $objConfig->get('_DB_DATABASE'))
        ;

        /**
         * SECURITY CONF
         */
        $objConfig

            /**
             * Clé secrète utilisée pour le chiffrement des URL et des mots de passe
             */

            ->set('_SECRETKEY', 'ma phrase secrete')

            /**
             * Algo de hashage choisi
             * @see hash_algos
             */
            ->set('_HASH_ALGORITHM', 'tiger128,3') // md5, sha1, sha256, sha512, tiger128,3, tiger192,3, ripemd128, ripemd256, whirlpool ...

            /**
             * Algo de chiffrement choisi
             * @see mcrypt_list_algorithms
             */
            ->set('_CRYPT_ALGORITHM', 'rijndael-128') // rijndael-128 (AES), rijndael-256, cast-128, cast-256, serpent, twofish, tripledes ...

            /**
             * Empreinte de l'application
             */

            ->set('_FINGERPRINT', md5(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$objConfig->get('_DB_SERVER').'/'.$objConfig->get('_DB_SERVER')))
        ;

        /**
         * URL CONF
         */

        $objConfig

            /**
             * Active ou non le chiffrement des URLs
             */

            ->set('_URL_ENCODING', 'crypt') // 'hash', 'crypt', 'none'

            /**
             * Force l'utilisation des URLs chiffrées et interdit l'usage d'autres paramètres (concerne $_GET uniquement)
             *
             * @see _URL_ENCODING
             */

            ->set('_URL_FORCE_ENCODING', false)

            /**
             * Durée de vie maximale d'une url "hashé"
             *
             * @see _URL_ENCODING
             */

            ->set('_URL_HASH_LIFETIME', 3600) // time in second

        ;

        /**
         * DATA PATH CONF
         */

        $objConfig

            /**
             * Le chemin physique vers le dossier des données (fichiers, images, etc..)
             */

            ->set('_PATHDATA', Core\Service\Controller::getService('kernel')->getRealPath().'/Data')
            //->set('_PATHDATA', realpath('.').'/Data')

        ;

        /**
         * SUBPATHS CONF
         */

        $objConfig

            /**
             * Le chemin physique vers le dossier partagé (ftp)
             */

            ->set('_PATHSHARED', $objConfig->get('_PATHDATA') . '/Shared')

        ;

        /**
         * ERRORS CONF
         */

        $objConfig

            /**
             * Active ou non le gestionnaire interne d'erreur
             * Vérifiez devriez l'activer (sauf pour les tests unitaires)
             */

            ->set('_ERROR_HANDLER', true)

            /**
             * Active ou non l'affichage des erreurs (exceptions incluses)
             * Vérifiez également la valeur de la directive de configuration 'display_errors' du fichier php.ini
             * Vous pouvez également paramétrer le niveau de reporting d'erreur avec la constante _ERROR_REPORTING
             *
             * @see _ERROR_REPORTING
             *
             * @link http://fr.php.net/manual/fr/errorfunc.configuration.php#ini.display-errors
             */

            ->set('_ERROR_DISPLAY', true)

            /**
             * Définit le niveau de sensibilité du gestionnaire d'erreur.
             * Valeurs possibles : E_ALL, E_ERROR,  E_WARNING,  E_PARSE,  E_NOTICE
             *
             * @link http://fr.php.net/manual/fr/errorfunc.constants.php
             */

            ->set('_ERROR_REPORTING', E_ALL)


            // non géré '_ERROR_TO_EXCEPTION' => true,

           /**
             * Active ou on l'envoi d'un mail si une erreur est rencontrée.
             * Le mail envoyé est une synthèse de l'ensemble des erreurs rencontrées durant l'exécution de la page.
             * L'adresse du destinataire est définie par la constante _ADMINMAIL.
             *
             * @see _ADMINMAIL
             */

            ->set('_MAIL_ERRORS', false)

            /**
             * Adresse email de l'administrateur système du site.
             * Cette adresse est utilisée pour l'envoi des mails d'erreurs, mais également pour l'envoi de tout message système.
             *
             * @see _MAIL_ERRORS
             */

            ->set('_ADMINMAIL', 'admin@mail.net')

            /**
             * Fichier du log d'erreur
             * Vide pour désactiver
             */

            ->set('_ERROR_LOGFILE', $objConfig->get('_PATHDATA') . '/Log/error.log')

        ;

        /**
         * PROXY CONF
         */

        $objConfig

            /**
             * Nom d'hôte du serveur proxy pour l'accès internet
             */

            ->set('_INTERNETPROXY_HOST', '')

            /**
             * Port du serveur proxy pour l'accès internet
             */

            ->set('_INTERNETPROXY_PORT', '0')

            /**
             * Nom d'utilisateur du serveur proxy pour l'accès internet
             */

            ->set('_INTERNETPROXY_USER', '')

            /**
             * Mot de passe du serveur proxy pour l'accès internet
             */

            ->set('_INTERNETPROXY_PASS', '')

        ;

        /**
         * OUTPUT CONF
         */

        $objConfig

            /**
             * Permet d'activer le buffer de sortie
             */

            ->set('_OUTPUT_BUFFERIZE', true)

            /**
             * Permet d'activer la compression gzip du buffer de sortie
             */

            ->set('_OUTPUT_COMPRESS', true)

            /**
             * Permet d'activer la minification HTML
             * Surtout ne pas activer avec les formulaires de validation de paiement type Systempay
             */

            ->set('_HTML_MINIFY', false)
        ;



        /**
         * CACHE
         */

        $objConfig

            /**
             * Moteur de cache activé ?
             */

            ->set('_CACHE_USE', true)

            /**
             * Le chemin physique vers le dossier des données du cache
             */

            ->set('_CACHE_PATH', $objConfig->get('_PATHDATA') . '/Cache')

            /**
             * Durée de vie du cache par défaut
             */

            ->set('_CACHE_DEFAULT_LIFETIME', 3600)

        ;



        /**
         * MISC
         */

        $objConfig

            /**
             * Probabilité d'exécution du garbage collector (de 0 à 1)
             */
            ->set('_GC_PROBABILITY', 0.1)



            /**
             * Chemin absolu vers le dossier contenant les librairies PEAR
             */

            ->set('_PEARPATH', '/usr/share/php')

        ;

        /**
         * DEBUG
         */

        $objConfig

            ->set('_DEBUG', false)

        ;


    }
}
