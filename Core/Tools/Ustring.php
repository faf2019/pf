<?php
// Voir ici : https://github.com/corpsee/php-utf-8
// Ici aussi : https://gist.github.com/Dethnull/9613129

namespace ovensia\pf\Core\Tools;

use ovensia\pf\Core;
use ovensia\pf\Core\Exception;

/**
 * Fonction de manipulation de chaînes.
 * Conversion, découpage, réécriture, etc..
 *
 * @package pf
 * @subpackage string
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Ustring
{
    use Core\Builders\Factory;

    private $_strString;

    private static $_arrWordSeparators = array(
        ' ',':',';',',','.','!','?',"'",'^','`','"',
        '«','»','~','-','_','|','(',')','[',']','{',
        '}','<','>','$','£','µ','&','#','§','@','%',
        '°', '=','+','/','*','\\','/',"\n","\r"
    );

    public function __construct($strString)
    {
        $this->setString($strString);
    }

    public function getString() { return $this->_strString; }

    public function setString($strString)
    {
        if (is_null($strString)) $strString = '';
        if (!is_scalar($strString) ) throw new Exception('Not a string');
        $this->_strString = $strString;
        return $this;
    }

    public function __toString() { return $this->_strString; }

    /**
     * Insère un retour à la ligne HTML à chaque nouvelle ligne, améliore le comportement de la fonction php nl2br()
     *
     * @param string $strStr
     * @return string chaîne modifiée
     */

    public function nl2br()
    {
       $this->_strString = preg_replace("/\r\n|\n|\r/", "<br />", $this->_strString);
       return $this;
    }

    /**
     * Convertit tous les caractères accentués en caractères non accentués en préservant les majuscules/minuscules
     *
     * @param string $str chaîne à convertir
     * @return string chaîne modifiée
     */

    public function convertAccents()
    {
        $this->_strString = str_replace(
            array(
                '¥','µ','À','Á','Â','Ã','Ä','Å','Æ','Ç',
                'È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ',
                'Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü',
                'Ý','ß','à','á','â','ã','ä','å','æ','ç',
                'è','é','ê','ë','ì','í','î','ï','ð','ñ',
                'ò','ó','ô','õ','ö','ø','ù','ú','û','ü',
                'ý','ÿ','Þ'
            ),
            array(
                'Y','u','A','A','A','A','A','A','A','C',
                'E','E','E','E','I','I','I','I','D','N',
                'O','O','O','O','O','O','U','U','U','U',
                'Y','ss','a','a','a','a','a','a','a','c',
                'e','e','e','e','i','i','i','i','o','n',
                'o','o','o','o','o','o','u','u','u','u',
                'y','y','s'
            ),
            $this->_strString
        );

        return $this;
    }


    /**
     * Décode les caractères iso non représentables
     *
     * @param boolean true si les caractères doivent être adaptés
     * @return chaîne décodée
     */
    public function iso8859Clean($booTranslit = true)
    {
        $this->_strString = strtr($this->_strString, array(
           "\x80" => "&#8364;", /* EURO SIGN */
           "\x82" => "&#8218;", /* SINGLE LOW-9 QUOTATION MARK */
           "\x83" => "&#402;",  /* LATIN SMALL LETTER F WITH HOOK */
           "\x84" => "&#8222;", /* DOUBLE LOW-9 QUOTATION MARK */
           "\x85" => "&#8230;", /* HORIZONTAL ELLIPSIS */
           "\x86" => "&#8224;", /* DAGGER */
           "\x87" => "&#8225;", /* DOUBLE DAGGER */
           "\x88" => "&#710;",  /* MODIFIER LETTER CIRCUMFLEX ACCENT */
           "\x89" => "&#8240;", /* PER MILLE SIGN */
           "\x8a" => "&#352;",  /* LATIN CAPITAL LETTER S WITH CARON */
           "\x8b" => "&#8249;", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
           "\x8c" => "&#338;",  /* LATIN CAPITAL LIGATURE OE */
           "\x8e" => "&#381;",  /* LATIN CAPITAL LETTER Z WITH CARON */
           "\x91" => "&#8216;", /* LEFT SINGLE QUOTATION MARK */
           "\x92" => "&#8217;", /* RIGHT SINGLE QUOTATION MARK */
           "\x93" => "&#8220;", /* LEFT DOUBLE QUOTATION MARK */
           "\x94" => "&#8221;", /* RIGHT DOUBLE QUOTATION MARK */
           "\x95" => "&#8226;", /* BULLET */
           "\x96" => "&#8211;", /* EN DASH */
           "\x97" => "&#8212;", /* EM DASH */

           "\x98" => "&#732;",  /* SMALL TILDE */
           "\x99" => "&#8482;", /* TRADE MARK SIGN */
           "\x9a" => "&#353;",  /* LATIN SMALL LETTER S WITH CARON */
           "\x9b" => "&#8250;", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
           "\x9c" => "&#339;",  /* LATIN SMALL LIGATURE OE */
           "\x9e" => "&#382;",  /* LATIN SMALL LETTER Z WITH CARON */
           "\x9f" => "&#376;"   /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
        ));

        if ($booTranslit)
            $this->_strString = strtr($this->_strString, array(
               '&#8364;' => 'Euro', /* EURO SIGN */
               '&#8218;' => ',',    /* SINGLE LOW-9 QUOTATION MARK */
               '&#402;' => 'f',     /* LATIN SMALL LETTER F WITH HOOK */
               '&#8222;' => ',,',   /* DOUBLE LOW-9 QUOTATION MARK */
               '&#8230;' => '...',  /* HORIZONTAL ELLIPSIS */
               '&#8224;' => '+',    /* DAGGER */
               '&#8225;' => '++',   /* DOUBLE DAGGER */
               '&#710;' => '^',     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
               '&#8240;' => '0/00', /* PER MILLE SIGN */
               '&#352;' => 'S',     /* LATIN CAPITAL LETTER S WITH CARON */
               '&#8249;' => '<',    /* SINGLE LEFT-POINTING ANGLE QUOTATION */
               '&#338;' => 'OE',    /* LATIN CAPITAL LIGATURE OE */
               '&#381;' => 'Z',     /* LATIN CAPITAL LETTER Z WITH CARON */
               '&#8216;' => "'",    /* LEFT SINGLE QUOTATION MARK */
               '&#8217;' => "'",    /* RIGHT SINGLE QUOTATION MARK */
               '&#8220;' => '"',    /* LEFT DOUBLE QUOTATION MARK */
               '&#8221;' => '"',    /* RIGHT DOUBLE QUOTATION MARK */
               '&#8226;' => '*',    /* BULLET */
               '&#8211;' => '-',    /* EN DASH */
               '&#8212;' => '--',   /* EM DASH */
               '&#732;' => '~',     /* SMALL TILDE */
               '&#8482;' => '(TM)', /* TRADE MARK SIGN */
               '&#353;' => 's',     /* LATIN SMALL LETTER S WITH CARON */
               '&#8250;' => '>',    /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
               '&#339;' => 'oe',    /* LATIN SMALL LIGATURE OE */
               '&#382;' => 'z',     /* LATIN SMALL LETTER Z WITH CARON */
               '&#376;' => 'Y'      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
            ));

        return $this;
    }

    /**
     * Encode une chaîne en MIME base64 avec compatibilité du codage pour les URL (métode url-safe base64)
     * thx to massimo dot scamarcia at gmail dot com
     * Php version of perl's MIME::Base64::URLSafe, that provides an url-safe base64 string encoding/decoding (compatible with python base64's urlsafe methods)
     *
     * @param string $str chaîne à coder
     * @return string chaîne codée
     *
     * @copyright massimo dot scamarcia at gmail dot com
     *
     * @see base64_encode
     */

    public function base64Encode()
    {
        $this->_strString = rtrim(strtr(base64_encode($this->_strString), '+/', '-_'), '=');
        return $this;
    }

    /**
     * Décode une chaîne en MIME base64 (métode url-safe base64)
     *
     * @param string $str chaîne à décoder
     * @return string chaîne décodée
     *
     * @see base64_decode
     */

    public function base64Decode()
    {
        $this->_strString = base64_decode(str_pad(strtr($this->_strString, '-_', '+/'), strlen($this->_strString) % 4, '=', STR_PAD_RIGHT));
        return $this;
    }


    /**
    * UTF-8 aware replacement for ltrim().
    *
    * @author Andreas Gohr <andi@splitbrain.org>
    * @see http://www.php.net/ltrim
    * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
    * @param string $str
    * @param string $charlist
    * @return string
    */
    public function ltrim($charlist= '')
    {
        if(empty($charlist)) {
            $this->_strString = ltrim($this->_strString);
            return $this;
        }

        // Quote charlist for use in a characterclass
        $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $charlist);

        $this->_strString = preg_replace('/^['.$charlist.']+/u', '', $this->_strString);
        return $this;
    }

    /**
    * UTF-8 aware replacement for rtrim().
    *
    * @author Andreas Gohr <andi@splitbrain.org>
    * @see http://www.php.net/rtrim
    * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
    * @param string $charlist
    * @return string
    */
    public function rtrim($charlist= '')
    {
        if(empty($charlist)) {
            $this->_strString = rtrim($this->_strString);
            return $this;
        }

        // Quote charlist for use in a characterclass
        $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $charlist);

        $this->_strString = preg_replace('/['.$charlist.']+$/u', '', $this->_strString);
        return $this;
    }

    /**
    * UTF-8 aware replacement for trim().
    *
    * @author Andreas Gohr <andi@splitbrain.org>
    * @see http://www.php.net/trim
    * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
    * @param boolean $charlist
    * @return string
    */
    public function trim($charlist = '')
    {
        if(empty($charlist)) {
            $this->_strString = trim($this->_strString);
            return $this;
        }

        return $this->ltrim($charlist)->rtrim($charlist);
    }

    /**
     * strpos UTF-8
     */

    public function pos($needle, $offset = 0)
    {
        return mb_strpos($this->_strString, $needle, $offset);
    }



    /**
     * Réécrit une chaîne destinée à être transformée en URL
     *
     * @param string $str chaîne à transformer
     * @return string chaîne transformée
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public function toUrl()
    {
        /*
        $this->trim()->convertAccents();
        $this->_strString = preg_replace(array('/--+/', '/-$/'), array('-', ''), urlencode(mb_strtolower(str_replace(self::$_arrWordSeparators, '-', $this->_strString))));
        */

        $this->_strString = preg_replace(array('/--+/', '/-$/'), array('-', ''), mb_strtolower(str_replace(self::$_arrWordSeparators, '-', mb_convert_encoding($this->convertAccents(), 'UTF-8', 'UTF-8'))));

        return $this;
    }


    /**
     * Remplacement une liste de caractères par une autre
     */

    /*

    function replace($replacement, $haystack)
    {
        $this->_strString = implode($replacement, mb_split($this->_strString, $haystack));
        return $this;
    }
    */

    /**
     * Encode les caractères spéciaux d'une chaîne pour qu'elle puisse être intégrée dans un document XML
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public function xmlEntities()
    {
        $this->_strString = str_replace(array("&", ">", "<", "\"", "'", "\r"), array("&amp;", "&gt;", "&lt;", "&quot;", "&apos;", ""), $this->_strString);

        return $this;
    }

    public function toUpper()
    {
        $this->_strString = mb_convert_case($this->_strString, MB_CASE_UPPER);

        return $this;
    }

    public function toLower()
    {
        $this->_strString = mb_convert_case($this->_strString, MB_CASE_LOWER);

        return $this;
    }

    public function ucWords()
    {
        $this->_strString = mb_convert_case($this->_strString, MB_CASE_TITLE);

        return $this;
    }

    public function ucFirst()
    {
        $strFc = mb_strtoupper(mb_substr($this->_strString, 0, 1));
        $this->_strString = $strFc.mb_substr($this->_strString, 1);

        return $this;
    }

    /**
     * Retourne les mots de la chaîne
     */
    public function getWords() {
        return (preg_split('/[\b\s\(\).,\-\',:!\?;"\{\}\[\]\/„“»«‘\r\n]+/u', $this->_strString, 0, PREG_SPLIT_NO_EMPTY));
    }


    /**
     * Coupe une chaîne à la longueur désirée et ajoute '...'
     *
     * @param string $len longueur maximale de la chaîne
     * @param string $mode détermine ou couper la chaîne : 'right', 'middle'
     */

    public function cut($intLen = 30, $strMode = 'right')
    {
        if (mb_strlen($this->_strString, 'UTF-8') > $intLen) {
            switch($strMode) {
                case 'right':
                    $this->_strString = mb_substr($this->_strString, 0, $intLen-3).'...';
                break;

                case 'middle':
                    $this->_strString = mb_substr($this->_strString, 0, ($intLen-3)/2).'...'.mb_substr($this->_strString, -($intLen-3)/2, ($intLen-3)/2);
                break;
            }
        }

        return $this;
    }



    /**
     * Réécrit une URL selon les règles de réécriture fournies en paramètre
     *
     * @param string $strUrl URL à réécrire
     * @param array $arrRules règles de réécriture au format array('patterns' => array(), 'replacements' => array())
     * @param string $strTitle titre à insérer dans la nouvelle URL
     * @param array $arrFolders tableau contenu les intitulé de dossiers à ajouter à l'url
     * @param bool $booKeepExt true si l'url doit contenir l'extension de fichier éventuellement utilisée dans le paramètre $strTitle (par défaut : false)
     * @return string URL réécrite
     *
     * @see _FRONTOFFICE_REWRITERULE
     * @see ploopi_convertaccents
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    function ploopi_urlrewrite($strUrl, $arrRules, $strTitle = '', $arrFolders = null, $booKeepExt = false)
    {
        if (defined('_FRONTOFFICE_REWRITERULE') && _FRONTOFFICE_REWRITERULE && !empty($arrRules['patterns']) && !empty($arrRules['replacements']))
        {
            $strExt = 'html';

            if ($booKeepExt)
            {
                $strExt = ploopi_file_getextension($strTitle);
                $strTitle = basename($strTitle, ".{$strExt}");
            }

            $strTitle = ploopi_string2url($strTitle);

            // Construction des dossiers si nécessaire
            if (!empty($arrFolders) && is_array($arrFolders))
            {
                foreach($arrFolders as &$strFolder) $strFolder = ploopi_string2url($strFolder);
                $strFolders = implode('/', $arrFolders).'/';
            }
            else $strFolders = '';

    //        ploopi_print_r($arrRules);

            return str_replace(
                array('<TITLE>', '<FOLDERS>', '<EXT>'),
                array($strTitle, $strFolders, $strExt),
                preg_replace($arrRules['patterns'], $arrRules['replacements'], $strUrl)
            );
        }
        else return $strUrl;
    }


    /**
     * Equivalent de strtr en version multibyte (UTF-8) car la version "mbstring" de strtr n'existe pas.
     * Remplace des caractères dans une chaîne.
     *
     * @param string $str la chaîne à traiter
     * @param mixed $from caractères de départ sous forme d'une chaine ou d'un tableau associatif (si to est null)
     * @param string $to caractères de remplacement ou null
     * @return string chaîne modifiée
     *
     * @see strtr
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    function ploopi_strtr($str, $from, $to = null)
    {
        if (!isset($to) && is_array($from))
        {
            return str_replace(array_keys($from), $from, $str);
        }
        else return str_replace(ploopi_str_split($from), ploopi_str_split($to), $str);

    }

    /**
     * Equivalent de str_split en version multibyte (UTF-8) car la version "mbstring" de str_split n'existe pas.
     * Convertit une chaîne de caractères en tableau.
     *
     * @param string $str la chaîne Ã  convertir
     * @return array tableau de caractères
     *
     * @see strtr
     */

    function ploopi_str_split($str)
    {
        $strlen = mb_strlen($str);

        while ($strlen)
        {
            $array[] = mb_substr($str, 0, 1, 'UTF-8');
            $str = mb_substr($str, 1, $strlen, 'UTF-8');
            $strlen = mb_strlen($str);
        }

        return $array;
    }



    /**
     * Encode une chaîne en UTF8
     *
     * @param string $str chaîne ISO-8859-15
     * @return string chaîne encodée UTF8
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    function ploopi_utf8encode($str)
    {
        return iconv('ISO-8859-15', 'UTF-8', $str);
    }


    /**
     * Rend les liens d'un texte cliquables
     *
     * @param string $text le texte à traiter
     * @return string le texte modifié
     */

    function ploopi_make_links($text)
    {
        $text = preg_replace(
                    array(
                            '!(^|([^\'"]\s*))([hf][tps]{2,4}:\/\/[^\s<>"\'()]{4,})!mi',
                            '!<a href="([^"]+)[\.:,\]]">!',
                            '!([\.:,\]])</a>!',
                            '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/iex'
                        ),
                    array(
                            '$2<a href="$3">$3</a>',
                            '<a href="$1">',
                            '</a>$1',
                           "stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
                        ),
                    $text);

        return $text;
    }

    /**
     * Encode et affiche une variable au format JSON et modifie les entêtes du document. Compatible x-json
     *
     * @param mixed $var variable à encoder
     * @param boolean $utf8encode true si le contenu de la variable doit être converti en UTF8 (true par défaut)
     * @param boolean $use_xjson true si X-json peut être utilisé
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    function ploopi_print_json($var, $utf8encode = true, $use_xjson = true)
    {

        if ($utf8encode) $var = ploopi_array_map('ploopi_utf8encode', $var);

        $json = json_encode($var);
        header("Content-Type: text/x-json");
        if ($use_xjson === false || strlen($json) > 1024) echo $json;
        else header("X-Json: {$json}");
    }

    /**
     * Nettoie le code html et le rend valide XHTML
     *
     * @param string $strContent code HTML à valider
     * @param boolean $booTrusted true si le code fourni est "sûr", dans ce cas le filtrage est moins sévère (par défaut : false)
     * @return string code HTML validé
     *
     * @link http://htmlpurifier.org/
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    function ploopi_htmlpurifier($strContent, $booTrusted = false)
    {
        $strCachePath = _PATHDATA._SEP.'cache';
        if (!file_exists($strCachePath)) ploopi_makedir($strCachePath);

        require_once './lib/htmlpurifier/HTMLPurifier.auto.php';
        $objConfig = HTMLPurifier_Config::createDefault();
        $objConfig->set('Cache.SerializerPath', $strCachePath);
        $objConfig->set('Core.Encoding', 'ISO-8859-15');
        $objConfig->set('HTML.Doctype', 'XHTML 1.0 Strict');

        if ($booTrusted)
        {
            $objConfig->set('HTML.Trusted', true);
            $objConfig->set('Attr.EnableID', true);
            $objConfig->set('HTML.SafeEmbed', true);
            $objConfig->set('HTML.SafeObject', true);
        }

        $objPurifier = new HTMLPurifier($objConfig);

        $res = $objPurifier->purify($strContent);

        return $res;
    }

    /**
     * Convertit une couleur HTML/Hex en un tableau de composantes RVB (entier)
     *
     * @param string $strHex couleur au format HTML/Hex
     * @return array tableau contenant les composantes RVB (entier)
     */

    function ploopi_color_hex2rgb($strHex)
    {
        return array_map('hexdec', str_split(str_replace('#', '', $strHex) ,2));
    }

    /**
     * Vérifie qu'une url est valide
     *
     * @param string $url url à tester
     * @return boolean true si l'url est valide
     */

    function ploopi_is_url($url)
    {
        $urlregex = "^(https?)\:\/\/";

        // USER AND PASS (optional)
        $urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

        // HOSTNAME OR IP
        $urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*";

        // PORT (optional)
        $urlregex .= "(\:[0-9]{2,5})?";

        // PATH  (optional)
        $urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";

        // GET Query (optional)
        $urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?";

        // ANCHOR (optional)
        $urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";

        return eregi($urlregex, $url) ? true : false;
    }

    /**
     * Nettoie une chaine pour en faire un nom de fichier valide. Ne conserve que les caracteres : [a-zA-Z0-9_-]
     *
     * @param string $str chaine à nettoyer
     * @return string la chaine nettoyée
     */

    function ploopi_clean_filename($str)
    {
        $str = ploopi_convertaccents($str);
        $arrSearch = array ('@[ */]@i','@[^a-zA-Z0-9_-]@');
        $arrReplace = array ('_','');
        return preg_replace($arrSearch, $arrReplace, $str);
    }
}
