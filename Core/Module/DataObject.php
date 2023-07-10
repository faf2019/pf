<?php
namespace ovensia\pf\Core\Module;

use ovensia\pf\Core\DataObject;

abstract class DataObject extends DataObject\DataObject
{
    public function __construct() { parent::__construct('ploopi_module', 'id'); }
}
