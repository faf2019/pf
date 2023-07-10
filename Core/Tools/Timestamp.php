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
 * Gestion des timestamps Ploopi
 * En utilisant "Fluent Interface"
 *
 * @package pf
 * @subpackage timestamp
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

namespace ovensia\pf\Core\Tools;

use \ovensia\pf\Core\Exception;

/**
 * Classe de gestion des timestamps
 *
 * @package pf
 * @subpackage timestamp
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class Timestamp
{
    use \ovensia\pf\Core\Builders\Factory;

    /**
     * Format du timestamp
     */

    Const _FORMAT = 'YmdHis';

    /**
     * Expression rationnelle du format (interne)
     */

    Const _PREG_FORMAT = '/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/';

    /**
     * Expression rationnelle du format de date locale (FR)
     */

    const _PREG_FORMAT_DATE_FR = '/^([0-9]{1,2})[-,\/,.]([0-9]{1,2})[-,\/,.]([0-9]{2,4})$/';

    /**
     * Expression rationnelle du format d'heure locale (FR)
     */

    const _PREG_FORMAT_TIME_FR = '/^([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$/';

    /**
     * Le timestamp
     *
     * @var string
     */

    private $strTimeStamp;

    /**
     * Constructeur de la classe
     *
     * @return ploopi_timestamp
     */

    public function __construct() { $this->setNow(); }

    /**
     * Retourne le timestamp brut
     *
     * @return string timestamp
     */

    public function get() { return $this->strTimeStamp; }

    /**
     * Définit une nouvelle valeur pour le timestamp
     *
     * @param string $strTs le nouveau timestamp
     * @return Timestamp l'objet (fluent)
     */

    public function set($strTs)
    {
        preg_match(self::_PREG_FORMAT, $this->strTimeStamp, $arrMatches);

        if (is_numeric($strTs) && strlen($strTs) == 14) $this->strTimeStamp = $strTs;
        else throw new Exception("Invalid timestamp format for &laquo; {$strTs} &raquo;");

        return $this;
    }

    public function setDetail($intYear, $intMonth = 1, $intDay = 1, $intHour = 0, $intMinute = 0, $intSecond = 0)
    {
        $this->strTimeStamp = sprintf('%04d%02d%02d%02d%02%02d', $intYear, $intMonth, $intDay, $intHour, $intMinute, $intSecond);

        return $this;
    }

    /**
     * Définit le timestamp à la date/heure du jour
     *
     * @return Timestamp l'objet (fluent)
     */

    public function setNow() { $this->strTimeStamp = date(self::_FORMAT); return $this; }

    /**
     * Ajoute une durée au timestamp
     *
     * @param int $intH nombre d'heure à ajouter
     * @param int $intMn nombre de minute à ajouter
     * @param int $intS nombre de seconde à ajouter
     * @param int $intM nombre de mois à ajouter
     * @param int $intD nombre de jour à ajouter
     * @param int $intY nombre d'année à ajouter
     * @return Timestamp l'objet (fluent)
     */

    public function add($intH = 0, $intMn = 0, $intS = 0, $intM = 0, $intD = 0, $intY = 0)
    {
        $arrTs = $this->getDetails();

        $this->strTimeStamp = date(
            self::_FORMAT,
            mktime(
                $arrTs[3] + $intH,
                $arrTs[4] + $intMn,
                $arrTs[5] + $intS,
                $arrTs[1] + $intM,
                $arrTs[2] + $intD,
                $arrTs[0] + $intY
            )
        );

        return $this;
    }

    /**
     * Retourne un tableau indexé contenant le détail du timestamp
     * 0 => année, 1 => mois, 2 => jour, 3 => heure, 4 => minute, 5 => seconde
     *
     * @return array
     */

    public function getDetails()
    {
        return array(
            $this->getYear(),
            $this->getMonth(),
            $this->getDay(),
            $this->getHour(),
            $this->getMinute(),
            $this->getSecond(),
        );
    }

    /**
     * Retourne l'année du timestamp
     *
     * @return int année
     */

    public function getYear() { return intval(substr($this->strTimeStamp, 0, 4)); }

    /**
     * Retourne le mois du timestamp
     *
     * @return int mois
     */

    public function getMonth() { return intval(substr($this->strTimeStamp, 4, 2)); }

    /**
     * Retourne le jour du timestamp
     *
     * @return int jour
     */

    public function getDay() { return intval(substr($this->strTimeStamp, 6, 2)); }

    /**
     * Retourne l'heure du timestamp
     *
     * @return int heure
     */

    public function getHour() { return intval(substr($this->strTimeStamp, 8, 2)); }

    /**
     * Retourne la minute du timestamp
     *
     * @return int minute
     */

    public function getMinute() { return intval(substr($this->strTimeStamp, 10, 2)); }

    /**
     * Retourne la seconde du timestamp
     *
     * @return int seconde
     */

    public function getSecond() { return intval(substr($this->strTimeStamp, 12, 2)); }


    public function toLocal()
    {
        $arrLocal = array('date' => '', 'time' => '');

        // timestamp => array
        $tsArray = $this->getDetails();

        // construction du tableau de résultat
        $arrLocal['date'] = sprintf('%02d/%02d/%04d', $tsArray[2], $tsArray[1], $tsArray[0]);
        $arrLocal['time'] = sprintf('%02d:%02d:%02d', $tsArray[3], $tsArray[4], $tsArray[5]);

        return $arrLocal;
    }

    public function fromLocal($strDate, $strTime = '00:00:00')
    {

        if (preg_match(_PREG_FORMAT_DATE_FR, $strDate, $arrRegsDate) === 1 && preg_match(_PREG_FORMAT_TIME_FR, $strTime, $arrRegsTime) === 1)
        {
            if ($arrRegsDate[3]<100) $arrRegsDate[3]+=2000;
            $this->set($arrRegsDate[2], $arrRegsDate[1], $arrRegsDate[0], $arrRegsTime[0], $arrRegsTime[1], $arrRegsTime[2]);
        }
        else throw new Exception("Invalid date or time format");

        return $this;
    }

    public function toUnix()
    {
        // timestamp => array
        $tsArray = $this->getDetails();

        // conversion timestamp unix
        return(mktime(
            $tsArray[3],
            $tsArray[4],
            $tsArray[5],
            $tsArray[1],
            $tsArray[2],
            $tsArray[0]
        ));
    }


    /**
     * Méthode magique __toString
     *
     * @return string timestamp brut
     */

    public function __toString() { return strval($this->get()); }

    /**
     * Vérifie le format de la date en fonction du format local
     *
     * @param string $strDate date à vérifier
     * @return boolean true si le format de la date est valide
     */

    private function _dateVerify($strDate)
    {
        return preg_match(_PREG_FORMAT_DATE_FR, $strDate, $arrRegs) === 1;
    }

    private function _timeVerify($strTime)
    {
        return preg_match(_PREG_FORMAT_TIME_FR, $strTime, $arrRegs) === 1;
    }
}
