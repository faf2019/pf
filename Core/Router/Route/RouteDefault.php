<?php
namespace ovensia\pf\Core\Router\Route;

use ovensia\pf\Core\Builders;

class RouteDefault extends Common implements Model
{
    use Builders\Factory;

    private $_arrStaticVariables;
    private $_strPattern;
    private $_arrFormats;

    public function check($strPath)
    {
        // Toujours valide
        return $this->getStaticVariables();
    }


    public function rewrite($arrVariables = array(), $arrParams = array(), $arrOptions = array())
    {
        // Pas une vraie route...
        return '';
    }

}
