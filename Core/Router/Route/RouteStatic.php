<?php
namespace ovensia\pf\Core\Router\Route;

class RouteStatic extends Common implements Model
{
    public function check($strPath)
    {
        if ($strPath == $this->getPattern()) return $this->getStaticVariables();
        else return false;
    }

    public function rewrite($arrVariables = array(), $arrParams = array(), $arrOptions = array())
    {
        $arrOptions = array_merge($this->getOptions(), $arrOptions);

        // Réécriture de l'url
        return $this->getPattern().$this->_addParams($arrParams, isset($arrOptions['restful']) ? $arrOptions['restful'] : false);
    }
}
