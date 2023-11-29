<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBlockCategories
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'eg_block_categories` (
    `id_eg_block_categories` int(11) NOT NULL AUTO_INCREMENT,
    `position` int(10) unsigned NOT NULL DEFAULT 0,
    `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY  (`id_eg_block_categories`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'eg_block_categories_lang` (
    `id_eg_block_categories` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(10) unsigned NOT NULL,
    `id_shop` int(10) unsigned NOT NULL DEFAULT 1,
    `image` varchar(255) NOT NULL,
    `alt` varchar(128) NOT NULL,
    `link` varchar(255) NOT NULL,
    `title` varchar(128) NOT NULL,
    `subtitle` varchar(128) NOT NULL,
    PRIMARY KEY (`id_eg_block_categories`, `id_shop`, `id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'eg_block_categories_shop` (
`id_eg_block_categories` int(10) unsigned NOT NULL,
`id_shop` int(10) unsigned NOT NULL ,
PRIMARY KEY (`id_eg_block_categories`, `id_shop`),
KEY `id_shop` (`id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
