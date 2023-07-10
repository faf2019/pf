<?php
/*
    Copyright (c) 2007-2010 Ovensia
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

namespace ovensia\pf\Core\Module;

use ovensia\pf\Core;
use ovensia\pf\Core\Builders\Factory;
use ovensia\pf\Core\Service\Controller;

/**
 Nécessité de séparer pour chaque "entité"
 - ActiveRecord (Model)
 - View
 - Controler
*/

abstract class Common
{
    use Factory;
    use Controller;

    /**
     * Lien vers le modèle (bdd)
     * @var ploopiModuleDO
     */
    private $_objModuleDO;

    /**
     * Contenu du menu
     * @var array
     */
    private $_arrBlockContent;

    /**
     * Tableau des paramètres du module
     * @var array
     */
    private $_arrParams;

    private $_strClassName;

    private $_strModuleType;

    private $_intIdModule;


    /**
     * Contrôleur par défaut du module (peut être surchargé).
     * Appelé depuis ploopiKernel::_processOp()
     * @param $strOp opération demandée
     */
    static public function processOp($strOp)
    {
        // Récupération du nom de la classe appelante
        $strStaticClassName = get_called_class();

        // Détermination du type de module
        $strModuleType = Tools::convertClassToType($strStaticClassName);


        // traitement de l'action, on vérifie le préfixe (type du module)
        if (substr($strOp, 0, strlen($strModuleType)) == $strModuleType)
        {
            // Chemin physique du fichier "controleur" principal (Ploopi)
            $strControlerFilePath = _DIRNAME."/modules/{$strModuleType}/controlers/{$strOp}.php";

            if (file_exists($strControlerFilePath)) include_once $strControlerFilePath;
            else throw new Core\Exception("Unknown controler '{$strOp}'");
        }


    }


    /**
     * Instancie un module en vérifiant que le type et l'identifiant concordent
     * @param int $intIdModule identifiant du module
     * @throws Core\ploopiException
     */
    final public function __construct($intIdModule)
    {
        // Récupération du nom de la classe appelante
        $this->_strClassName = get_class($this);

        // On vérifie que l'objet implémente l'interface ploopiModuleInterface
        $objReflection = new \ReflectionClass($this->_strClassName);
        if (!$objReflection->implementsInterface(__NAMESPACE__.'\Model')) throw new Core\Exception("Class '{$this->_strClassName}' does not implements 'Model'");

        // On vérifie l'existence du module
        if (!Core\Service\Controller::getService('session')->exists("modules/{$intIdModule}")) throw new Core\Exception("Unknown module id");

        $this->_intIdModule = $intIdModule;

        // Détermination du type de module
        $this->_strModuleType = Manager::convertClassToType($this->_strClassName);

        // On vérifie le type du module
        // if ($this->type != $this->_strModuleType) throw new Core\ploopiException("Bad module id");

        $this->_objModuleDO = null;

        $this->_arrBlockContent = array();

        $this->initBlock();
    }


    /**
     * Accesseur magique pour les propriétés de l'objet (lues dans la session)
     * @param string $strPropertyName nom de la propriété
     */
    public function __get($strPropertyName) { return $this->getValue($strPropertyName); }

    /**
     * Retourne une propriété de l'objet parmi (id, label, active, visible, etc... voir le contenu de 'modules' dans la session)
     * @param string $strPropertyName nom de la propriété
     * @throws Core\ploopiException
     */
    public function getValue($strPropertyName)
    {
        if (!Core\Service\Controller::getService('session')->exists("modules/{$this->_intIdModule}/{$strPropertyName}"))
            throw new Core\Exception("Unkown module property '{$strPropertyName}'");
        return Core\Service\Controller::getService('session')->get("modules/{$this->_intIdModule}/{$strPropertyName}");
    }

    /**
     * Ajoute un menu à un block
     *
     * @param string $label intitulé du menu
     * @param string $url adresse du lien vers le menu
     * @param boolean $selected true si le menu est sélectionné (optionnel, false par défaut)
     * @param string $target cible du lien (optionnel, par défaut vide)
     */

    final protected function addBlockMenu($strLabel, $strUrl, $booSelected = false, $strTarget = '')
    {
        $this->_arrBlockContent[] = array(
            'type' => 'menu',
            'content' => array(
                'label' => $strLabel,
                'cleaned_label' => htmlentities(trim(str_replace('&nbsp;', ' ', strip_tags($strLabel)))),
                'url' => $strUrl,
                'selected' => $booSelected,
                'target' => $strTarget
            )
        );
    }

    /**
     * Ajoute du contenu à un block
     *
     * @param string $content contenu html à ajouter au bloc
     */

    final protected function addBlockContent($strContent) { $this->_arrBlockContent[] = array('type' => 'content', 'content' => $strContent); }

    /**
     * Retourne le contenu des blocs de menu (pour affichage ?)
     * @return array
     */
    final public function getBlockContent() { return $this->_arrBlockContent; }


    /**
     * Retourne le DataObject associé
     */
    final public function getDO()
    {
        if (is_null($this->_objModuleDO))
        {
            try {
                return $this->_objModuleDO = ploopiModuleDO::getInstance()->open($this->_intIdModule);
            }
            catch (Core\ploopiException $e) { }
        }

        return $this->_objModuleDO;
    }


    /*
    final public function getParams()
    {
        // Tableau des paramètres de modules
        $arrParams = array();

        // Chargement des paramètres par défaut
        $objRs = Select::getInstance()
            ->addSelect('
                pd.id_module,
                pt.name,
                pt.label,
                pd.value
            ')
            ->addFrom('ploopi_param_default pd')
            ->addInnerJoin('ploopi_param_type pt ON pt.name = pd.name AND pt.id_module_type = pd.id_module_type')
            ->execute();

        while ($row = $objRs->fetchRow()) $arrParams[$row['id_module']]['default'][$row['name']] = $row['value'];
    }
    */

}

