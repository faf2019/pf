<?php
/**
 * Gestion des fichiers de log
 *
 * @package pf
 * @subpackage log
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core;

use ovensia\pf\Core\Tools;

/**
 * Classe de gestion des fichiers de log
 *
 * @package pf
 * @subpackage log
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class LogFile
{
    use Builders\Factory;

    private $_ptrFileHandle;

    /**
     * Constructeur de la classe
     *
     * @return log
     */

    public function __construct($strFilePath)
    {
        $this->_ptrFileHandle = false;

        if (!empty($strFilePath))
        {
            $strDirName = dirname($strFilePath);

            if (!file_exists($strDirName))
                if (!mkdir($strDirName, 0700, true)) throw new Exception("Can't create log path '{$strDirName}'");

            if (file_exists($strFilePath)) if (!is_writable($strFilePath)) throw new Exception("Can't write into log file '{$strFilePath}'");
            else if (!is_writable($strDirName)) throw new Exception("Can't write into log path '{$strDirName}'");

            $this->_ptrFileHandle = fopen($strFilePath, 'a');

            if ($this->_ptrFileHandle === false) throw new Exception("Can't open log file '{$strFilePath}'");
        }
    }

    protected function isWritable()
    {
        return $this->_ptrFileHandle !== false && is_resource($this->_ptrFileHandle);
    }

    public function write($strMessage)
    {
        if ($this->isWritable())
        {
            fwrite($this->_ptrFileHandle, '## '. Tools\Timestamp::getInstance()."\n{$strMessage}\n");
        }
    }

    public function __destruct() { if ($this->isWritable()) fclose($this->_ptrFileHandle); }
}
