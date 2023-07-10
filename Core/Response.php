<?php
/**
 * Gestion de la réponse envoyée au client
 * Gestion des headers
 * Gestion du corps de page
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core;

/**
 * Classe FrontController
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Response extends Service\Common
{
    /**
     * @var ArrayObject
     */
    private $_arrHeaders = null;

    private $_booRedirect;

    /**
     * @var string
     */
    private $_strBody;

    protected function __construct()
    {
        parent::__construct();
        $this->_arrHeaders = array();
        $this->_strBody = '';
        $this->_booRedirect = false;
    }


    public function start()
    {
        parent::start();

        return $this
            ->setHeader('Expires: Sat, 1 Jan 2000 05:00:00 GMT')
            ->setHeader('Last-Modified: '.gmdate("D, d M Y H:i:s"))
            // HTTP/1.1
            ->setHeader('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0')
            // HTTP/1.0
            ->setHeader('Pragma: no-cache')
            // On génère un Etag unique
            ->setHeader('Etag: '.microtime())
            ->setHeader('Accept-Ranges: bytes')
            // Contenu par défaut
            ->setHeader('Content-type: text/html; charset=utf-8')
            // On cache la version de php
            ->setHeader('X-powered-By: pf/1.0');
    }

    public function getHeaders()
    {
        return $this->_arrHeaders;
    }

    public function removeHeader($strKey)
    {
        foreach($this->_arrHeaders as $k => $row) {
            if (strpos($row[0], $strKey) !== false) {
                unset($this->_arrHeaders[$k]);
            }
        }

        return $this;
    }

    public function getRedirect()
    {
        return $this->_booRedirect;
    }

    public function setHeader($strValue, $booReplace = true, $intResponseCode = null)
    {
        $this->_arrHeaders[] = array($strValue, $booReplace, $intResponseCode);

        return $this;
    }

    public function setErrorHeader($intErrorCode)
    {
        switch($intErrorCode) {
            case 301:
                $this->setHeader('HTTP/1.1 301 Moved Permanently');
            break;

            case 302:
                $this->setHeader('HTTP/1.1 302 Found');
            break;

            case 304:
                $this->setHeader('HTTP/1.1 304 Not Modified');
            break;

            case 403:
                $this->setHeader('HTTP/1.1 403 Forbidden');

            case 404:
                $this->setHeader('HTTP/1.1 404 Not Found');
            break;

            case 500:
                $this->setHeader('HTTP/1.1 500 Internal Server Error');
            break;
        }

        return $this;
    }

    public function setBody($strContent)
    {
        $this->_strBody .= $strContent;
        return $this;
    }

    public function redirect($strLocation = '', $intErrorCode = 302) //, $booKill = true)
    {
        if (empty($strLocation)) {
            foreach($this->_arrHeaders as $k => $v) {
                if (strpos($k, 'Location:') === 0) unset($this->_arrHeaders[$k]);
            }
            $this->_booRedirect = false;
        }
        else {
            $this->setHeader('Location: '.$strLocation, true, $intErrorCode);
            $this->_booRedirect = true;
        }

        //if ($booKill) $this->printOut();

        return $this;
    }

    public function printOut()
    {
        // Header
        foreach ($this->_arrHeaders as $row) header($row[0], $row[1], $row[2]);

        // Body
        if (!$this->_booRedirect) echo $this->_strBody;
    }
}
