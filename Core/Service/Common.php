<?php
namespace ovensia\pf\Core\Service;

use ovensia\pf\Core\Builders;
use ovensia\pf\Core\Exception;

/**
 * Gestion des services lancés par le FrontController.
 * Le service est un singleton
 * Il peut être démarré ou stoppé.
 * @author ovensia
 *
 */
abstract class Common implements Model
{
    use Builders\Singleton;
    use Controller;

    /**
     * @var boolean
     */
    private $_booStarted;

    /**
     * Empêche la création directe de l'objet
     */
    protected function __construct()
    {
        $this->_booStarted = false;
        return $this;
    }

    public function isStarted() { return $this->_booStarted; }

    public function start()
    {
        if ($this->isStarted()) throw new Exception(get_called_class().' already started');

        $this->_booStarted = true;
        return $this;
    }

    public function stop() { $this->_booStarted = false; return $this; }
}
