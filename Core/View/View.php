<?php
namespace ovensia\pf\Core\View;

use ovensia\pf;
use ovensia\pf\Core;
use ovensia\pf\Core\Tools;

class View
{
    use Core\Builders\Factory;
    use Core\Service\Controller;

    private $_strFilePath;
    private $_strApplication;
    private $_strController;
    private $_strAction;
    private $_strTemplate;

    /**
     * Constructeur de la vue
     * @throws Core\ploopiException
     * @param  $strFilePath chemin vers le fichier de la vue
     */
    public function __construct($strApplication, $strController, $strAction, $strTemplate = null)
    {
        $this->_strApplication = $strApplication;
        $this->_strController = $strController;
        $this->_strAction = $strAction;
        $this->_strTemplate = $strTemplate;

        $strFilePath = pf\DIRNAME."/Applications/".
            implode('/', array_map('ucfirst', preg_split('@[,/]@', $this->_strApplication)))."/Views/".
            (empty($this->_strTemplate) ? '' : implode('/', array_map('ucfirst', preg_split('@[,/]@', $this->_strTemplate))).'/').
            (empty($this->_strController) ? '' : implode('/', array_map('ucfirst', preg_split('@[,/_]@', $this->_strController))).'/').
            implode('/', array_map('ucfirst', preg_split('@[,/]@', $this->_strAction))).'.php';

        if (!file_exists($strFilePath)) throw new Exception\ViewException("Can't open view file '{$strFilePath}'");

        $this->_strFilePath = $strFilePath;
    }

    /**
     * Rendu de la vue
     * @param ArrayObject $objAssigns variables à transmettre à la vue
     * @return string contenu HTML généré
     */
    public function render(Tools\ArrayObject $objAssigns)
    {
        // On affecte les variables à la vue
        foreach($objAssigns->getIterator() as $strKey => $strValue) {
            $this->{$strKey} = $strValue;
            //${$strKey} = &$this->{$strKey};
        }

        ob_start();
        include $this->_strFilePath;
        $strContent = ob_get_contents();
        ob_end_clean();

        return $strContent;
    }

    /**
     * Retourne le nom de la vue
     */
    public function getName() {
        return $this->_strAction;
    }

    /**
     * Permet d'intégrer une autre vue depuis la vue courante
     */
    private function _includeView($strAction, $strController = null) {
        if ($strController == null) $strController = $this->_strController;

        $strFilePath = pf\DIRNAME."/Applications/".
            implode('/', array_map('ucfirst', preg_split('@[,/]@', $this->_strApplication)))."/Views/".
            (empty($this->_strTemplate) ? '' : implode('/', array_map('ucfirst', preg_split('@[,/]@', $this->_strTemplate))).'/').
            (empty($strController) ? '' : implode('/', array_map('ucfirst', preg_split('@[,/_]@', $strController))).'/').
            implode('/', array_map('ucfirst', preg_split('@[,/]@', $strAction))).'.php';

        if (!file_exists($strFilePath)) throw new Exception\ViewException("Can't open view file '{$strFilePath}'");

        include $strFilePath;
    }

    protected function getFile() { return $this->_strFilePath; }
}
