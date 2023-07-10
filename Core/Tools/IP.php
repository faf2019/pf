<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Méthodes de manipulation d'IPs
 *
 * @package pf
 * @subpackage ip
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class IP
{
     /**
     * Retourne un tableau d'IP pour le client.
     * On peut en effet obtenir plusieurs IP pour un même client, notamment s'il passe par un proxy.
     *
     * @param boolean $wan_only true si l'on ne veut que les adresses WAN (false par défaut)
     * @return array tableau d'IP
     */

    static public function get($booWanOnly = false)
    {
        $arrIp = array();

        if (getenv("HTTP_CLIENT_IP")) $strIp = getenv("HTTP_CLIENT_IP");
        elseif(getenv("HTTP_X_FORWARDED_FOR")) $strIp = getenv("HTTP_X_FORWARDED_FOR");
        else $strIp = getenv("REMOTE_ADDR");

        $arrIpList = explode(',', $strIp);

        foreach($arrIpList as $strIp)
        {
            $intIp = sprintf("%u",ip2long($strIp));
            if (preg_match("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/", $strIp) && $intIp != sprintf("%u",ip2long('255.255.255.255')))
            {
                if (!$booWanOnly || (
                            (sprintf("%u",ip2long('10.0.0.0')) <= $intIp && $intIp <= sprintf("%u",ip2long('10.255.255.255')))
                        ||  (sprintf("%u",ip2long('172.16.0.0')) <= $intIp && $intIp <= sprintf("%u",ip2long('172.31.255.255')))
                        ||  (sprintf("%u",ip2long('192.168.0.0')) <= $intIp && $intIp <= sprintf("%u",ip2long('192.168.255.255')))
                        ||  (sprintf("%u",ip2long('169.254.0.0')) <= $intIp && $intIp <= sprintf("%u",ip2long('169.254.255.255')))
                )) {
                    $arrIp[] = $strIp;
                }
            }
        }

        return $arrIp;
    }

    /**
     * Convertit une liste de range d'IP en liste de règles facilement exploitables (à base d'entiers)
     *
     * @param string $rules ranges d'IP
     * @return array tableau de règles
     */

    static public function getRules($strRules)
    {
        $arrIntervals = array();
        $arrIpRules = array();

        if ($strRules == '') return false;

        $arrIntervals = explode(';', $strRules);

        foreach($arrIntervals as $arrInterval)
        {
            $arrIp = explode('-',trim($arrInterval));

            $strIp1 = '';
            $strIp2 = '';

            if (count($arrIp) == 1)
            {
                $arrIp[0] = trim($arrIp[0]);
                if (strpos($arrIp[0],"*") !== false)
                {
                    $strIp1 = str_replace('*','0',$arrIp[0]);
                    $strIp2 = str_replace('*','255',$arrIp[0]);
                }
                else
                {
                    $strIp1 = $strIp2 = $arrIp[0];
                }
            }
            elseif (count($arrIp) == 2)
            {
                $strIp1 = trim($arrIp[0]);
                $strIp2 = trim($arrIp[1]);
            }

            $arrIpRules[ip2long($strIp1)] = ip2long($strIp2);
        }

        return $arrIpRules;
    }

    /**
     * Indique si l'IP du client fait partie du range d'IP fourni
     *
     * @param array $iprules tableau de range d'ip (fourni par ploopi_getiprules)
     * @return boolean true si l'IP est incluse dans le range d'IP fourni
     */

    public static function isValid($arrIpRules)
    {
        $booIpIsOk = false;

        if ($arrIpRules)
        {
            $arrUserIp = ploopiIP::get();

            if (!empty($arrUserip)) $intUserIp = $arrUserip[0];

            foreach($arrIpRules as $intStartIp => $intEndIp)
            {
                if ($intUserIp >= $intStartIp && $intUserIp <= $intEndIp) $booIpIsOk = true;
            }
        }
        else $booIpIsOk = true;

        return $booIpIsOk;
    }
}
