<?php
/*
 *
 * OGP - Open Game Panel
 * Copyright (C) 2008 - 2017 The OGP Development Team
 *
 * http://www.opengamepanel.org/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */

// Module general information
$module_title = "billing";
$module_version = "6.4";
$db_version = 4;
$module_required = FALSE;
$module_menus = array(
    array( 'subpage' => 'shop', 'name'=>'Shop', 'group'=>'user' ),
	array( 'subpage' => 'shop_guest', 'name'=>'Shop', 'group'=>'guest' ),
    array( 'subpage' => 'orders', 'name'=>'Orders', 'group'=>'admin' ),
	array( 'subpage' => 'services', 'name'=>'Services', 'group'=>'admin' ),
	array( 'subpage' => 'shop_settings', 'name'=>'Shop Settings', 'group'=>'admin' )
);

$install_queries = array();
$install_queries[0] = array(
	"DROP TABLE IF EXISTS `".OGP_DB_PREFIX."billing_services`;",
    "CREATE TABLE IF NOT EXISTS `".OGP_DB_PREFIX."billing_services` (
	`service_id` int(11) NOT NULL auto_increment,
	`home_cfg_id` int(11) NOT NULL,
	`mod_cfg_id` int(11) NOT NULL,
	`service_name` varchar(255) NOT NULL,
	`remote_server_id` int(11) NOT NULL,
	`slot_max_qty` int(11) NOT NULL,
	`slot_min_qty` int(11) NOT NULL,
	`price_hourly` float(15,4) NOT NULL,
	`price_monthly` float(15,4) NOT NULL,
	`price_year` float(15,4) NOT NULL,
	`description` varchar(1000) NOT NULL,
	`img_url` varchar(255) NOT NULL,
	`ftp` varchar(255) NOT NULL,
	`install_method` varchar(255) NOT NULL,
	`manual_url` varchar(255) NOT NULL, 
	`access_rights` varchar(255) NOT NULL, 
	PRIMARY KEY  (`service_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8;",
	
    "DROP TABLE IF EXISTS `".OGP_DB_PREFIX."billing_orders`;",
    "CREATE TABLE IF NOT EXISTS `".OGP_DB_PREFIX."billing_orders` (
	`order_id` int(11) NOT NULL auto_increment,	
	`user_id` int(11) NOT NULL,
	`service_id` int(11) NOT NULL,
	`home_path` varchar(255) NOT NULL,
	`home_name` varchar(255) NOT NULL,
	`ip` varchar(255) NOT NULL,
	`port` varchar(5) NOT NULL,
	`qty` int(11) NOT NULL,
	`invoice_duration` varchar(16) NOT NULL,
	`max_players` int(11) NOT NULL,
	`remote_control_password` varchar(10) NULL,
	`ftp_password` varchar(10) NULL,
	`subtotal` float(15,2) NOT NULL,
	`rate` int(11) NOT NULL,
	`total` float(15,2) NOT NULL,
	`date` varchar(10) NULL,
	PRIMARY KEY  (`order_id`)
	) ENGINE=MyISAM;"
);

$install_queries[1] = array(
    "DROP TABLE IF EXISTS `".OGP_DB_PREFIX."billing_carts`;",
    "CREATE TABLE IF NOT EXISTS `".OGP_DB_PREFIX."billing_carts` (
	`cart_id` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL,
	`paid` int(11) NULL,
	PRIMARY KEY  (`cart_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=UTF8;",
	
	"DROP TABLE IF EXISTS `".OGP_DB_PREFIX."billing_orders`;",
    "CREATE TABLE IF NOT EXISTS `".OGP_DB_PREFIX."billing_orders` (
	`order_id` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL,
	`service_id` int(11) NOT NULL,
	`home_path` varchar(255) NOT NULL,
	`home_name` varchar(255) NOT NULL,
	`ip` varchar(255) NOT NULL,
	`qty` int(11) NOT NULL,
	`invoice_duration` varchar(16) NOT NULL,
	`max_players` int(11) NOT NULL,
	`price` float(15,2) NOT NULL,
	`remote_control_password` varchar(10) NULL,
	`ftp_password` varchar(10) NULL,
	`paid` varchar(1) NULL,
	`date` varchar(10) NULL,
	`cart_id` int(11) NOT NULL,
	PRIMARY KEY  (`order_id`)
	) ENGINE=MyISAM;"
);

$install_queries[2] = array(
	"ALTER TABLE `".OGP_DB_PREFIX."billing_orders` DROP `date`;",
	"ALTER TABLE `".OGP_DB_PREFIX."billing_orders` DROP `home_path`;",
	"ALTER TABLE `".OGP_DB_PREFIX."billing_orders` DROP `paid`;",
    "ALTER TABLE `".OGP_DB_PREFIX."billing_orders` ADD `home_id` varchar(255) NOT NULL DEFAULT '0';",
	"ALTER TABLE `".OGP_DB_PREFIX."billing_orders` ADD `end_date` varchar(16) NOT NULL DEFAULT '0';",
	"ALTER TABLE `".OGP_DB_PREFIX."billing_carts` ADD `date` varchar(16) NOT NULL DEFAULT '0';",
	"ALTER TABLE `".OGP_DB_PREFIX."billing_carts` ADD `tax_amount` varchar(16) NOT NULL DEFAULT '0';",
	"ALTER TABLE `".OGP_DB_PREFIX."billing_carts` ADD `currency` varchar(3) NOT NULL DEFAULT '0';"
);

$install_queries[3] = array(
	"ALTER TABLE `".OGP_DB_PREFIX."billing_orders` ADD `finish_date` varchar(16) NOT NULL DEFAULT '0';"
);

$install_queries[4] = array(
	"ALTER TABLE `".OGP_DB_PREFIX."billing_orders` ADD `extended` tinyint(1) NOT NULL;"
);


?>