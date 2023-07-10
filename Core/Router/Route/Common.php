<?php
namespace ovensia\pf\Core\Router\Route;

use ovensia\pf\Core\Builders\Factory;
use ovensia\pf\Core\Exception;

abstract class Common
{
    use Factory;

    const VARIABLE_MOTIF = '[a-z_][a-zA-Z0-9_]*';
    const VALUE_MOTIF = '[^/]+';

    private $_arrStaticVariables;
    private $_strPattern;
    private $_arrFormats;
    private $_arrOptions;
    private $_arrReadParams;

    /**
     * Constructeur d'une route
     * Options : scheme (http, https...), domain
     */
    final public function __construct($strPattern, $arrStaticVariables = array(), $arrFormats = array(), $arrOptions = array())
    {
        $this->_strPattern = $strPattern;
        $this->_arrStaticVariables = $arrStaticVariables;
        $this->_arrFormats = $arrFormats;
        $this->_arrOptions = $arrOptions;
        $this->_arrReadParams = array();
    }

    final public function getPattern() { return $this->_strPattern; }

    final public function getFormats() { return $this->_arrFormats; }

    final public function getStaticVariables() { return $this->_arrStaticVariables; }

    final public function getOptions() { return $this->_arrOptions; }

    final public function getVariables() {
        // Recherche des variables dans le pattern
        preg_match_all('#:('.self::VARIABLE_MOTIF.')#', $this->getPattern(), $arrPatternVars);
        $arrPatternVars = isset($arrPatternVars[1]) ? $arrPatternVars[1] : array();

        return $arrPatternVars;
    }

    final public function getReadParams() { return $this->_arrReadParams; }

    protected function _setReadParam($strParam, $strValue) {
        $this->_arrReadParams[$strParam] = $strValue;
    }

    protected function _addParams($arrParams = array(), $booRestful = false)
    {
        if (empty($arrParams)) return '';

        if (!is_array($arrParams)) throw new Exception('Wrong type for $arrParams');

        $strParams = '';

        if ($booRestful) {
            // Ajout des paramètres optionnels
            foreach($arrParams as $strKey => $strValue) $strParams .= '/'.urlencode($strKey).'-'.urlencode($strValue);
        }
        else {
            if (!empty($arrParams)) {
                // Ajout des paramètres optionnels
                foreach($arrParams as $strKey => $strValue) {
                    $strParams .= $strParams == '' ? '?' : '&';
                    $strParams .= urlencode($strKey).'='.urlencode($strValue);
                }
            }
        }

        return $strParams;
    }

}
