<?php
/*
    Copyright (c) 2007-2010 Ovensia
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
 * Gestion du buffer
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core;

use zz\Html\HTMLMinify;

/**
 * Classe de gestion du buffer
 *
 * @package Ploopi2
 * @subpackage Service
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Buffer extends Service\Common
{
    public function start()
    {
        if ($this->getService('config')->_OUTPUT_BUFFERIZE)
        {
            parent::start();
            ob_start(array($this, 'cbBuffer'));
        }

        return $this;
    }

    /**
     * Gère la sortie du buffer principal.
     * Met à jour le rendu final en mettant à jour les variables d'éxection.
     * Compresse éventuellement le contenu.
     * Ecrit dans le log.
     *
     * @param string $strBuffer contenu du buffer de sortie
     * @return string buffer modifié
     *
     * @see _OUTPUT_COMPRESS
     * @see ob_start
     */

    public function cbBuffer($strBuffer)
    {

        // On essaye de récupérer le content-type du contenu du buffer
        $strContentType = 'text/html';
        $arrHeaders = headers_list();
        $booDownloadFile = false;

        foreach($arrHeaders as $strProperty)
        {
            $matches = array();

            if (preg_match('/Content-type:((.*);(.*)|(.*))/i', $strProperty, $arrMatches))
            {
                $strContentType = (empty($arrMatches[2])) ? $arrMatches[1] : $arrMatches[2];
                $strContentType = strtolower(trim($strContentType));
            }

            if (preg_match('/X-Ploopi:(.*)/i', $strProperty, $arrMatches))
            {
                if (isset($arrMatches[1]))
                {
                    switch(trim($arrMatches[1]))
                    {
                        case 'Download': $booDownloadFile = true; break;
                    }
                }
            }
        }

        // Traitement des balises spéciales dans le rendu HTML, permet d'afficher les stats d'exécution après le rendu global
        if ($strContentType == 'text/html' && !$booDownloadFile)
        {
            if ($this->getService('config')->_HTML_MINIFY) {
                $strBuffer = preg_replace(
                    array(
                        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
                        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
                        '/(\s)+/s',         // shorten multiple whitespace sequences
                        '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s' // '/<!--(?!\[if).*?-->/' // Remove HTML comments
                    ),
                    array(
                        '>',
                        '<',
                        '\\1',
                        ''
                    ),
                    $strBuffer
                );
            }


            $arrExecStats = $this->getService('kernel')->getExecStats();

            $strBuffer = trim(str_replace(
                array(
                    '{PLOOPI_PAGE_SIZE}',
                    '{PLOOPI_EXEC_TIME}',
                    '{PLOOPI_PHP_P100}',
                    '{PLOOPI_SQL_P100}',
                    '{PLOOPI_NUMQUERIES}',
                    '{PLOOPI_SESSION_SIZE}',
                    '{PLOOPI_PHP_MEMORY}',
                    '{PLOOPI_DEBUG}',
                    '{PLOOPI_DEBUG_CONTENT}'
                ),
                array(
                    sprintf("%.02f", strlen($strBuffer)/1024),
                    $arrExecStats['total_exectime'],
                    $arrExecStats['php_ratiotime'],
                    $arrExecStats['sql_ratiotime'],
                    $arrExecStats['numqueries'],
                    sprintf("%.02f", $arrExecStats['sessionsize']/1024),
                    sprintf("%.02f", $arrExecStats['php_memory']/1024),
                    $this->getService('config')->_DEBUG ? 'on' : 'off'
                ),
                $strBuffer
            ));
        }

        //$strBuffer.='<br />'.htmlentities('<< END >>');

        if (!$booDownloadFile && $this->getService('config')->_OUTPUT_COMPRESS && $this->_acceptsGzip() && ($strContentType == 'text/plain' || $strContentType == 'text/html' || $strContentType == 'text/xml' || $strContentType == 'text/x-json'))
        {
            header("Content-Encoding: gzip");
            $strBuffer = gzencode($strBuffer);
        }
        else
        {
            // Attention, Content-Encoding: none ET Content-Type: text/html ne font pas bon ménage !
            // => Problème avec le validateur W3C : Line 1, Column 0: end of document in prolog
            if ($strContentType != 'text/html') header("Content-Encoding: none");
        }

        header('Content-Length: '.strlen($strBuffer));

        return $strBuffer;
    }

    /**
     * Détecte si le navigateur supporte la compression gzip
     *
     * @return boolean true si le navigateur supporte la compression gzip
     *
     * @copyright tellinya.com
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @link http://www.tellinya.com/read/2007/09/09/106.html
     */

    private function _acceptsGzip()
    {
        return isset($_SERVER['HTTP_ACCEPT_ENCODING']) && in_array('gzip', explode(',', str_replace(' ', '', strtolower($_SERVER['HTTP_ACCEPT_ENCODING']))));
    }
}
