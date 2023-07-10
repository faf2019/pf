<?php
namespace ovensia\pf\Core;

/**
 * Gestion des sorties d'erreur (affichage/stockage fichier)
 * @author ovensia
 *
 */

class ErrorMessage
{
    use Builders\Factory;
    use Service\Controller;

    const _NOWORKSPACEDEFINED           = 1;
    const _LOGINERROR                   = 2;
    const _LOGINEXPIRE                  = 3;
    const _SESSIONEXPIRE                = 4;
    const _SESSIONINVALID               = 5;
    const _LOSTPASSWORD_UNKNOWN         = 11;
    const _LOSTPASSWORD_INVALID         = 12;
    const _LOSTPASSWORD_MANYRESPONSES   = 13;

    /**
     * @var array
     */
    private static $_arrErrorType =
        array(
            E_ERROR          => 'Fatal Error',
            E_WARNING        => 'Warning',
            E_PARSE          => 'Parse Error',
            E_NOTICE         => 'Notice',
            E_DEPRECATED     => 'Deprecated',
            E_CORE_ERROR     => 'Core Error',
            E_CORE_WARNING   => 'Core Warning',
            E_COMPILE_ERROR  => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR     => 'User Error',
            E_USER_WARNING   => 'User Warning',
            E_USER_NOTICE    => 'User Notice',
            E_USER_DEPRECATED => 'User Deprecated',
            E_STRICT         => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error'
        );

    private static $_intErrorNb = 0;

    private static $_intErrorLevel = 0;

    /**
     * @var ploopiLogFile
     */
    private static $_objErrorLog;

    private $_intCode;

    private $_strMsg;

    private $_arrTrace;

    /**
     *
     * @param integer $this->_intCode code de l'erreur
     * @param string $strMsg message d'erreur
     * @param array $arrTrace historique de l'erreur
     */
    public function __construct($intCode, $strMsg, $arrTrace)
    {
        $this->_intCode = $intCode;
        $this->_strMsg = $strMsg;
        $this->_arrTrace = is_array($arrTrace) ? $arrTrace : array();
    }

    /**
     * Affiche un erreur (HTML)
     *
     */
    public function show($booForce = false)
    {
        // Compteur global du nombre d'erreurs
        self::$_intErrorNb++;

        // Niveau d'erreur global rencontré dans le script
        self::$_intErrorLevel = ($this->_intCode == E_ERROR || $this->_intCode == E_PARSE || $this->_intCode == E_USER_ERROR) ? max(self::$_intErrorLevel, 2) : max(self::$_intErrorLevel, 1);

        if ($booForce || $this->getService('config')->get('_ERROR_DISPLAY'))
        {
            $strMessage = '';

            foreach($this->_arrTrace as $intKey => $arrTraceInfo)
            {
                if (!empty($arrTraceInfo['file']) && !empty($arrTraceInfo['line']))
                {
                    if ($intKey == 0)
                    {
                        $arrTraceInfo['origin'] = isset($arrTraceInfo['args'][1]) ? sprintf(" with %s", $arrTraceInfo['args'][1]) : '';
                    }
                    else
                    {
                        $arrTraceInfo['origin'] = isset($arrTraceInfo['function']) ? sprintf(" with %s%s%s()",
                            isset($arrTraceInfo['class']) ? $arrTraceInfo['class'] : '',
                            isset($arrTraceInfo['type']) ? $arrTraceInfo['type'] : '',
                            $arrTraceInfo['function']
                        ) : '';
                    }

                    if (php_sapi_name() != 'cli') $strMessage .= sprintf("<div style=\"margin-left:10px;\">at <strong>%s</strong>  <em>line %d</em>%s</div>", $arrTraceInfo['file'],  $arrTraceInfo['line'], isset($arrTraceInfo['origin']) ? $arrTraceInfo['origin'] : '');
                    else $strMessage .= sprintf("at %s line %d %s\n", $arrTraceInfo['file'],  $arrTraceInfo['line'], isset($arrTraceInfo['origin']) ? $arrTraceInfo['origin'] : '');
                }
            }


            // display message
            if (php_sapi_name() != 'cli')  // Affichage standard, sortie HTML
            {
                $strMsg = Tools\Ustring::getInstance($this->_strMsg)->nl2br()->getString();

                echo "
                    <div style=\"background-color:#ffff60; border:1px dotted #a60000; color:#a60000; padding:4px 10px; margin:10px; font-family:Courier, monospace; \">
                        <div>
                        <strong>".self::$_arrErrorType[$this->_intCode]."</strong> - <span>{$strMsg}</span>
                        </div>
                        {$strMessage}
                    </div>
                ";
            }
            else // Affichage cli, sortie texte brut
            {
                echo "=== ".self::$_arrErrorType[$this->_intCode]." - ".strip_tags($strMsg)."\r{$strMessage}";
            }
        }

        return $this;
    }

    /**
     * Ecriture dans le fichier de log
     *
     * @param integer $this->_intCode code de l'erreur
     * @param string $strMsg message d'erreur
     * @param array $arrTrace historique de l'erreur
     */
    public function writeLog()
    {
        if ($this->getService('config')->_ERROR_LOGFILE != '')
        {
            $strMessage = self::$_arrErrorType[$this->_intCode]." - ".strip_tags($this->_strMsg)."\n";

            foreach($this->_arrTrace as $intKey => $arrTraceInfo)
            {
                if (!empty($arrTraceInfo['file']) && !empty($arrTraceInfo['line']))
                {
                    if ($intKey == 0)
                    {
                        $arrTraceInfo['origin'] = isset($arrTraceInfo['args'][1]) ? sprintf(" with %s", $arrTraceInfo['args'][1]) : '';
                    }
                    else
                    {
                        $arrTraceInfo['origin'] = isset($arrTraceInfo['function']) ? sprintf(" with %s%s%s()",
                            isset($arrTraceInfo['class']) ? $arrTraceInfo['class'] : '',
                            isset($arrTraceInfo['type']) ? $arrTraceInfo['type'] : '',
                            $arrTraceInfo['function']
                        ) : '';
                    }

                    $arrTraceInfo['message'] = sprintf("at %s line %d%s", $arrTraceInfo['file'],  $arrTraceInfo['line'], isset($arrTraceInfo['origin']) ? $arrTraceInfo['origin'] : '');
                    $strMessage .= $arrTraceInfo['message']."\n";
                }
            }

            if (!(self::$_objErrorLog instanceof LogFile)) self::$_objErrorLog = LogFile::getInstance($this->getService('config')->_ERROR_LOGFILE);
            self::$_objErrorLog->write($strMessage);
        }

        return $this;
    }
}
