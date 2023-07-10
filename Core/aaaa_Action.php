<?php
namespace ovensia\pf\Core;

abstract class Action
{
    protected $_controller;

    public function __construct($controller)
    {
        $this->_controller = $controller;
    }

    abstract public function launch(Request $request, Response $response);

    public function render($file)
    {
        $this->_controller->render($file);
    }

    public function printOut()
    {
        $this->_controller->getResponse()->printOut();
    }

    protected function _forward($module, $action)
    {
        $this->_controller->forward($module, $action);
    }

    protected function _redirect($url)
    {
        $this->_controller->redirect($url);
    }
}
