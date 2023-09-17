<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Enum;

enum Framework: string
{
    case LARAVEL = 'laravel';
    case LUMEN = 'lumen';
    case SYMFONY = 'symfony';
    case WORDPRESS = 'wordpress';
    case DRUPAL = 'drupal';
    case MAGENTO = 'magento';
    case PRESTASHOP = 'prestashop';
    case OPENCART = 'opencart';
    case YII2 = 'yii2';
    case CODEIGNITER = 'codeigniter';
    case CAKEPHP = 'cakephp';
    case FUEL = 'fuel';
    case SLIM = 'slim';
    case PHALCON = 'phalcon';
    case ZEND = 'zend';
    case TYPO3 = 'typo3';
    case JOOMLA = 'joomla';
    case LAMINAS = 'laminas';
}
