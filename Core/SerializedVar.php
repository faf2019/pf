<?php
/*
    Copyright (c) 2009 Ovensia
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

/**
 * Classe de gestion des variables sérialisées
 */
class serializedvar
{

    private $strId = '';
    
    public static function get_path() { return ploopi_session::get_path()._SEP.'sv'; }
    
    public static function generateId() { return uniqid(mt_rand(), true); }
    
    public function __construct($strId) { $this->strId = md5($strId); }
     
    public function read()
    {
        if (ploopi_session::get_usedb())
        {
            return (ploopi_session::get_db()->query("SELECT `data` FROM `ploopi_serializedvar` WHERE `id` = '".ploopi_session::get_db()->addslashes($this->strId)."' AND `id_session` = '".ploopi_session::get_db()->addslashes(session_id())."'") && $arrRecord = ploopi_session::get_db()->fetchrow()) ? unserialize(gzuncompress($arrRecord['data'])) : false;
        }
        else
        {
            return file_exists(self::get_path()._SEP.$this->strId) ? unserialize(gzuncompress(file_get_contents(self::get_path()._SEP.$this->strId))) : false;
        }
    }
    
    public function save(&$data)
    {
        if (ploopi_session::get_usedb())
        {
            ploopi_session::get_db()->query("REPLACE INTO `ploopi_serializedvar` VALUES ('".ploopi_session::get_db()->addslashes($this->strId)."', '".ploopi_session::get_db()->addslashes(session_id())."', '".ploopi_session::get_db()->addslashes(gzcompress(serialize($data)))."')");
        }
        else
        {
            ploopi_makedir(self::get_path());
            $resHandle = fopen(self::get_path()._SEP.$this->strId, 'wb');
            fwrite($resHandle, gzcompress(serialize($data)));
            fclose($resHandle);
        }
        return true;
    }

    public function destroy()
    {
        if (ploopi_session::get_usedb())
        {
            ploopi_session::get_db()->query("DELETE FROM `ploopi_serializedvar` WHERE `id` = '".ploopi_session::get_db()->addslashes($this->strId)."' AND `id_session` = '".ploopi_session::get_db()->addslashes(session_id())."'");
        }
        else
        {
            if (file_exists(self::get_path()._SEP.$this->strId)) unlink(self::get_path()._SEP.$this->strId); 
        }
        return true;
    }
    
}
