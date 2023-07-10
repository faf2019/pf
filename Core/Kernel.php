<?php
namespace ovensia\pf\Core;

use ovensia\pf as home;

class Kernel
{
    use Builders\Singleton;
    use Service\Controller;

    const _VERSION_MAJOR = '0';
    const _VERSION_MINOR = '1';
    const _VERSION_UPDATE = '0';

    private $_strSelfPath;
    private $_strRealPath;
    private $_strBasePath;
    private $_strScheme;
    private $_strDomain;
    private $_strOsType;
    private $_arrExecStats;

    protected function __construct()
    {

        $this->_arrExecStats = array(
            'numqueries' => 0,
            'sql_exectime' => 0,
            'total_exectime' => 0,
            'sql_ratiotime' => 0,
            'php_ratiotime' => 0,
            'php_memory' => 0,
            'sessionsize' => 0,
            'time' => 0
        );

        // Initialisation de quelques variables
        $this->_strOsType = substr(PHP_OS, 0, 3) == 'WIN' ? 'windows' : 'unix';
        $this->_strRealPath = realpath('.');

        if (php_sapi_name() != 'cli')
        {
            $this->_strSelfPath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $this->_strScheme = (!empty($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_SSL_REQUEST']) && ($_SERVER['HTTP_X_SSL_REQUEST'] == 1 || $_SERVER['HTTP_X_SSL_REQUEST'] == true || $_SERVER['HTTP_X_SSL_REQUEST'] == 'on'))) ? 'https' : 'http';
            $this->_strDomain = ((!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']).((!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80' && empty($_SERVER['HTTP_HOST'])) ? ":{$_SERVER['SERVER_PORT']}" : '');
            $this->_strBasePath = $this->_strScheme.'://'.$this->_strDomain.rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        }
        else
        {
            $this->_strSelfPath = '';
            $this->_strScheme = '';
            $this->_strDomain = '';
            $this->_strBasePath = '';
        }

    }

    public function usePear()
    {
        // Prise en compte de PEAR dans le include path
        if ((strstr(ini_get('include_path'), $this->getService('config')->_PEARPATH) == false) && file_exists($this->getService('config')->_PEARPATH)) ini_set('include_path', ini_get('include_path').($this->_strOsType == 'windows' ? ';' : ':').$this->getService('config')->_PEARPATH);
    }

    public function getVersion($strFormat = 'string')
    {
        return $strFormat == 'string' ? sprintf("%s.%s.%s", self::_VERSION_MAJOR, self::_VERSION_MINOR, self::_VERSION_UPDATE) : array(self::_VERSION_MAJOR, self::_VERSION_MINOR, self::_VERSION_UPDATE);
    }

    /**
     * Retourne le numéro de révision du portail
     * Nécessite que le fichier ./revision.txt soit mis à jour avec la commande svnversion > revision.txt
     *
     * @return string
     */
    public function getRevision()
    {
        $strFileName = home\DIRNAME.'/revision.txt';
        return file_exists($strFileName) ? trim(utf8_decode(file_get_contents($strFileName))) : null;
    }

    public function getRealPath() { return $this->_strRealPath; }

    public function getSelfPath() { return $this->_strSelfPath; }

    public function getBasePath() { return $this->_strBasePath; }

    public function getScheme() { return $this->_strScheme; }

    public function getDomain() { return $this->_strDomain; }

    public function getOsType() { return $this->_strOsType; }


    /**
     * Déconnecte l'utilisateur, nettoie la session et renvoie éventuellement un code d'erreur
     *
     * @param int $errorcode code d'erreur
     * @param int $sleep durée d'attente avant la redirection en seconde
     */
    public function _____deprecated_logout($intErrorCode = null, $intSleep = 1, $booRedirect = true)
    {
       // Suppression de l'information de connexion
        Entities\ConnectedUser::deleteFromKey($this->getService('session')->getId());

        // Destruction de la session (elle est enregistrée plus tard)
        $this->getService('session')->destroyId();

        if ($intSleep > 0) sleep($intSleep);

        // Execute l'action de logout
        $this->getService('response')->redirect($this->getService('router')->rewrite('plIndex'), true)->printOut();

        // Préparation de l'url de redirection
        /*
        require_once 'Net/URL.php';
        if ($booRedirect && isset($_SERVER['HTTP_REFERER']))
        {
            $objUrl = new Net_URL($_SERVER['HTTP_REFERER']);
            if (isset($intErrorCode)) $objUrl->addQueryString('ploopiErrorCode', $intErrorCode);
            Output::redirect($objUrl->getURL(), false, false);
        }
        else
        {
            Output::redirect(basename(ploopiInput::getUrlPath()).(isset($intErrorCode) ? "?ploopiErrorCode={$intErrorCode}" : ''), false);
        }
        */
    }



    /**
     * Affiche un message et termine le script courant.
     * Peut envoyer un mail contenant les erreurs rencontrées durant l'exécution du script.
     * Peut vider le buffer en cours.
     * Ferme la session en cours.
     * Ferme la connexion à la base de données (si ouverte).
     *
     * @param mixed $var variable à afficher
     * @param boolean $flush true si la sortie doit être vidée (true par défaut)
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see die
     * @see ploopi_print_r
     */

    public function kill($mixVar = null)
    {

        /*
        try {
            if (!empty($this->intErrorLevel) && $this->intErrorLevel && $this->getService('config')->get('_MAIL_ERRORS') && $this->getService('config')->get('_ADMINMAIL') != '')
            {
                $strErrorType = $this->arrErrorLevel[$this->intErrorLevel];

                mail(
                    $this->getService('config')->get('_ADMINMAIL'),
                    "{$strErrorType} sur [{$_SERVER['HTTP_HOST']}]",
                    "{$this->intErrorNb} erreur(s) sur {$this->strErrorsMsg}".
                    "\n_SERVER:\n".print_r($_SERVER, true).
                    "\n_POST:\n".print_r($_POST, true).
                    "\n_GET:\n".print_r($_GET, true)
                    // ."\n_SESSION:\n".print_r($_SESSION, true)
                );
            }

            if (!is_null($mixVar)) Output::printR($mixVar);

            // Actualisation des stats d'exécution
            $this->getExecStats(true);

            ploopiLog::write();

            session_write_close();

            if (is_object($this->getDb())) $this->getDb()->close();

            // Appel implicite de la méthode Output::bufferCallback($strBuffer);
            while (ob_get_level()) ob_end_flush();
        }
        catch (Core\ploopiException $objException)
        {
            echo $objException->show();
        }
        */

        if (!is_null($mixVar)) Core\Output::printR($mixVar);

        // attention pose problème si session non créée
        // Ploopi::Kernel()->getExecStats();

        // Vidage de tous les buffers ouverts
        // Appel implicite de la méthode ploopiBuffer::cbBuffer($strBuffer);
        while (ob_get_level()) ob_end_flush();
        // Fin du script
        die();
    }

    /*
    public function temp()
    {
        // Suppression de l'information de connexion
        ploopiConnectedUser::deleteFromKey(Ploopi::Session()->getId());

        Ploopi::Session()->destroyId();

        if ($intSleep > 0) sleep($intSleep);

        // Préparation de l'url de redirection
        require_once 'Net/URL.php';
        if ($booRedirect && isset($_SERVER['HTTP_REFERER']))
        {
            $objUrl = new Net_URL($_SERVER['HTTP_REFERER']);
            if (isset($intErrorCode)) $objUrl->addQueryString('ploopiErrorCode', $intErrorCode);
            Output::redirect($objUrl->getURL(), false, false);
        }
        else
        {
            Output::redirect(basename(ploopiInput::getUrlPath()).(isset($intErrorCode) ? "?ploopiErrorCode={$intErrorCode}" : ''), false);
        }

    }


    /**
     * Retourne le tableau de variables des statistiques d'exécution
     * @param bool $booForceRefresh force la mise à jour des variables
     * @return array
     */
    public function getExecStats($booForceRefresh = false)
    {
        if ($booForceRefresh || empty($this->_arrExecStats['time']))
        {
            $this->_arrExecStats['time'] = time();
            $this->_arrExecStats['total_exectime'] = round(Service\Controller::getService('timer')->getExectime() * 1000, 0);
            $this->_arrExecStats['php_memory'] = memory_get_peak_usage();

            // Session nécessitant une session
            try {
                $this->_arrExecStats['sessionsize'] = Service\Controller::getService('session')->getSize();
            }
            catch (Exception $e) { }

            $this->_arrExecStats['pagesize'] = ob_get_length();

            // Stats nécessitant une connexion à la BDD
            try {
                $objDb = Service\Controller::getService('db');
                if (is_object($objDb) && $objDb->isConnected())
                {
                    $this->_arrExecStats['numqueries'] = $objDb->getNumQueries();
                    $this->_arrExecStats['sql_exectime'] = round($objDb->getExectimeQueries() * 1000, 0);

                    $this->_arrExecStats['sql_ratiotime'] = round(($this->_arrExecStats['sql_exectime'] * 100) / $this->_arrExecStats['total_exectime'], 0);
                    $this->_arrExecStats['php_ratiotime'] = 100 - $this->_arrExecStats['sql_ratiotime'];
                }
            }
            catch (Exception $e) { }
        }

        return $this->_arrExecStats;
    }

}
