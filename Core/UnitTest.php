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

namespace ovensia\pf\Core;

class UnitTest
{
    private static $_arrTest;
    
    public static function saveResult($strLabel, $booResult)
    {
        if (!isset(self::$_arrTest)) self::$_arrTest = array();
        
        self::$_arrTest[] = array(
            'strLabel' => $strLabel, 
            'booResult' => $booResult
        );
    }
    
    public static function output()
    {
        echo '<table style="border:1px solid #000;border-collapse:collapse;">';
        echo '<tr><th style="border:1px solid #000;padding:2px;">Test</th><th style="border:1px solid #000;padding:2px;">Résultat</th></tr>';
        foreach(self::$_arrTest as $test)
        {
            echo '<tr><td style="border:1px solid #000;padding:2px;">'.htmlentities($test['strLabel']).'</td><td style="border:1px solid #000;padding:2px;">'.($test['booResult'] ? 'ok' : 'error').'</td></tr>';
        }
        echo '</table>';
    }
}