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

/**
 * Gestion du chiffrement/déchiffrement
 *
 * @package pf
 * @subpackage crypt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see mcrypt
 * @see _SECRETKEY
 */

namespace ovensia\pf\Core\Tools;

use ovensia\pf\Core\Service\Controller;
use ovensia\pf\Core\Builders\Singleton;
use ovensia\pf\Core\Exception;

/**
 * Classe de chiffrement/déchiffrement (basé sur mcrypt), notamment utilisée pour chiffrer les URL
 *
 * @package pf
 * @subpackage crypt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see mcrypt
 * @see _SECRETKEY
 */

class Cipher
{
    use Controller;
    use Singleton;

    /**
     * Clé secrète
     *
     * @var string
     */

    private $_strKey;

    /*
     * Vecteur d'initialisation
     *
     * @var string
     */

    private $_strIV;

    /**
     * Pointeur de chiffrement
     *
     * @var resource
     */

    private $_resCipher;

    /**
     * Constructeur de la classe
     *
     * @return ploopiCipher
     */

    protected function __construct($key = null, $iv = null, $algorithm = null)
    {
        $this->_resCipher = mcrypt_module_open($algorithm ? $algorithm : $this->getService('config')->_CRYPT_ALGORITHM, '', 'cbc', '');
        $this->_strKey = substr($key ? $key : $this->getService('config')->_SECRETKEY, 0, mcrypt_enc_get_key_size($this->_resCipher));
        $this->_strIV = substr($iv ? $iv : $this->getService('config')->_FINGERPRINT, 0, mcrypt_enc_get_block_size($this->_resCipher));
    }


    /**
     * Chiffre une chaine
     *
     * @param string $str chaîne à chiffrer
     * @return string chaîne chiffrée
     */

    public function crypt($strString)
    {
        if (empty($strString)) throw new Exception("Empty string");

        mcrypt_generic_init($this->_resCipher, $this->_strKey, $this->_strIV);
        $strEncrypted = Ustring::getInstance(mcrypt_generic($this->_resCipher, gzcompress($strString)))->base64Encode()->getString();
        mcrypt_generic_deinit($this->_resCipher);


        return $strEncrypted;
    }

    /**
     * Déchiffre une chaîne
     *
     * @param string $encrypted chaîne chiffrée
     * @return string chaîne déchiffrée
     */

    public function decrypt($strEncrypted)
    {
        if (empty($strEncrypted)) throw new Exception("Empty string");

        mcrypt_generic_init($this->_resCipher, $this->_strKey, $this->_strIV);
        $strDecoded = @gzuncompress(@mdecrypt_generic($this->_resCipher, Ustring::getInstance($strEncrypted)->base64Decode()->getString()));
        mcrypt_generic_deinit($this->_resCipher);

        return $strDecoded;
    }

    /**
     * Détruit l'objet
     */

    public function __destruct()
    {
        mcrypt_module_close($this->_resCipher);
    }
}
?>
