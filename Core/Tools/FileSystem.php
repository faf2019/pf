<?php
/*
    Copyright (c) 2010 Ovensia
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

namespace ovensia\pf\Core\Tools;

/**
 * Méthodes d'accès à l'espace physique de stockage.
 * Création de dossier, copie de fichiers, téléchargement de fichiers...
 *
 * @package pf
 * @subpackage filesystem
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class FileSystem
{
    /**
     * Copie récursive du contenu d'un dossier source vers un dossier destination
     *
     * @param string $src dossier source
     * @param string $dest dossier destination
     * @param string $folder_mode mode attribué aux dossiers
     * @param string $file_mode mode attribué aux fichiers
     * @return boolean true si pas de problème de copie
     */

    function ploopi_copydir($src , $dest, $folder_mode = 0750, $file_mode = 0640)
    {
        $ok = true;

        if (_SERVER_OSTYPE == 'unix') $processid = posix_getuid();

        $folder = opendir($src);

        if (!file_exists($dest)) mkdir($dest, $folder_mode);

        while ($file = readdir($folder))
        {
            if (!in_array($file, array('.', '..')))
            {
                $src_file = "{$src}/{$file}";
                $dest_file = "{$dest}/{$file}";

                if (is_dir($src_file))
                {
                    $ok = ploopi_copydir($src_file, $dest_file, $folder_mode = 0750, $file_mode = 0640);
                }
                else
                {
                    // test if writable
                    if (!(file_exists($dest_file) && !is_writable($dest_file)))
                    {
                        copy($src_file, $dest_file);

                        // changement des droits uniquement le processus courant est propriétaire du fichier
                        if (_SERVER_OSTYPE == 'unix' && fileowner($dest_file) == $processid) chmod($dest_file, $file_mode);
                    }
                    else $ok = false;
                }
            }
        }
        return $ok;
    }

    /**
     * Suppression récursive du contenu d'un dossier source vers un dossier destination
     *
     * @param string $strPath dossier à supprimer
     */

    public static function deleteDir($strPath)
    {
        if (file_exists($strPath))
        {
            $resFolder = opendir($strPath);

            while ($strFile = readdir($resFolder))
            {
                if (!in_array($strFile, array('.', '..')))
                {
                    $strFilePath = "{$strPath}/{$strFile}";

                    if (is_dir($strFilePath)) ploopi_deletedir($strFilePath);
                    else unlink($strFilePath);
                }
            }

            if (is_dir($strPath)) rmdir($strPath);
        }
    }

    /**
     * Création récursive d'un dossier
     *
     * @param string $strPath chemin à créer
     * @param octal $octMode droits appliqués
     */


    /**** DEPRECATED utiliser mkdir ******/
    public static function makeDir($strPath, $octMode = 0750)
    {
        if (!file_exists($strPath))
        {
            $arrFolder = explode('/', $strPath);

            $strOldPath = '/';

            foreach($arrFolder as $strFolder)
            {
                if ($strFolder != '')
                {
                    $strFolder = $strOldPath.$strFolder;

                    if (!is_dir($strFolder)) mkdir($strFolder, $octMode);

                    $strOldPath = "{$strFolder}/";
                }
            }
        }
    }

    /**
     * Renvoie le type mime du fichier en fonction de son extension (mais pas par rapport au contenu)
     *
     * @param string $filename chemin du fichier
     * @return string type mime
     *
     * @see ploopi_downloadfile
     */

    function ploopi_getmimetype($filename)
    {
        $ext = ploopi_file_getextension($filename);

        global $db;

        // Si mimetype = '' ou pas trouvé c'est que c'est un octetstream donc on passe
        $sqlMime = $db->query("SELECT mimetype FROM ploopi_mimetype WHERE ext = '{$ext}' AND mimetype != ''");
        if($db->numrows($sqlMime))
        {
            $fieldMime = $db->fetchrow($sqlMime);
            return $fieldMime['mimetype'];
        }

        $strUserBrowser = '';
        if (!empty($_SERVER['HTTP_USER_AGENT']))
        {
            if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
                $strUserBrowser = "Opera";
            elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
                $strUserBrowser = "IE";
        }

        /// important for download im most browser
        $strMimetype = ($strUserBrowser == 'IE' || $strUserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';

        return($strMimetype);
    }

    /**
     * Téléchargement d'un fichier vers le navigateur. Complète automatiquement les entêtes en renseignant notamment le type mime.
     *
     * @param string $filepath chemin physique du fichier
     * @param string $destfilename nom du fichier tel qu'il apparaîtra au moment du téléchargement
     * @param boolean $deletefile true si le fichier doit être supprimé après téléchargement
     * @param boolean $attachment true si le fichier doit être envoyé en "attachment", false si il doit être envoyé "inline"
     * @param boolean $die true si la fonction doit arrêter le script
     * @return boolean false si le fichier n'existe pas, rien sinon
     *
     * @see ploopi_getmimetype
     * @see ploopi_file_getextension
     */

    function ploopi_downloadfile($filepath, $destfilename, $deletefile = false, $attachment = true, $die = true)
    {
        //if (substr($path,-1) == '/') $path = substr($path, 0, strlen($path)-1);

        if (file_exists($filepath))
        {
            ploopi_ob_clean();

            @set_time_limit(0);

            $filepath = rawurldecode($filepath);
            $size = filesize($filepath);

            header('Content-Type: ' . ploopi_getmimetype($destfilename));

            if (ploopi_file_getextension($destfilename) == 'svgz') header('Content-Encoding: gzip');

            if ($attachment) header("Content-disposition: attachment; filename=\"{$destfilename}\"");
            else header("Content-disposition: inline; filename=\"{$destfilename}\"");

            header('Expires: Sat, 1 Jan 2000 05:00:00 GMT');
            header('Accept-Ranges: bytes');
            header('Cache-control: private');
            header('Pragma: private');
            header("Content-Encoding: identity");
            header("X-Ploopi: Download"); // Permet d'indiquer au gestionnaire buffer qu'il s'agit d'un téléchargement de fichier @see ploopi_ob_callback

            $chunksize = 1*(1024*1024);

            if ($fp = fopen($filepath, 'r'))
            {
                while(!feof($fp) && connection_status() == 0)
                {
                    echo fread($fp, $chunksize);
                }
                fclose($fp);

            }
            else
            {
                header('Content-type: text/html; charset=iso-8859-1');
                ploopi_die('Impossible d\'ouvrir le fichier');
            }

            if ($deletefile && is_writable($filepath)) @unlink($filepath);

            if ($die) ploopi_die(null, false);

        }
        else return(false);
    }

    /**
     * Extrait l'extension d'un fichier
     *
     * @param string $filename chemin physique du dossier
     * @return string extension du fichier
     *
     * @see ploopi_downloadfile
     */

    function ploopi_file_getextension($filename)
    {
        $filename_array = explode('.',$filename);
        return(strtolower($filename_array[sizeof($filename_array)-1]));
    }
}
