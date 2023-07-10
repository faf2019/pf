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
 * Gestion de tableaux
 *
 * @package Ploopi2
 * @subpackage Array
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\Tools;

use ovensia\pf\Core;

/**
 * Class de Gestion de tableaux
 *
 * @package Ploopi2
 * @subpackage Array
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ArrayObject
{
    use Core\Builders\Factory;

    private $_arrData;

    public function __construct($arrData = array())
    {
        $this->setArray($arrData);
    }

    /**
     * Parse le nom d'une clé de la forme /variable/exemple
     *
     * @param mixed $mixKey nom de la clé
     * @return array nom de la clé sous forme d'une tableau
     */
    private static function _parseKey($mixKey)
    {
        if (is_string($mixKey))
        {
            // Suppression du 1er caractère si "/"
            if (strlen($mixKey) > 0 && $mixKey[0] == '/') $mixKey = $mixKey == '/' ? '' : substr($mixKey, 1-strlen($mixKey));
            $mixKey = $mixKey == '' ? array() : explode('/', $mixKey);
        }
        elseif (is_integer($mixKey)) $mixKey = array($mixKey);
        elseif (!is_array($mixKey)) $mixKey = array();

        return $mixKey;
    }

    public function getArray() { return $this->_arrData; }

    public function setArray($arrData = array())
    {
        if (!is_array($arrData)) throw new Core\Exception('Not an array');
        $this->_arrData = $arrData;
        return $this;
    }

    /**
     * Stockage d'une variable
     *
     * @param string/array $mixKey chemin vers la variable
     * @param mixed $mixValue variable à stocker
     * @return boolean true si la variable a été stockée
     */
    public function set($mixKey, $mixValue)
    {
        // Recherche pointeur
        $ptrVar = & $this->get($mixKey, true);

        // Affectation de la variable
        $ptrVar = $mixValue;

        return $this;
    }

    /**
     * Merge d'une variable
     *
     * @param string/array $mixKey chemin vers la variable
     * @param mixed $mixValue variable à stocker
     * @return boolean true si la variable a été stockée
     */

    public function merge($mixKey, $mixValue)
    {
        // Recherche pointeur
        $ptrVar = & $this->get($mixKey, true);

        // Merge
        $ptrVar = array_replace_recursive($ptrVar, $mixValue);

        return $this;
    }

    /**
     * Supprime une variable du tableau
     *
     * @param string/array $mixVar chemin vers la variable
     * @return boolean true si la variable a été supprimée
     */

    public function delete($mixKey = '')
    {
        $arrKey = self::_parseKey($mixKey);

        // Suppression complète
        if (empty($arrKey)) {
            $this->setArray();
            return $this;
        }


        // Pointeur sur la racine de la variable session par défaut
        $ptrVar = &$this->_arrData;

        // Position du pointeur sur la bonne variable et suppression
        foreach($arrKey as $strKey)
        {
            if (isset($ptrVar[$strKey]))
            {
                if ($strKey == $arrKey[count($arrKey)-1]) { unset($ptrVar[$strKey]); return $this; }
                else $ptrVar = &$ptrVar[$strKey];
            }
            else
            {
                $strKey = implode('/', $arrKey);
                throw new Core\Exception("Unkown key '{$strKey}' in array");
            }
        }

        return $this;
    }

    /**
     * Lecture d'une variable
     *
     * @param string/array $mixKey chemin vers la variable
     * @param boolean $booSetter true si crée la variable
     * @return mixed contenu de la variable ou null
     */
    public function & get($mixKey = '', $booSetter = false)
    {
        $arrKey = self::_parseKey($mixKey);

        if (empty($arrKey)) return $this->_arrData;

        // Pointeur sur la variable
        $ptrVar = &$this->_arrData;

        // Lecture de la variable
        foreach($arrKey as $strKey)
        {
            if ((is_array($ptrVar) && array_key_exists($strKey, $ptrVar)) || $booSetter) $ptrVar = &$ptrVar[$strKey];
            else
            {
                $strKey = implode('/', $arrKey);
                throw new Core\Exception("Unkown key '{$mixKey}' in array");
            }
        }

        // Retourne la variable
        return $ptrVar;
    }

    public function & __get($strKey) { return $this->get($strKey); }

    public function __set($strKey, $mixValue) { return $this->set($strKey, $mixValue); }

    public function __isset($strKey) { return $this->exists($strKey); }

    public function __unset($strKey) { return $this->delete($strKey); }

    /**
     * Vérifie qu'une clé existe
     *
     * @param string/array $mixKey chemin vers la variable
     * @return mixed contenu de la variable ou null
     */

    public function exists($mixKey)
    {
        $arrKey = self::_parseKey($mixKey);

        // Pointeur sur la variable session
        $ptrVar = &$this->_arrData;

        // Lecture de la variable
        foreach($arrKey as $strKey)
        {
            if (isset($ptrVar[$strKey])) $ptrVar = &$ptrVar[$strKey];
            else return false;
        }

        return true;
    }

    /**
     * Vérifie qu'une clé est vide (ou le tableau)
     *
     * @param string/array $mixKey chemin vers la variable
     * @return mixed contenu de la variable ou null
     */

    public function isEmpty($mixKey = '')
    {
        $arrKey = self::_parseKey($mixKey);

        if (empty($arrKey)) return empty($this->_arrData);

        // Pointeur sur la variable session
        $ptrVar = &$this->_arrData;

        // Lecture de la variable
        foreach($arrKey as $strKey)
        {
            if (isset($ptrVar[$strKey])) $ptrVar = &$ptrVar[$strKey];
            else return true;
        }

        return empty($ptrVar);
    }


    public function getIterator() { return new \ArrayIterator($this->_arrData); }

    public function implode($strGlue = '') { return $this->isEmpty() ? '' : Ustring::getInstance(implode($strGlue, $this->_arrData)); }

    private function _sanitizeKeysRec($arrData)
    {
        $arrNewArray = array();

        foreach($arrData as $strKey => $mixValue)
        {
            $strKey = preg_replace("/[^a-z0-9_]/i", "_", Ustring::getInstance($strKey)->convertAccents()->getString());
            //$strKey = preg_replace("/[^a-z0-9_]/", "_", $strKey);

            // Cas particulier des clés non conformes
            if (strlen($strKey) == 0) $strKey = 'xml';
            elseif (substr($strKey,0,1) == '_') $strKey = 'xml'.$strKey;

            if (is_array($mixValue)) $arrNewArray[$strKey] = $this->_sanitizeKeysRec($mixValue);
            else $arrNewArray[$strKey] = $mixValue;
        }

        return $arrNewArray;
    }

    /**
     * "Nettoie" les clés d'un tableau multidimensionnel afin que les clés soient compatibles avec des noms d'entités ou de variables
     *
     * @return ArrayObject le tableau modifié
     */
    public function sanitizeKeys()
    {
        $this->_arrData = $this->_sanitizeKeysRec($this->_arrData);
        return $this;
    }


    private function _mapRec($cbFunction, $mixedVar, $booInstanciable)
    {
        if (is_array($mixedVar)) { foreach($mixedVar as $strKey => $mixedValue) $mixedVar[$strKey] = $this->_mapRec($cbFunction, $mixedValue, $booInstanciable); return $mixedVar; }
        elseif (is_object($mixedVar)) { foreach(get_object_vars($mixedVar) as $strKey => $mixedValue)  $mixedVar->$strKey = $this->_mapRec($cbFunction, $mixedValue, $booInstanciable); return $mixedVar; }
        else
        {
            if ($booInstanciable && is_array($cbFunction))
            {
                $obj = new $cbFunction[0]($mixedVar);
                $obj->$cbFunction[1]();
                return $obj->__toString();
            }
            else return call_user_func($cbFunction, $mixedVar);
        }
    }

    /**
     * Applique récursivement une fonction sur les éléments d'un tableau
     * Les éléments peuvent être des tableaux récursifs ou des objets récursifs
     *
     * @param callback $cbFunction fonction à appliquer sur le tableau
     * @return ArrayObject le tableau modifié
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see array_map
     */

    public function map($cbFunction, $booInstanciable = false)
    {
        $this->_arrData = $this->_mapRec($cbFunction, $this->_arrData, $booInstanciable);
        return $this;
    }


    /**
     * Retourne le contenu d'un tableau multidimensionnel au format JSON
     *
     * @param boolean $booForceUTF8 true si les données doivent être convertie en UTF-8 (depuis ISO-8859-1)
     * @return string contenu JSON
     */

    public function toJson($booForceUTF8 = true)
    {
        $objArray = $this->getClone()->sanitizeKeys();

        if ($booForceUTF8) $objArray->map('utf8_encode');

        return json_encode($objArray->getArray());
    }

    /**
     * Retourne le contenu d'un tableau multidimensionnel au format XML
     *
     * @param string $strRootName nom du noeud racine
     * @param string $strDefaultTagName nom des noeuds 'anonymes'
     * @param string $strEncoding charset utilisé
     * @return string contenu XML
     */

    public function toXml($strRootName = 'data', $strDefaultTagName = 'row', $strEncoding = 'ISO-8859-1', $booSanitizeKeys = true)
    {
        require_once 'XML/Serializer.php';

        // Configuration du serializer XML
        $objSerializer = new XML_Serializer(
            array (
               'addDecl' => true,
               'encoding' => $strEncoding,
               'indent' => '  ',
               'rootName' => $strRootName,
               'defaultTagName' => $strDefaultTagName,
            )
        );

        // Sérialisation
        if ($booSanitizeKeys) $objError = $objSerializer->serialize($this->getClone()->sanitizeKeys()->getArray());
        else $objError = $objSerializer->serialize($this->getArray());

        // Détection d'erreur PEAR
        if (PEAR::isError($objError)) return false;

        // Contenu XML
        return $objSerializer->getSerializedData();
    }

    private static function _csvEchap($strValue, $strTextSep)
    {
        return $strTextSep.str_replace($strTextSep, $strTextSep.$strTextSep, $strValue).$strTextSep;
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format CSV
     *
     * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
     * @param string $strFieldSep séparateur de champs
     * @param string $strLineSep séparateur de lignes
     * @param string $strTextSep caractère d'encapsulation des contenus
     * @return string contenu CSV
     */

    public function toCsv($booHeader = true, $strFieldSep = ',', $strLineSep = "\n", $strTextSep = '"')
    {
        // Tableau des lignes du fichier CSV
        $arrCSV = array();

        $objArray = $this->getClone();

        // Fonction d'échappement & formatage du contenu
        $funcLineEchap = create_function('$value', 'return \''.$strTextSep.'\'.str_replace(\''.$strTextSep.'\', \''.$strTextSep.$strTextSep.'\', $value).\''.$strTextSep.'\';');

        // Ajout de la ligne d'entête
        if ($booHeader) {
            $arr = $objArray->getArray();
            $arrCSV[] = implode($strFieldSep, ArrayObject::getInstance(array_keys(reset($arr)))->map($funcLineEchap)->getArray());
        }

        // Traitement des contenus
        foreach($objArray->getIterator() as $row)
        {
            $arrCSV[] = implode($strFieldSep, ArrayObject::getInstance($row)->map($funcLineEchap)->getArray());
        }

        return implode($strLineSep, $arrCSV);
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format HTML
     *
     * @param unknown_type $booHeader
     * @param unknown_type $strClassName
     * @return unknown
     */

    function toHtml($booHeader = true, $strClassName = 'ploopi_array', $booHtmlEntities = true)
    {
        // Tableau des lignes
        $arrHtml = array();

        if (!empty($this->_arrData))
        {
            // Fonction de formatage du contenu
            $funcLineTH = create_function('$value', 'return \'<th>\'.htmlentities($value).\'</th>\';');

            // Ajout de la ligne d'entête
            if ($booHeader)
            {
                $strHtml = '<tr>';
                $arrHeaderLine = reset($this->_arrData);
                if (!is_array($arrHeaderLine)) throw new Core\Exception("Unable to render this array");
                foreach(array_keys($arrHeaderLine) as $mixValue)
                {
                    if (is_array($mixValue) || is_object($mixValue)) throw new Core\Exception("Value can't be an array or an object");
                    $strHtml .= '<th>'.htmlentities($mixValue).'</th>';
                }
                $arrHtml[] = $strHtml.'</tr>';
            }

            // Traitement des contenus
            // foreach($this->_arrData as $row) $arrHTML[] = '<tr>'.ArrayObject::getInstance($row)->map($funcLineTD)->implode().'</tr>';
            foreach($this->_arrData as $mixLine)
            {
                $strHtml = '<tr>';
                if (!is_array($mixLine)) throw new Core\Exception("Unable to render this array");
                foreach($mixLine as $strKey => $mixValue)
                {
                    if (is_object($mixValue)) throw new Core\Exception("Value can't be an object");
                    $strHtml .= '<td>';
                    if (is_array($mixValue)) $strHtml .= ArrayObject::getInstance($mixValue)->toHtml($booHeader, $strClassName, $booHtmlEntities);
                    else $strHtml .= htmlentities($mixValue);
                    $strHtml .= '</td>';
                }

                $arrHtml[] = $strHtml.'</tr>';
            }
        }

        // contenu HTML
        return '<table class="'.$strClassName.'">'.ArrayObject::getInstance($arrHtml)->implode().'</table>';
    }

    /**
     * Retourne le contenu d'un tableau à 2 dimensions au format XLS
     *
     * @param boolean $booHeader true si la ligne d'entête doit être ajoutée (nom des colonnes)
     * @param string $strFileName nom du fichier
     * @param string $strSheetName nom de la feuille dans le document XLS
     * @param array $arrDataFormats formats des colonnes ('title', 'type', 'width')
     * @param array $arrOptions Options de configuration de l'export ('landscape', 'fitpage_width', 'fitpage_height', 'file', 'send', 'setborder')
     * @return binary contenu XLS
     */

    function toXls($booHeader = true, $strSheetName = 'Feuille', $arrDataFormats = null, $arrOptions = null)
    {
        require_once 'Spreadsheet/Excel/Writer.php';

        $arrDefautOptions = array(
            'landscape' => true,
            'fitpage_width' => true,
            'fitpage_height' => false,
            'file' => null,
            'send' => false,
            'setborder' => false
        );

        $arrOptions = empty($arrOptions) ? $arrDefautOptions : array_merge($arrDefautOptions, $arrOptions);

        // Création du document
        if ($arrOptions['send'])
        {
            // Envoi direct vers le client
            $objWorkBook = new Spreadsheet_Excel_Writer();
            $objWorkBook->send($arrOptions['file']);
        }
        else
        {
            if (empty($arrOptions['file']))
            {
                // Création d'un fichier temporaire, retour du contenu via la méthode
                $strFileName = tempnam(sys_get_temp_dir(), uniqid());
                $objWorkBook = new Spreadsheet_Excel_Writer($strFileName);
            }
            else
            {
                // Ecriture dans le fichier passé en option
                $objWorkBook = new Spreadsheet_Excel_Writer($arrOptions['file']);
            }
        }

        $objFormatTitle = $objWorkBook->addFormat( array( 'Align' => 'center', 'Bold'  => 1, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter', 'FgColor' => 'silver'));
        if ($arrOptions['setborder']) { $objFormatTitle->setBorder(1); $objFormatTitle->setBorderColor('black'); }
        $objFormatDefault = $objWorkBook->addFormat( array( 'TextWrap' => 1, 'Align' => 'left', 'Bold'  => 0, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter'));
        if ($arrOptions['setborder']) { $objFormatDefault->setBorder(1); $objFormatDefault->setBorderColor('black'); }

        // Définition des différents formats numériques/text
        $arrFormats = array(
            'string' => null,
            'float' => null,
            'float_percent' => null,
            'float_euro' => null,
            'integer' => null,
            'integer_percent' => null,
            'integer_euro' => null,
            'date' => null,
            'datetime' => null
        );

        foreach($arrFormats as $strKey => &$objFormat)
        {
            $objFormat = $objWorkBook->addFormat( array( 'Align' => 'right', 'TextWrap' => 1, 'Bold'  => 0, 'Color'  => 'black', 'Size'  => 10, 'vAlign' => 'vcenter'));
            if ($arrOptions['setborder']) { $objFormat->setBorder(1); $objFormat->setBorderColor('black'); }

            switch($strKey)
            {
                case 'string': $objFormat->setAlign('left'); break;
                case 'float': $objFormat->setNumFormat('#,##0.00;-#,##0.00'); break;
                case 'float_percent': $objFormat->setNumFormat('#,##0.00 %;-#,##0.00 %'); break;
                case 'float_euro': $objFormat->setNumFormat('#,##0.00 ;-#,##0.00 '); break;
                case 'integer': $objFormat->setNumFormat('#,##0;-#,##0'); break;
                case 'integer_percent': $objFormat->setNumFormat('#,##0 %;-#,##0 %'); break;
                case 'integer_euro': $objFormat->setNumFormat('#,##0 ;-#,##0 '); break;
                case 'date': $objFormat->setNumFormat('DD/MM/YYYY'); break;
                case 'datetime' : $objFormat->setNumFormat('DD/MM/YYYY HH:MM:SS'); break;
            }
        }
        unset($objFormat);

        // Création d'une feuille de données
        $objWorkSheet = $objWorkBook->addWorksheet($strSheetName);
        if ($arrOptions['fitpage_width'] || $arrOptions['fitpage_height']) $objWorkSheet->fitToPages($arrOptions['fitpage_width'] ? 1 : 0, $arrOptions['fitpage_height'] ? 1 : 0);
        if ($arrOptions['landscape']) $objWorkSheet->setLandscape();

        if (!empty($this->_arrData))
        {
            // Définition des formats de colonnes
            if (!empty($arrDataFormats))
            {
                $intCol = 0;
                foreach(array_keys(reset($this->_arrData)) as $strKey)
                {
                    if (isset($arrDataFormats[$strKey]['width'])) $objWorkSheet->setColumn($intCol, $intCol, $arrDataFormats[$strKey]['width']);
                    $intCol++;
                }
            }

            // Ajout de la ligne d'entête
            if ($booHeader)
            {
                $intCol = 0;
                foreach(array_keys(reset($this->_arrData)) as $strKey) $objWorkSheet->writeString(0, $intCol++, isset($arrDataFormats[$strKey]['title']) ? $arrDataFormats[$strKey]['title'] : $strKey, $objFormatTitle);
            }
            // Traitement des contenus
            $intLine = 1;
            foreach($this->_arrData as $row)
            {
                $intCol = 0;
                foreach($row as $strKey => $strValue)
                {
                    if (empty($arrDataFormats[$strKey]['type'])) $arrDataFormats[$strKey]['type'] = 'string';

                    // On vérifie si un format de donné est proposé pour le champ
                    $objFormat = (!empty($arrDataFormats[$strKey]['type']) && !empty($arrFormats[$arrDataFormats[$strKey]['type']])) ? $arrFormats[$arrDataFormats[$strKey]['type']] : $objFormatDefault;

                    switch($arrDataFormats[$strKey]['type'])
                    {
                        case 'float':
                        case 'float_percent':
                        case 'float_euro':
                        case 'integer':
                        case 'integer_percent':
                        case 'integer_euro':
                            $objWorkSheet->writeNumber($intLine, $intCol++, $strValue, $objFormat);
                        break;

                        default:
                            $objWorkSheet->writeString($intLine, $intCol++, $strValue, $objFormat);
                        break;
                    }
                }
                $intLine++;
            }
        }

        // fermeture du document
        $objWorkBook->close();

        if (!$arrOptions['send'] && empty($arrOptions['file']))
        {
            $strFileContent = file_get_contents($strFileName);
            unlink($strFileName);
            return $strFileContent;
        }

        return void;
    }


    private static function _linkPage($intPage, $strPage, $strUrlMask, $intPageSel = 0)
    {
        return $intPageSel == $intPage ? str_replace('{p}', $strPage, '<strong>{p}</strong>') : str_replace('{p}', $strPage, '<a href="'.$strUrlMask.'">{p}</a>');
    }

    public static function getPages($intNumRows, $intMaxLines = 50, $strUrlMask = '?page={p}', $intPageSel = 1)
    {
        $arrPages = array();

        // Affichage des pages (optionnel)
        if ($intMaxLines > 0 && $intMaxLines < $intNumRows)
        {
            $intNumPages = ceil($intNumRows / $intMaxLines);

            // Fleche page précédente
            if ($intPageSel > 1) $arrPages[] = self::_linkPage($intPageSel-1, '&laquo;', $strUrlMask);

            // On affiche toujours la premiere page
            $arrPages[] = self::_linkPage(1, 1, $strUrlMask, $intPageSel);

            // Affichage "..." après première page
            if ($intPageSel > 4) $arrPages[] = '...';

            // Boucle sur les pages autour de la page sélectionnée (-2 à +2 si existe)
            for ($i = $intPageSel - 2; $i <= $intPageSel + 2; $i++)
            {
                if ($i>1 && $i<$intNumPages) $arrPages[] = self::_linkPage($i, $i, $strUrlMask, $intPageSel);
            }

            // Affichage "..." avant dernière page
            if ($intPageSel < $intNumPages - 3) $arrPages[] = '...';

            // Dernière page
            if ($intNumPages>1) $arrPages[] = self::_linkPage($intNumPages, $intNumPages, $strUrlMask, $intPageSel);

            // Fleche page suivante
            if ($intPageSel < $intNumPages) $arrPages[] = ploopi_array_page($intPageSel+1, '&raquo;', $strUrlMask);
        }

        return implode(' ', $arrPages);
    }

    /**
     * Affichage du contenu du tableau
     */
    public function printR()
    {
        Core\Output::printR($this->_arrData);
    }


}
