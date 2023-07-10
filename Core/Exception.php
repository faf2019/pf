<?php
namespace ovensia\pf\Core;

class Exception extends \Exception
{
    private $_arrTrace;

    /**
    * Constructeur
    */
    public function __construct($strMsg, $intCode = E_USER_ERROR, $strFile = null, $intLine = null, $arrContext = null, $booIsError = false)
    {
        //echo "<br />".$strMsg;
        //var_dump($this->getTrace());

        // Récupération du trace
        $this->_arrTrace = $this->getTrace();

        // Appel direct, on ajoute la dernière erreur à la pile d'erreur

        if ($arrContext == null) $this->_arrTrace = array_merge(array(array('file' => $this->file, 'line' => $this->line)), $this->_arrTrace);

        if (!is_null($strFile)) $this->file = $strFile;
        if (!is_null($intLine)) $this->line = $intLine;

        parent::__construct($strMsg, $intCode);
    }

    /**
     * Affichage de l'erreur
     *
     * @param boolean $booKill true si le script doit être arrêté
     */

    public function show($booKill = false)
    {
        // Affichage / log
        $objErrorMessage = ErrorMessage::getInstance($this->code, $this->message, $this->_arrTrace)
            ->writeLog()
            ->show();

        // Critical error or kill
        // if ($this->code == E_ERROR || $this->code == E_PARSE || $this->code == E_USER_ERROR || $booKill) Ploopi::Kernel()->kill();
        if ($booKill) {
            // @todo vérifier l'existence du service
            Service\Controller::getService('kernel')->kill();
        }

        /*
        ploopiErrorHandler::writeLog($this->code, $this->message, $this->_arrTrace);

        ploopiErrorHandler::show($this->code, $this->message, $this->_arrTrace);

        // critical error or kill
        if ($this->code == E_ERROR || $this->code == E_PARSE || $this->code == E_USER_ERROR || $booKill) ploopiKernel::kill();
        */
    }

    public function getContent()
    {
        ob_start();
        $objErrorMessage = ErrorMessage::getInstance($this->code, $this->message, $this->_arrTrace)
            ->show(true);
        $strContent = ob_get_contents();
        ob_end_clean();

        return $strContent;
    }
}
