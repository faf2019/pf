<?php
/*
    Copyright (c) 2009,2010 Ovensia
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
 * Méthodes permettant de mettre en place des mécanismes de chiffrement
 * Voir également la classe ploopi_cipher.
 *
 * @package pf
 * @subpackage crypt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see ploopi_cipher
 */

namespace ovensia\pf\Core\Tools;


/**
 * Classe de chiffrement
 *
 * @package pf
 * @subpackage crypt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Crypt
{
    /**
     * Génère un mot de passe pour écrire dans le fichier .htpasswd
     *
     * @param string $pass mot de passe en clair
     * @return string mot de passe chiffré
     *
     * @see crypt
     */

    static function htpasswdGenerate($pass)
    {
        return (crypt(trim($pass),CRYPT_STD_DES));
    }


    /**
     * Générateur de clé aléatoire
     */

    static function randkeyGenerate($intLength = 32)
    {
        $strRandKey = '';
        for ($i=0; $i<$intLength; $i++) $strRandKey.= chr(mt_rand(0, 255));
        return $strRandKey;
    }
}
