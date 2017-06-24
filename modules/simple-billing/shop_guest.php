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

function exec_ogp_module()
{
	require('includes/config.inc.php');	
	global $db;
	$settings = $db->getSettings();
	?>
	<p style="text-align:center;background-color:cyan;color:blue;">&nbsp;<?php print_lang("you_need_to");?> <a href="?m=register&p=form"><?php print_lang("register");?></a> <?php print_lang("and");?> <a href="index.php"><?php print_lang("log_in");?></a>&nbsp;<?php print_lang("to_purchase_a_service");?>.</p>
	<h2><?php print_lang("available_services");?></h2>
	<div style="border: 5px solid transparent;">
	<?php  
	$qry_services = "SELECT * FROM OGP_DB_PREFIXbilling_services";
	$services = $db->resultQuery($qry_services);
	foreach ($services as $key => $row) {
		$service_id[$key] = $row['service_id'];
		$home_cfg_id[$key] = $row['home_cfg_id'];
		$mod_cfg_id[$key] = $row['mod_cfg_id'];
		$service_name[$key] = $row['service_name'];
		$remote_server_id[$key] = $row['remote_server_id'];
		$slot_max_qty[$key] = $row['slot_max_qty'];
		$slot_min_qty[$key] = $row['slot_min_qty'];
		$price_hourly[$key] = $row['price_hourly'];
		$price_monthly[$key] = $row['price_monthly'];
		$price_year[$key] = $row['price_year'];
		$description[$key] = $row['description'];
		$img_url[$key] = $row['img_url'];
		$ftp[$key] = $row['ftp'];
		$install_method[$key] = $row['install_method'];
		$manual_url[$key] = $row['manual_url'];
		$access_rights[$key] = $row['access_rights'];
	}
	//Sort by service name, the 1st position in this array multisort, service_name, defines the row that sorts the array, if there are two equal service names then the next row, service_id, will sort these rows.
	array_multisort($service_name,
					$service_id,
					$home_cfg_id,
					$mod_cfg_id,
					$remote_server_id,
					$slot_max_qty,
					$slot_min_qty,
					$price_hourly,
					$price_monthly,
					$price_year,
					$description,
					$img_url,
					$ftp,
					$install_method,
					$manual_url,
					$access_rights, SORT_DESC, $services);
	
	foreach($services as $row)
	{ 
	?>
	<div style="float:left; border: 4px solid transparent;border-bottom: 25px solid transparent;">
	<img src="<?php echo $row['img_url'] ;?>" width=280 height=132 border=0 alt="Bad Image">
	<br>
	<center><b><?php echo $row['service_name'];?></b></center>
	<center><em style="text-align:center;background-color:orange;color:blue;"><?php echo get_lang('starting_on') . " <b>" . floatval(round(($row['price_monthly']*$row['slot_min_qty']),2 )) . "</b>&nbsp;" . $settings['currency'] . "/" . get_lang('month') . " (" . $row['slot_min_qty'] . " " . get_lang('slots') . ").";?></em></center>
	</div>
	<?php 
	}
	?>
	</div>
	<?php
}
?>
