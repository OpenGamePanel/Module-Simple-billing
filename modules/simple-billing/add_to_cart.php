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
	global $db ,$view;
	$settings = $db->getSettings();

	//The service id should also be cast to an int.
	$service_id = intval($_REQUEST['service_id']);

	// Query for Selected service info.
	$qry_service = "SELECT DISTINCT service_id, home_cfg_id, mod_cfg_id, service_name, remote_server_id, slot_max_qty, slot_min_qty, price_hourly, price_monthly, price_year, description, img_url FROM OGP_DB_PREFIXbilling_services WHERE service_id=".$db->realEscapeSingle($service_id);
	$result_service = $db->resultQuery($qry_service);		
	$row_service = $result_service[0];
	//Compiling info about invoice to create an invoice order.

	/*	
	Check if it's numeric before used in the WHERE clause... otherwise an SQL error is possible currently.
	If it's not an int (or if it's 0 after casting and or not vaild service) redirect to the shop page.
	*/		
	if ($service_id <= 0 || $result_service === false){
		$view->refresh("home.php?m=simple-billing&p=shop");
		return;
	}	
	
	// remote server value
	$remote_server_id = $row_service['remote_server_id'];

	// request ogp user to create a home path.
	$r_server = $db->getRemoteServer($remote_server_id);
	$ogp_user = $r_server['ogp_user'];

	// request the user name and the game name to generate a game home name.
	$home_name = $_POST['home_name'];

	//Calculating Price
	if ($_POST['invoice_duration'] == "hour")
	{
		$price_slot=$row_service['price_hourly'];
	}
	elseif ($_POST['invoice_duration'] == "month")
	{
		$price_slot=$row_service['price_monthly'];
	}
	elseif ($_POST['invoice_duration'] == "year")
	{
		$price_slot=$row_service['price_year']*12;
	}
	else
	{
		$price_slot=$row_service['price_monthly'];
	}
	
	
	//Game Server Values
	$ip_id = $_POST['ip_id'];
	$ip = $db->getIpById($ip_id);
	$max_players = $_POST['max_players'];
	$qty = $_POST['qty'];
	$invoice_duration = $_POST['invoice_duration'];
	$user_id = $_SESSION['user_id'];
	$remote_control_password = $_POST['remote_control_password'];
	$ftp_password = $_POST['ftp_password'];
	$tax_amount = $settings['tax_amount'];
	$currency = $settings['currency'];
	
	/*
	Cast $_REQUEST['service_id'] to an int and then check if its value is higher than 0 before using it in the WHERE clause.
	Checking if it's higher than 0 because if it's a non-numeric value, after casting it to an int it'll be 0.
	*/	
	if($service_id !== 0) $where_service_id = " WHERE service_id=".$db->realEscapeSingle($service_id); else $where_service_id = "";
	$qry_services = "SELECT * FROM OGP_DB_PREFIXbilling_services".$where_service_id;
	$services = $db->resultQuery($qry_services);			
	foreach ($services as $key => $row) {	
	if($max_players < $row['slot_min_qty'] || $qty < 1){
		$max_players = $row['slot_min_qty'];
		$qty = 1;
		}
	/*
	An extra check added for the inverse: check max_players against slot_max_qty. 
	It would be good to do in the event someone is only selling a max of 16 slots per server.
	*/
	elseif ($max_players > $row['slot_max_qty'])
		{
		$max_players = $row['slot_max_qty'];	
		}
	}
	
	$price = $max_players*$price_slot*$qty;
		
	if( isset( $_POST["add_to_cart"] ) )
	{
		if( isset( $_SESSION['CART'] ) )
		{
			$i = count( $_SESSION['CART'] );
			$i++;
		}
		else
		{
			$i = 0;
		}
		
		$_SESSION['CART'][$i] = array( "cart_id" => $i,
									   "service_id" => $service_id,
									   "home_name" => $home_name, 
									   "ip" => $ip_id,
									   "max_players" => $max_players, 
									   "qty" => $qty, 
									   "invoice_duration" => $invoice_duration, 
									   "price" => $price, 
									   "remote_control_password" => $remote_control_password, 
									   "ftp_password" => $ftp_password,
									   "tax_amount" => $tax_amount,
									   "currency" => $currency,
									   "paid" => 0);
		echo '<meta http-equiv="refresh" content="0;url=?m=simple-billing&amp;p=cart">';
	}
}
?>
