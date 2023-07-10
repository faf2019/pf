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

namespace ovensia\pf\Core\Skin;

use ovensia\pf as home;

/**
 * Gestion de l'affichage
 *
 * @package pf
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Regroupe des méthodes génériques pour afficher bloc, menus, onglets, barre d'outils, popups, etc...
 *
 * @package pf
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class Common
{
    private $_strTemplateName;

    private $_strTemplatePath;

    private $_strCachePath;

    private $_objTemplate;

    /**
     * Constructeur de la classe ploopiSkin
     *
     * @param string $strSkinName nom du skin (nom du dossier)
     * @return ploopiSkin
     */

    final private function __construct($strTemplateName, $strTemplatePath, $strCachePath)
    {
        require_once home\DIRNAME.'/Libs/phpbb3/template.php';

        $this->_strTemplateName = $strTemplateName;
        $this->_strTemplatePath = $strTemplatePath;
        $this->_strCachePath = $strCachePath;

        $this->_objTemplate = new \template($this->_strTemplatePath, $this->_strCachePath);
        $this->_objTemplate->set_filenames(array('body' => 'index.html'));
    }

    public function getTemplateName() { return $this->_strTemplateName; }

    public function getTemplatePath() { return $this->_strTemplatePath; }

    public function getTemplate() { return $this->_objTemplate; }

    /**
     * Assigne une variable au template
     * @throws Core\ploopiException
     * @param mixed $mixedVar
     * @param string $strName
     * @param string $strPrefix
     * @return void
     */
    public function setTemplateVar($mixedVar, $strName = null, $strPrefix = null)
    {
        if (is_array($mixedVar))
        {
            $arrValues = array();
            foreach($mixedVar as $strKey => $mixedValue)
            {
                if (is_array($mixedValue) || is_object($mixedValue)) $this->setTemplateVar($mixedValue, $strName.'.'.strtolower($strKey));
                else $arrValues[strtoupper((is_null($strPrefix) ? '' : $strPrefix).$strKey)] = $mixedValue;
            }
            //if (!empty($arrValues))
            //{
                if (is_null($strName)) $this->_objTemplate->assign_vars($arrValues);
                else $this->_objTemplate->assign_block_vars($strName, $arrValues);
            //}
        }
        else
        {
            if (is_null($strName)) throw new Core\ploopiException('A single var need a name');
            else $this->_objTemplate->assign_var(strtoupper($strName), $mixedVar);
        }
    }

    public function displayPageTitle($strTitle, $booReturn = false)
    {
        $strHtml = "<div class=\"ploopiPageTitle\"><p>{$strTitle}</p></div>";

        if ($booReturn) return $strHtml;
        else echo $strHtml;
    }

    public function displayIconBar() {}

    public function displayTabBar() {}

    public function openBlock($strTitle = null, $booReturn = false)
    {
        $strHtml = '<div class="ploopiBlock">';
        if (!is_null($strTitle)) $strHtml .= "<div class=\"ploopiBlockTitle\">{$strTitle}</div>";
        $strHtml .= '<div class="ploopiBlockContent">';

        if ($booReturn) return $strHtml;
        else echo $strHtml;

    }

    public function closeBlock($booReturn = false)
    {
        $strHtml = '</div></div>';

        if ($booReturn) return $strHtml;
        else echo $strHtml;
    }



    /**
     * Crée une barre d'outils (icones)
     *
     * @param array $icons tableau associatif d'icones (propriétés : title, url, icon, width, confirm, javascript)
     * @param string $iconsel clé de l'icone sélectionnée (par référence), sélectionne par défaut la première icone
     * @return string code html de la barre d'outils
     */

    public function displayToolBar($arrIcons, &$strIconSel, $rowOptions = array())
    {
        $rowDefaultOptions = array(
            'class' => 'toolbar',
            'return' => false
        );

        $rowOptions = array_merge($rowDefaultOptions, $rowOptions);

        if (!isset($arrIcons[$strIconSel])) $strIconSel = null;

        $strHtml = '<div class="ploopiToolBar">';

        foreach($arrIcons as $strKey => $row) {
            $strHtml .= $this->_displayToolBarIcon($row, $strKey == $strIconSel);
        }

        $strHtml .= '</div>';

        if ($rowOptions['return']) return $strHtml;
        else echo $strHtml;
    }

    /**
     * Crée une icone pour la barre d'outils
     *
     * @param array $icon icone à afficher (propriétés : title, url, icon, width, confirm, javascript)
     * @param boolean $sel true si l'icone est sélectionnée
     * @return string code html de l'icone
     */

    public function _displayToolBarIcon($rowIcon, $booSelected = false)
    {
        $rowDefaultIcon = array(
            'title' => '',
            'url' => '',
            'icon' => '',
            'onclick' => '',
            'style' => '',
            'class' => '',
        );

        $rowIcon = array_merge($rowDefaultIcon, $rowIcon);

        $strHrefTitle = htmlentities(strip_tags($rowIcon['title']));
        $strUrl = $rowIcon['url'];
        $strOnclick = '';

        if (!empty($rowIcon['onclick'])) {
            $strOnclick = "onclick=\"javascript:{$rowIcon['onclick']}\"";
            if (empty($strUrl)) $strUrl = 'javascript:void(0);';
        }

        return $strHtml = "
            <a class=\"{$rowIcon['class']}\" style=\"{$rowIcon['style']}\" href=\"{$strUrl}\" {$strOnclick} title=\"{$strHrefTitle}\">
                <img src=\"{$rowIcon['icon']}\" />
                <span>{$rowIcon['title']}</span>
            </a>
        ";
    }














    /**
     * Créé un haut de bloc
     *
     * @param string $title titre du bloc
     * @param string $style styles optionnels du bloc
     * @param string $styletitle styles optionnels du titre
     * @param srting $additionnal_title titre additionnel
     * @return string code html de l'entête du bloc
     */

    public function open_simplebloc($title = '', $style = '', $styletitle = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"simplebloc\" style=\"{$style}\">";
        else $res = "<div class=\"simplebloc\">";

        if ($title!=null) $res .= "<div class=\"simplebloc_title\" id=\"handle\" style=\"{$styletitle}\"><div class=\"simplebloc_titleleft\">{$additionnal_title}{$title}</div></div>";

        $res .= '<div class="simplebloc_content">';

        return $res;
    }

    /**
     * Créé un bas de bloc (ferme le dernier bloc ouvert)
     *
     * @return string code html du pied du bloc
     */

    function close_simplebloc()
    {
        return '<div style="clear:both;"></div></div><div class="simplebloc_footer"></div></div>';
    }

    /**
     * Crée un titre de page
     *
     * @param string $title titre de la page
     * @param string $style styles optionnels
     * @return string code html du titre
     */

    public function create_pagetitle($title, $style = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"pagetitle\" style=\"{$style}\"><p>{$additionnal_title}</p>{$title}</div>";
        else $res = "<div class=\"pagetitle\"><p>{$additionnal_title}</p>{$title}</div>";

        return $res;
    }


    /**
     * Crée une barre d'onglets
     *
     * @param array $tabs tableau associatif d'onglets (propriétés : title, url, width)
     * @param string $tabsel clé de l'onglet sélectionné (par référence), sélectionne par défaut le premier onglet
     * @return string code html de la barre d'onglets
     */

    public function create_tabs($tabs, &$tabsel)
    {

        $res = "<div class=\"tabs\">";

        if (!isset($tabs[$tabsel])) $tabsel = -1;

        foreach($tabs AS $key => $value)
        {
            if ($tabsel == -1) $tabsel = $key;
            $res .= $this->create_tab($tabs[$key], ($tabsel==$key));
        }

        $res .= "</div>";

        return $res;
    }

    /**
     * Crée un onglet
     *
     * @param array $tab onglet (propriétés : title, url, width)
     * @param boolean $sel true si l'onglet est sélectionné
     * @return string code html de l'onglet
     */

    public function create_tab($tab, $sel)
    {
        if (!empty($tab['width'])) $style = "style=\"width:{$tab['width']}px;\"";
        else $style = '';

        $strHrefTitle = htmlentities(strip_tags($tab['title']));

        if ($sel) $res = "<a href=\"".ploopi_urlencode($tab['url'])."\" title=\"Accéder à l'onglet &laquo; {$strHrefTitle} &raquo;\"  class=\"selected\" {$style}>{$tab['title']}</a>";
        else  $res = "<a href=\"".ploopi_urlencode($tab['url'])."\" title=\"Accéder à l'onglet &laquo; {$strHrefTitle} &raquo;\"  {$style}>{$tab['title']}</a>";
        return $res;
    }

    /**
     * Crée un faux popup (div)
     *
     * @param string $title titre du popup
     * @param string $content contenu du popup (html)
     * @param string $popupid id du popup (propriété html id)
     * @return string code html du popup
     */

    function create_popup($title, $content, $popupid = 'ploopi_popup')
    {
        $strOptionAnchor = ($_SESSION['ploopi']['modules'][_MODULE_SYSTEM]['system_focus_popup']) ? "document.location.href = '#anchor_{$popupid}';" : '';

        $res =  '
                <div class="simplebloc" style="margin:0;">
                    <a name="anchor_'.$popupid.'"></a>
                    <div class="simplebloc_title">
                        <div class="simplebloc_titleleft">
                            <img alt="Fermer" title="Fermer le popup" onclick="javascript:ploopi_hidepopup(\''.$popupid.'\');" style="display:block;float:right;margin:2px;cursor:pointer;" src="'.$this->values['path'].'/template/close_popup.png">
                            <div style="overflow:auto;cursor:move;" id="handle_'.$popupid.'">'.$title.'</div>
                        </div>
                    </div>
                    <div class="simplebloc_content">'.$content.'</div>
                    <div class="simplebloc_footer" style="cursor:move;" id="handlebottom_'.$popupid.'"></div>
                </div>
                <script type="text/javascript">
                new Draggable(\''.$popupid.'\', { handle: \'handle_'.$popupid.'\'});
                new Draggable(\''.$popupid.'\', { handle: \'handlebottom_'.$popupid.'\'});
                '.$strOptionAnchor.'
                </script>
                ';

        return($res);
    }


    /**
     * Crée un faux popup et l'ouvre via javascript
     *
     * @param string $title titre du popup
     * @param string $content contenu du popup (html)
     * @param string $popupid id du popup (propriété html id)
     * @return string code html du popup
     */

    function open_popup($title, $content, $popupid = 'ploopi_popup', $arrOptions = array())
    {
        $arrDefaultOptions = array(
            'intWidth' => 200,
            'booCentered' => true,
            'intPosx' => 0,
            'intPosy' => 0,
            'stringJsBeforeStart' => '',
            'stringJsAfterFinish' => '',
        );

        $arrOptions = array_merge($arrDefaultOptions, $arrOptions);

        $strOptionAnchor = ($_SESSION['ploopi']['modules'][_MODULE_SYSTEM]['system_focus_popup']) ? "document.location.href = '#anchor_{$popupid}';" : '';

        $res =  '
                <div id="'.$popupid.'" style="display:none;">
                    <div class="simplebloc" style="margin:0;">
                        <a name="anchor_'.$popupid.'"></a>
                        <div class="simplebloc_title">
                            <div class="simplebloc_titleleft">
                                <img alt="Fermer" title="Fermer le popup" onclick="javascript:ploopi_hidepopup(\''.$popupid.'\');" style="display:block;float:right;margin:2px;cursor:pointer;" src="'.$this->values['path'].'/template/close_popup.png">
                                <div style="overflow:auto;cursor:move;" id="handle_'.$popupid.'">'.$title.'</div>
                            </div>
                        </div>
                        <div class="simplebloc_content">'.$content.'</div>
                        <div class="simplebloc_footer" style="cursor:move;" id="handlebottom_'.$popupid.'"></div>
                    </div>
                </div>
                <script type="text/javascript">
                    ploopi_window_onload_stock(
                        function() {
                            '.$arrOptions['stringJsBeforeStart'].'
                            ploopi_popupize(\''.$popupid.'\', '.$arrOptions['intWidth'].', '.($arrOptions['booCentered'] ? 'true' : 'false').', '.$arrOptions['intPosx'].', '.$arrOptions['intPosy'].');
                            new Draggable(\''.$popupid.'\', { handle: \'handle_'.$popupid.'\'});
                            new Draggable(\''.$popupid.'\', { handle: \'handlebottom_'.$popupid.'\'});
                            '.$arrOptions['stringJsAfterFinish'].'
                            '.$strOptionAnchor.'
                        }
                    );

                </script>
                ';

        return($res);
    }

    /**
     * Affiche un tableau avancé
     *
     * @param array $arrColumns définition des colonnes
     * @param array $arrValues contenu du tableau
     * @param string $strArrayId identifiant du tableau
     * @param array $arrOptions options d'affichage
     *
     * propriétés des colonnes : label, width, styles, options
     * propriétés des valeurs : label, style, sort_label
     * propriétés des options : height, sortable, orderby_default, sort_default
     *
     * <code>
     * <?php
     * $array_columns = array();
     * $array_values = array();
     *
     * $array_columns['left']['colonne1'] = array('label' => 'Colonne 1', 'width' => '100', 'options' => array('sort' => true));
     * $array_columns['right']['colonne2'] = array('label' => 'Colonne 2', 'width' => '100','options' => array('sort' => true));
     * $array_columns['auto']['colonneauto'] = array('label' => 'Colonne Auto', 'options' => array('sort' => true));
     *
     * $c = 0;
     *
     * $array_values[$c]['values']['colonne1'] = array('label' => 'valeur1');
     * $array_values[$c]['values']['colonne2'] = array('label' => 'valeur2', 'style' => 'text-align:right');
     * $array_values[$c]['values']['colonneauto'] = array('label' => 'valeur3', 'sort_label' => '3');
     *
     * $skin->display_array($array_columns, $array_values, 'id_tableau', array('height' => 200, 'sortable' => true, 'orderby_default' => 'valeur3', 'sort_default' => 'DESC'));
     * ?>
     * </code>
     */

    public function display_array(&$arrColumns, &$arrValues, $strArrayId = null, $arrOptions = null)
    {

        if (empty($strArrayId)) $strArrayId = md5(uniqid(rand(), true));

        $sort = $orderby = '';

        /*
        if (!empty($_SESSION['ploopi']['arrays'][$strArrayId]))
        {
            // preserve orderby & sort values
            $orderby = $_SESSION['ploopi']['arrays'][$strArrayId]['orderby'];
            $sort = $_SESSION['ploopi']['arrays'][$strArrayId]['sort'];
        }
        */

        if (empty($arrOptions['page'])) $arrOptions['page'] = 1;
        if (empty($arrOptions['limit'])) $arrOptions['limit'] = 0;

        $array = array('columns' => &$arrColumns, 'values' => &$arrValues, 'options' => &$arrOptions, 'orderby' => &$orderby, 'sort' => &$sort);

        if (!empty($array['options']['sortable']) && $array['options']['sortable'])
        {
            $array['sortable_columns'] = array();
            if (!empty($array['columns']['left']))
            {
                foreach($array['columns']['left'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['left'][$id]['onclick'] = "ploopi_skin_array_refresh('{$strArrayId}', '{$id}');";
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

            if (!empty($array['columns']['auto']))
            {
                foreach($array['columns']['auto'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['auto'][$id]['onclick'] = "ploopi_skin_array_refresh('{$strArrayId}', '{$id}');";
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

            if (!empty($array['columns']['right']))
            {
                foreach($array['columns']['right'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['right'][$id]['onclick'] = "ploopi_skin_array_refresh('{$strArrayId}', '{$id}');";
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

        }

        $objSV = new serializedvar($strArrayId);
        $objSV->save($array);
        ?>
        <div class="ploopi_explorer_main" id="ploopi_explorer_main_<?php echo $strArrayId; ?>" style="visibility:visible;">
        <?php $this->display_array_refresh($strArrayId); ?>
        </div>
        <?php

    }


    private function array_page($strArrayId, $intPage, $strPage, $intSel = 0)
    {
        return $intSel == $intPage ? str_replace('{p}', $strPage, '<strong>{p}</strong>') : str_replace(array('{p}', '{id}'), array($strPage, $intPage), '<a href="javascript:void(0);" onclick="javascript:ploopi_skin_array_refresh(\''.$strArrayId.'\', \'\', \'{id}\');">{p}</a>');
    }

    /**
     * Rafraichit l'affichage d'un tableau avancé
     *
     * @param string $strArrayId id du tableau
     * @param string $orderby colonne de tri
     */

    public function display_array_refresh($strArrayId, $strOrderBy = null, $intIdPage = null)
    {
        // On récupère le tableau stocké en session (identifié par array_id)
        include_once './include/classes/serializedvar.php';

        $objSV = new serializedvar($strArrayId);
        $array = $objSV->read();

        // Le tableau n'existe pas, pas normal, on sort
        if ($array == false) return;

        $sort_img = '';

        // index temporaire (pour tri à l'affichage)
        $array['index'] = array();

        // si le tableau est "triable" (option)
        if (!empty($array['options']['sortable']) && $array['options']['sortable'])
        {
            // initialisation  du tri par défaut pour le tableau courant
            if (empty($array['orderby']))
            {
                if (!empty($array['options']['orderby_default'])) $array['orderby'] = $array['options']['orderby_default'];
                elseif (!empty($array['sortable_columns'][0])) $array['orderby'] = $array['sortable_columns'][0];
            }

            if (empty($array['sort']))
            {
                if (!empty($array['options']['sort_default'])) $array['sort'] = $array['options']['sort_default'];
                else $array['sort'] = 'ASC';
            }
            // on réinitialise l'ordre de tri si l'ordreby est différent du précédent
            if (!empty($strOrderBy))
            {
                if ($strOrderBy != $array['orderby']) $array['sort'] = 'ASC';
                else $array['sort'] = ($array['sort'] == 'ASC') ? 'DESC' : 'ASC';
            }

            // récupération de la valeur de l'orderby en session ou en parametre (par défaut en paramètre)
            $array['orderby'] = (empty($strOrderBy)) ? $array['orderby'] : $strOrderBy;

            $c = 0;
            foreach($array['values'] as $key => $value)
            {
                $label = isset($value['values'][$array['orderby']]['sort_label']) ? 'sort_label' : 'label';
                $idx = is_numeric($value['values'][$array['orderby']][$label]) ? sprintf("%064s_%06s", $value['values'][$array['orderby']][$label], $c++) : sprintf("%s_%06s", strtoupper(ploopi_convertaccents($value['values'][$array['orderby']][$label])), $c++);

                $array['index'][$idx] = $key;
            }

            if ($array['sort'] == 'ASC') ksort($array['index'], SORT_STRING);
            else krsort($array['index'], SORT_STRING);

            $sort_img = ($array['sort'] == 'DESC') ? "<img src=\"{$this->values['path']}/arrays/arrow_down.png\">" : "<img src=\"{$this->values['path']}/arrays/arrow_up.png\">";

        }
        else $array['index'] = array_keys($array['values']);

        if (!empty($intIdPage) && is_numeric($intIdPage)) $array['options']['page'] = $intIdPage;

        $objSV->save($array);

        // Affichage des pages (optionnel)
        if ($array['options']['limit'] > 0 && $array['options']['limit'] < sizeof($array['values']))
        {
            $intNbPages = ceil(sizeof($array['values']) / $array['options']['limit']);

            $arrPages = array();

            // Fleche page précédente
            if ($array['options']['page'] > 1) $arrPages[] = $this->array_page($strArrayId, $array['options']['page']-1, '&laquo;');

            // On affiche toujours la premiere page
            $arrPages[] = $this->array_page($strArrayId, 1, 1, $array['options']['page']);

            // Affichage "..." après première page
            if ($array['options']['page'] > 4) $arrPages[] = '...';

            // Boucle sur les pages autour de la page sélectionnée (-2 à +2 si existe)
            for ($i = $array['options']['page'] - 2; $i <= $array['options']['page'] + 2; $i++)
            {
                if ($i>1 && $i<$intNbPages) $arrPages[] = $this->array_page($strArrayId, $i, $i, $array['options']['page']);
            }

            // Affichage "..." avant dernière page
            if ($array['options']['page'] < $intNbPages - 3) $arrPages[] = '...';

            // Dernière page
            if ($intNbPages>1) $arrPages[] = $this->array_page($strArrayId, $intNbPages, $intNbPages, $array['options']['page']);

            // Fleche page suivante
            if ($array['options']['page'] < $intNbPages) $arrPages[] = $this->array_page($strArrayId, $array['options']['page']+1, '&raquo;');

            ?>
            <div class="ploopi_explorer_multipage" style="border-bottom:1px solid #c0c0c0;padding:2px 4px;text-align:right;">
                Pages : <?php echo implode(' ', $arrPages); ?>
            </div>
            <?php
        }


        $i = 0;
        $w = 0;

        // on insère d'abord la colonne optionnelle de droite (actions)
        if (!empty($array['columns']['actions_right']))
        {
            foreach($array['columns']['actions_right'] as $id => $c)
            {
                $w += $c['width'];
                ?>
                <div style="right:<?php echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<?php echo $strArrayId; ?>_<?php echo $i; ?>"></div>
                <?php
                $i++;
            }
        }

        // on insère ensuite les colonnes de données de droite (optionnelles)
        if (!empty($array['columns']['right']))
        {
            foreach($array['columns']['right'] as $c)
            {
                $w += $c['width'];
                ?>
                <div style="right:<?php echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<?php echo $strArrayId; ?>_<?php echo $i; ?>"></div>
                <?php
                $i++;
            }
        }

        $w = 0;

        // puis les colonnes de données de gauche (optionnelles)
        if (!empty($array['columns']['left']))
        {
            foreach($array['columns']['left'] as $c)
            {
                $w += $c['width'];
                ?>
                <div style="left:<?php echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<?php echo $strArrayId; ?>_<?php echo $i; ?>"></div>
                <?php
                $i++;
            }
        }

        // on gère ensuite l'affichage des titres de colonne
        ?>
        <div style="position:relative;">
            <div class="ploopi_explorer_title" id="ploopi_explorer_title_<?php echo $strArrayId; ?>">
                <?php
                // titres des colonnes d'action (à droite)
                if (!empty($array['columns']['actions_right']))
                {
                    foreach($array['columns']['actions_right'] as $id => $c)
                    {
                        ?>
                        <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><?php echo $c['label']; ?>&nbsp;</span></p></a>
                        <?php
                    }
                }

                // titres des colonnes de données à droite
                if (!empty($array['columns']['right']))
                {
                    foreach($array['columns']['right'] as $id => $c)
                    {
                        $img = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $c['style'] .= 'background-color:#e0e0e0;';
                        }
                        ?>
                        <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element ploopi_explorer_element_right"><p><span><?php echo $c['label']; ?>&nbsp;</span><?php echo $img; ?></p></a>
                        <?php
                    }
                }

                // titres des colonnes de données à gauche
                if (!empty($array['columns']['left']))
                {
                    foreach($array['columns']['left'] as $id => $c)
                    {
                        $img = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $c['style'] .= 'background-color:#e0e0e0;';
                        }
                        ?>
                        <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="width:<?php echo $c['width']; ?>px;float:left;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><?php echo $c['label']; ?>&nbsp;</span><?php echo $img; ?></p></a>
                        <?php
                    }
                }

                // titre de la colonne centrale (auto)
                if (!empty($array['columns']['auto']))
                {
                    foreach($array['columns']['auto'] as $id => $c)
                    {
                        $img = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $c['style'] .= 'background-color:#e0e0e0;';
                        }
                        ?>
                        <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="overflow:auto;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><?php echo $c['label']; ?>&nbsp;</span><?php echo $img; ?></p></a>
                        <?php
                    }
                }
                ?>
            </div>

            <?php
            // Gestion de l'affichage des lignes de données
            ?>

            <div <?php if (!empty($array['options']['height'])) echo "style=\"height:{$array['options']['height']}px;overflow:auto;\""; ?> id="ploopi_explorer_values_outer_<?php echo $strArrayId; ?>">

                <div id="ploopi_explorer_values_inner_<?php echo $strArrayId; ?>">
                <?php
                if (!empty($array['values']))
                {
                    /*
                    // On se positionne sur la bonne page de données
                    reset($array['values']);
                    $intLines = $array['options']['limit'];

                    if ($intLines > 0 && $intLines < sizeof($array['values']))
                    {
                        for ( $i=0 ; $i < ($array['options']['page']-1) * $intLines ; $i++) next($array['values']);
                        if ($intLines > sizeof($array['values']) - ($array['options']['page']-1) * $intLines) $intLines = sizeof($array['values']) - ($array['options']['page']-1) * $intLines;
                    }
                    else $intLines = sizeof($array['values']);
                    */

                    // On se positionne sur la bonne page de données
                    $intLines = $array['options']['limit'];

                    if ($intLines > 0 && $intLines < sizeof($array['index']))
                    {
                        for ( $i=0 ; $i < ($array['options']['page']-1) * $intLines ; $i++) next($array['index']);
                        if ($intLines > sizeof($array['index']) - ($array['options']['page']-1) * $intLines) $intLines = sizeof($array['index']) - ($array['options']['page']-1) * $intLines;
                    }
                    else $intLines = sizeof($array['index']);

                    // On parcourt le nombre de ligne souhaité
                    for ($i = 0 ; $i < $intLines ; $i++)
                    {
                        $v = $array['values'][current($array['index'])];

                        // alternance des couleurs (une ligne sur 2) : on joue sur les css
                        $color = (empty($color) || $color == 1) ? 2 : 1;
                        ?>
                        <div <?php if (!empty($v['id'])) echo "id=\"{$v['id']}\""; ?> class="ploopi_explorer_line_<?php echo $color; ?>" <?php if (!empty($v['style'])) echo "style=\"{$v['style']}\""; ?>>
                            <?php
                            if (!empty($array['columns']['actions_right']))
                            {
                                foreach($array['columns']['actions_right'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div class="ploopi_explorer_tools" style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>"><?php echo $v['values'][$id]['label']; ?></div>
                                    <?php
                                }
                            }

                            $option = (empty($v['option'])) ? '' : $v['option'];

                            if (!empty($v['link']) || !empty($v['onclick']))
                            {
                                $onclick = (empty($v['onclick'])) ? '' : "onclick=\"{$v['onclick']}\"";
                                $title = (empty($v['description'])) ? '' : 'title="'.htmlentities($v['description']).'"';
                                ?>
                                <a class="ploopi_explorer_link" href="<?php echo $v['link']; ?>" <?php echo $title ; ?> <?php echo $onclick ; ?> <?php echo $option; ?>>
                                <?php
                            }
                            if (!empty($array['columns']['right']))
                            {
                                foreach($array['columns']['right'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element"><p><?php echo $v['values'][$id]['label']; ?></p></div>
                                    <?php
                                }
                            }

                            if (!empty($array['columns']['left']))
                            {
                                foreach($array['columns']['left'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div style="width:<?php echo $c['width']; ?>px;float:left;<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element"><p><?php echo $v['values'][$id]['label']; ?></p></div>
                                    <?php
                                }
                            }

                            if (!empty($array['columns']['auto']))
                            {
                                foreach($array['columns']['auto'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div style="<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element"><p><?php echo $v['values'][$id]['label']; ?></p></div>
                                    <?php
                                }
                            }

                            if (!empty($v['link']) || !empty($v['onclick']))
                            {
                                ?>
                                </a>
                                <?php
                            }

                            ?>
                        </div>
                        <?php

                        next($array['index']);
                    }
                }
                ?>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            ploopi_skin_array_renderupdate('<?php echo $strArrayId; ?>');
        </script>
        <?php
    }

    /**
     * Affiche un treeview
     *
     * @param array $nodes tableau associatif contenant les noeuds
     * @param array $treeview tableau contenant la hiérarchie des noeuds
     * @param string $node_id_sel identifiant du noeud sélectionné
     * @param string $node_id_from identifiant du noeud de départ (permet de n'afficher qu'un sous-ensemble)
     * @param boolean $viewall true tous les noeuds de l'arbre doivent être ouvert (false par défaut)
     * @return string code html du treeview
     */

    public function display_treeview(&$nodes, &$treeview, $node_id_sel = null, $node_id_from = null, $viewall = false)
    {
        // recherche du premier noeud
        if (is_null($node_id_from)) $node_id_from = key($treeview);

        if (!is_null($node_id_sel) && isset($nodes[$node_id_sel])) $nodesel = $nodes[$node_id_sel];

        // code html généré par ce niveau de boucle
        $html = '';

        if (isset($treeview[$node_id_from]))
        {
            $c = 0;
            foreach($treeview[$node_id_from] as $node_id)
            {
                // noeud courant
                $node = $nodes[$node_id];

                // true si le noeud courant est sélectionné
                $is_node_sel = (!is_null($node_id_sel) && ($node_id_sel == $node['id']));

                // parents du noeud sélectionné
                $nodesel_parents = (isset($nodesel)) ? $nodesel['parents'] : array();

                // parents du noeud courant
                $node_parents = array_merge($node['parents'], array($node['id']));

                // true si le noeud est ouvert : le noeud est ouvert si les parents du noeud courant et du noeud sélectionné se superposent
                $is_node_opened = ($viewall || sizeof(array_intersect_assoc($nodesel_parents, $node_parents)) == sizeof($node_parents));

                // true si le noeud est le dernier fils de son père
                $is_node_last = ($c == sizeof($treeview[$node_id_from])-1);

                // profondeur du noeud ( = nombre de noeuds parents)
                $node_depth = sizeof($node['parents']);

                $node_link = '';
                $bg = '';

                if ($node_depth == 1)
                {
                    // au premier niveau de profondeur, on ne crée pas de décalage
                    $marginleft = 0;
                }
                else
                {
                    $type_node = 'join';
                    if (isset($treeview[$node_id])) $type_node = ($is_node_sel || $is_node_opened) ? 'minus' : 'plus';

                    if (!$is_node_last)
                    {
                        $type_node .= 'bottom';
                        $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/treeview/line.png) 0 0 repeat-y;";
                    }

                    $n_link = (empty($node['node_link'])) ? 'javascript:void(0);' : $node['node_link'];
                    $n_onclick = (empty($node['node_onclick'])) ? '' : 'onclick="javascript:'.$node['node_onclick'].';"';

                    $node_link = "<a href=\"{$n_link}\" {$n_onclick}><img id=\"t{$node['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$type_node}.png\" /></a>";

                    $marginleft = 20;
                }

                // récupération du code html des noeuds fils par un appel récursif
                $html_children = ($is_node_sel || $is_node_opened || $node_depth == 1) ? $this->display_treeview($nodes, $treeview, $node_id_sel, $node['id'], $viewall) : '';

                // si du contenu à afficher, display = 'block'
                $display = ($html_children == '') ? 'none' : 'block';

                // si le noeud courant est sélectionné on le met en gras
                $style_sel = ($is_node_sel) ? 'bold' : 'none';

                // lien sur le libellé
                $link = (empty($node['link'])) ? 'javascript:void(0);' : $node['link'];

                // onclick sur le libellé
                $onclick = (empty($node['onclick'])) ? '' : 'onclick="'.$node['onclick'].';"';

                // label supplémentaire
                $status = (empty($node['status'])) ? '' : $node['status'];

                // génération du code html du noeud courant
                $html .= "
                    <div class=\"treeview_node\" id=\"treeview_node{$node['id']}\" style=\"{$bg}\">
                        <div>
                            {$node_link}<img src=\"{$node['icon']}\" />
                            <div style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;font-weight:{$style_sel};\">
                                <a href=\"{$link}\" {$onclick}>{$node['label']}</a>
                                {$status}
                            </div>
                        </div>
                        <div style=\"margin-left:{$marginleft}px;display:{$display};\" id=\"n{$node['id']}\">{$html_children}</div>
                    </div>
                ";

                $c++;
            }
        }
        return $html;

    }


    /**
     * Affichage d'une liste de choix paramétrable
     *
     * @param string $id identifiant du champ de formulaire
     * @param string $name nom du champ de formulaire
     * @param array $arrValues tableau des valeurs de la liste
     * @param array $arrUserOptions options d'affichage
     * @param string $selecteditem clé de l'élément sélectionné
     * @return string code html de la liste
     */

    public function display_selectbox($id, $name, $arrValues, $arrUserOptions = null, $selecteditem = null)
    {
        // Options par défaut
        $arrOptions =
            array(
                'input_width' => null,
                'menu_width' => null,
                'onchange' => null
            );

        // Merge avec les options utilisateur
        $arrOptions = array_merge($arrOptions, $arrUserOptions);

        // Démarrage bufferisation
        ob_start();
        ?>
        <input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php if (!empty($selecteditem)) echo htmlentities($selecteditem); ?>" <?php if (!empty($arrOptions['onchange'])) echo 'onchange="javascript:'.$arrOptions['onchange'].'";'; ?>/>

        <div class="ploopi_selectbox" style="display:inline-block;<?php if (!empty($arrOptions['input_width'])) echo "width:{$arrOptions['input_width']};"; ?>">
            <div class="ploopi_selectbox_button" id="ploopi_selectbox_button<?php echo $id; ?>" onclick="javascript:$('ploopi_selectbox_list<?php echo $id; ?>').style.display='block';">
                <div class="ploopi_selectbox_button_content" id="ploopi_selectbox_button_content<?php echo $id; ?>" >
                    <?php
                    if (!empty($arrValues[$selecteditem]))
                    {
                        $menu = $arrValues[$selecteditem];

                        if (!empty($menu['icon']))
                        {
                            ?>
                            <img src="<?php echo $menu['icon']; ?>">
                            <?php
                        }
                        ?>
                        <span><?php echo $menu['label']; ?></span><?php if (!empty($menu['label_extended'])) echo $menu['label_extended'];
                    }
                    ?>
                </div>
            </div>

            <div id="ploopi_selectbox_list<?php echo $id; ?>" class="ploopi_selectbox_list" style="display:none;<?php if (!empty($arrOptions['menu_width'])) echo "width:{$arrOptions['menu_width']};"; ?>" onclick="$('ploopi_selectbox_list<?php echo $id; ?>').style.display='none';" onmouseout="$('ploopi_selectbox_list<?php echo $id; ?>').style.display='none';">
                <ul onmouseover="$('ploopi_selectbox_list<?php echo $id; ?>').style.display='block';">
                    <?php
                    foreach($arrValues as $key => $menu)
                    {
                        switch($menu['type'])
                        {
                            case 'group':
                                ?>
                                <li style="font-weight:bold;padding:2px 4px;"><?php echo $menu['label']; ?></li>
                                <?php
                            break;

                            case 'select':
                                ?>
                                <li>
                                    <a href="javascript:void(0);" <?php if (!empty($menu['onclick'])) echo 'onclick="javascript:'.$menu['onclick'].'"'; ?> onclick="javascript:$('ploopi_selectbox_button_content<?php echo $id; ?>').innerHTML = this.innerHTML; $('<?php echo $id; ?>').value = '<?php echo addslashes($key); ?>'; ploopi_dispatch_onchange('<?php echo $id; ?>');return false;" title="Accéder à <?php echo htmlentities($menu['label']); ?>">
                                        <?php
                                        if (!empty($menu['icon']))
                                        {
                                            ?>
                                            <img src="<?php echo $menu['icon']; ?>">
                                            <?php
                                        }
                                        ?>
                                        <span><?php echo $menu['label']; ?></span><?php if (!empty($menu['label_extended'])) echo $menu['label_extended']; ?>
                                    </a>
                                </li>
                                <?php
                            break;

                            case 'link':
                                ?>
                                <li>
                                    <a href="<?php echo $menu['link']; ?>" <?php if (!empty($menu['onclick'])) echo 'onclick="javascript:'.$menu['onclick'].'"'; ?> <?php if (!empty($menu['target'])) echo 'target="'.$menu['target'].'"'; ?> title="Accéder à <?php echo htmlentities($menu['label']); ?>">
                                        <?php
                                        if (!empty($menu['icon']))
                                        {
                                            ?>
                                            <img src="<?php echo $menu['icon']; ?>">
                                            <?php
                                        }
                                        ?>
                                        <span><?php echo $menu['label']; ?></span><?php if (!empty($menu['label_extended'])) echo $menu['label_extended']; ?>
                                    </a>
                                </li>
                                <?php
                            break;
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
        $strContent = ob_get_contents();
        ob_end_clean();

        return $strContent;
    }
}
