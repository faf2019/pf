<?php
namespace ovensia\pf\Core\Router\Route;

interface Model
{
    public function __construct($strPattern, $arrStaticVariables = null, $arrFormats = null, $arrOptions = null);
    public function check($strPath);
    public function rewrite($arrVariables = null, $arrParams = null, $arrOptions = null);

}
