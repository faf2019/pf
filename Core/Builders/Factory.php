<?php
namespace ovensia\pf\Core\Builders;

use ovensia\pf\Core;

/**
 * Classe factory générique qui fournit une méthode getInstance.
 * Cette méthode est capable de créer une instance de la classe appelante (en utilisant éventuellement le contructeur public et les paramètres adaptés)
 * @param array $arrArgs arguments dynamiques
 * @return object instance de l'objet
 */

trait Factory
{
    public static function getInstance()
    {
        // Lecture des arguments
        $arrArgs = func_get_args();

        // Détermination de la classe appelante (héritée)
        $strClassName = get_called_class();

        // Création d'un objet Reflexion de la classe
        $objReflection = new \ReflectionClass($strClassName);

        // Vérification de l'instanciabilité de la classe appelante (héritée)
        if (!$objReflection->isInstantiable()) throw new Core\Exception("Class &laquo; {$strClassName} &raquo; not instanciable");

        // Lecture du constructeur de la classe appelante (héritée)
        $objMethod = $objReflection->getConstructor();

        // Vérification de l'existence d'un constructeur public (pour l'appeler)
        if ($objMethod instanceof \ReflectionMethod && $objMethod->isPublic())
        {
            // On vérifie le nombre de paramètres attendus par le constructeur de la classe appelante (héritée)
            $intRequiredParams = $objMethod->getNumberOfRequiredParameters();
            if (sizeof($arrArgs) < $intRequiredParams) throw new Core\Exception("The factory method &laquo; {$strClassName}.getInstance() &raquo; requires at least {$intRequiredParams} parameter(s) : \n".implode(', ', $objMethod->getParameters()));

            return $objReflection->newInstanceArgs($arrArgs);
        }
        else return $objReflection->newInstanceArgs();
    }

    public function getClone() { return clone $this; }
}
