<?php
namespace ovensia\pf\Core\Router\Route;

use ovensia\pf\Core;

class Route extends Common implements Model
{
    /**
     * Vérifie que la route valide le chemin fournit en paramètre
     * @param  $strPath chemin à tester
     * @return array|bool
     */
    public function check($strPath)
    {
        //echo '<br />'.$strPath;
        //echo '<br />'.$this->getPattern();

        // Traduction du pattern en regexp pour valider ou non l'url
        // 1. Description des variables aux formats attendus
        // 2. Description par défaut pour les autres variables
        $arrPatterns = array();
        $arrReplacements = array();

        // Remplacements spécifiques
        foreach($this->getFormats() as $strVar => $strPattern)
        {
            // Détection des variables disposant d'un type défini
            // On recherche donc la variable suivie d'un caractère non alpha ou d'une fin de chaine
            $arrPatterns[] = '/:'.$strVar.'([^a-z0-9]|$)/';
            // Remplacement par le type de donnée attendu
            $arrReplacements[] = '('.$strPattern.')$1';
        }

        // Remplacement par défaut
        $arrPatterns[] = '/:'.self::VARIABLE_MOTIF.'/'; // Variable
        $arrReplacements[] = '('.self::VALUE_MOTIF.')'; // Valeur acceptée

        // Génération de la regexp
        $strRegexPattern = '#^'.preg_replace($arrPatterns, $arrReplacements, $this->getPattern()).'(/.*|)?$#';
        //$strRegexPattern = '#^'.preg_replace($arrPatterns, $arrReplacements, $this->getPattern()).'(/.*)?$#';
        //$strRegexPattern = '#^'.preg_replace($arrPatterns, $arrReplacements, $this->getPattern()).'$#';

        // Analyse du path avec le regexp calculé
        if (!preg_match($strRegexPattern, $strPath, $arrMatches)) return false;

         // Variables statiques par défaut (peuvent être écrasées par le contenu du path)
        $arrVariables = $this->getStaticVariables();

        // Recherche des variables dans le pattern
        preg_match_all('#:('.self::VARIABLE_MOTIF.')#', $this->getPattern(), $arrPatternVars);
        $arrPatternVars = isset($arrPatternVars[1]) ? $arrPatternVars[1] : array();

        // Récupération des variables
        foreach($arrPatternVars as $intKey => $strVar) $arrVariables[$strVar] = $arrMatches[$intKey+1];

        // Récupération des paramètres en reliquat
        if (isset($arrMatches[sizeof($arrPatternVars)+1]))
        {
            if (preg_match_all('#/('.self::VARIABLE_MOTIF.')(-('.self::VALUE_MOTIF.'))?#', $arrMatches[sizeof($arrPatternVars)+1], $arrParams))
            {
                foreach($arrParams[1] as $intKey => $strVar) {
                    $arrVariables[$strVar] = $arrParams[3][$intKey];
                    $this->_setReadParam($strVar, $arrParams[3][$intKey]);
                }
            }
            /*
            if (preg_match_all('#/('.self::VARIABLE_MOTIF.')(/('.self::VALUE_MOTIF.'))?#', $arrMatches[sizeof($arrPatternVars)+1], $arrParams))
            {
                foreach($arrParams[1] as $intKey => $strVar) $arrVariables[$strVar] = $arrParams[3][$intKey];
            }
            */
        }

        return $arrVariables;
    }

    public function rewrite($arrVariables = array(), $arrParams = array(), $arrOptions = array())
    {
        // Vérification des paramètres
        if (!is_array($arrVariables)) throw new Core\Exception('Wrong type for $arrVariables');

        // Stockage local des formats pour passage à la fonction anonyme
        $arrFormats = $this->getFormats();

        // Réécriture de l'url
        return preg_replace_callback('#:('.self::VARIABLE_MOTIF.')#', function ($arrMatches) use ($arrVariables, $arrFormats, $arrOptions) {
            // Vérification de la présence de la variable en paramètre
            if (!isset($arrVariables[$arrMatches[1]])) throw new Core\Exception("Missing variable '{$arrMatches[1]}' in url");
            // Vérification du type de la variable
            if (isset($arrFormats[$arrMatches[1]]) && !preg_match('#^'.$arrFormats[$arrMatches[1]].'$#', $arrVariables[$arrMatches[1]])) throw new \ovensia\pf\Core\Exception("Unexpected type for variable '{$arrMatches[1]}' in url");
            // Ok donc on réécrit
            return mb_strcut($arrOptions['urlify'] ? Core\Tools\Ustring::getInstance($arrVariables[$arrMatches[1]])->toUrl()->getString() : $arrVariables[$arrMatches[1]], 0, 200);
        }, $this->getPattern()).$this->_addParams($arrParams, $arrOptions['restful']);
    }

}
