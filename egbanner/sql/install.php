<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

$sql = array();


/* Information on content EG Banner */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'eg_banner` (
        `id_eg_banner` int(10) unsigned NOT NULL AUTO_INCREMENT,  
        `position` int(10) unsigned NOT NULL DEFAULT 0,
        `id_category` int(10) unsigned NOT NULL, 
        `active` tinyint(1) unsigned NOT NULL DEFAULT 1, 
        PRIMARY KEY (`id_eg_banner`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;';

/* Localized EG Banner infos */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'eg_banner_lang` (
        `id_eg_banner` int(10) unsigned NOT NULL, 
        `id_lang` int(10) unsigned NOT NULL, 
        `id_shop` int(10) unsigned NOT NULL DEFAULT 1,
        `image` varchar(255) NOT NULL,
        `image_mobile` varchar(255) NOT NULL,
        `alt` varchar(128) NOT NULL,
        `link` varchar(255) NOT NULL, 
        `title` varchar(128) NOT NULL, 
        `description` longtext,
        PRIMARY KEY (`id_eg_banner`, `id_shop`, `id_lang`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;';

/* Structure table eg_banner shop */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'eg_banner_shop` (
	`id_eg_banner` int(10) unsigned NOT NULL, 
	`id_shop` int(10) unsigned NOT NULL ,
	PRIMARY KEY (`id_eg_banner`, `id_shop`), 
	KEY `id_shop` (`id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
