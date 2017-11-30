<?php
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');

// instantiate the IpnListener class
include('ipnlistener.php');
$listener = new IpnListener();

// Enable sandbox for developers (https://developer.paypal.com)
//$listener->use_sandbox = true;

try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    error_log($e->getMessage());
}

chdir("../../"); /* It just makes life easier */

set_include_path(get_include_path() . PATH_SEPARATOR . "includes/");

/* Includes */
require_once("helpers.php");
require_once("config.inc.php");
require_once("functions.php");
require_once("lib_remote.php");
require_once("lang.php");
require_once("modules/config_games/server_config_parser.php");
ogpLang();

/* Query DB */
$db = createDatabaseConnection($db_type, $db_host, $db_user, $db_pass, $db_name, $table_prefix);

$panel_settings	= $db->getSettings();

$s = ( isset($_SERVER['HTTPS']) and  get_true_boolean($_SERVER['HTTPS']) ) ? "s" : "";
$p = isset($_SERVER['SERVER_PORT']) & $_SERVER['SERVER_PORT'] != "80" ? ":".$_SERVER['SERVER_PORT'] : NULL ;
$this_script = 'http'.$s.'://'.$_SERVER['SERVER_NAME'].$p.$_SERVER['SCRIPT_NAME'];
				
function curPageName() 
{
	return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}

$current_folder_url = str_replace( curPageName(), "", $this_script);

if( empty( $panel_settings['panel_name'] ) )
	$panel_name = "Open Game Panel";
else
	$panel_name = $panel_settings['panel_name'];

$ipn = $_POST;

if(empty($ipn))
{
	exit(0);
}

$to = $ipn['receiver_email'] . ', ' . $ipn['payer_email'];

$body = "<b>PayPal Payment For <a href='".
		$current_folder_url.
		"../../index.php?m=simple-billing&p=shop_guest' >".
		$panel_name."</a></b><br><br>".
		"<h2>Order</h2>".
		"- Item: ".$ipn['item_name']."<br>".
		"- Item number: ".$ipn['item_number']."<br>".
		"- Quantity: ".$ipn['quantity']."<br>".
		"- Shipping: ".$ipn['shipping']."<br>".
		"- Tax: ".$ipn['tax']."<br>".
		"- Currency: ".$ipn['mc_currency']."<br>".
		"- Currency fee: ".$ipn['mc_fee']."<br>".
		"- Currency gross: ".$ipn['mc_gross']."<br>".
		"- Transaction type: ".$ipn['txn_type']."<br>".
		"- Transaction ID: ".$ipn['txn_id']."<br>".
		"- Notify version: ".$ipn['notify_version']."<br><br>".
		"<h2>Payer Info</h2>".
		"- ID: ".$ipn['payer_id']."<br>".
		"- First name: ".$ipn['first_name']."<br>".
		"- Last name: ".$ipn['last_name']."<br>".	
		"- Email: ".$ipn['payer_email']."<br>".
		"- Email status: ".$ipn['payer_status']."<br><br>".
		"<h2>Address</h2>".
		"- Name: ".$ipn['address_name']."<br>".
		"- Street: ".$ipn['address_street']."<br>".
		"- City: ".$ipn['address_city']."<br>".
		"- State: ".$ipn['address_state']."<br>".
		"- Zip: ".$ipn['address_zip']."<br>".
		"- Country code: ".$ipn['address_country_code']."<br>".
		"- Country: ".$ipn['address_country']."<br>".
		"- Residence country code: ".$ipn['residence_country']."<br>".
		"- Address status: ".$ipn['address_status']."<br><br>".
		"<h2>Payment Receiver Info</h2>".
		"- Email: ".$ipn['receiver_email']."<br>".
		"- ID: ".$ipn['receiver_id']."<br><br>".
		"<h2>Payment</h2>".
		"- Type: ".$ipn['payment_type']."<br>".
		"- Date: ".$ipn['payment_date']."<br>".
		"- Status: ".$ipn['payment_status']."<br>";
/*
The processIpn() method returned true if the IPN was "VERIFIED" and false if it
was "INVALID".
*/
if ($verified AND isset( $ipn['payment_status'] ) ) 
{
	$user_homes = $db->resultQuery( "SELECT * 
									 FROM OGP_DB_PREFIXbilling_carts AS cart
									 JOIN
									 OGP_DB_PREFIXbilling_orders AS orders  
									 ON 
									 orders.cart_id=cart.cart_id
									 WHERE cart.cart_id=".$db->realEscapeSingle($ipn['item_number']));
	if( $ipn['payment_status']=="Completed" OR $ipn['payment_status']=="Canceled_Reversal" )
	{  
		$cart_id = $ipn['item_number'];

		$cart_price_info = $db->resultQuery( "SELECT price,tax_amount 
											 FROM OGP_DB_PREFIXbilling_carts AS cart
											 JOIN
											 OGP_DB_PREFIXbilling_orders AS orders  
											 ON 
											 orders.cart_id=cart.cart_id
											 WHERE cart.cart_id=".$db->realEscapeSingle($cart_id));
											 
		$cart_price = number_format( $cart_price_info[0]['price'] + (($cart_price_info[0]['price']/100)*$cart_price_info[0]['tax_amount']) , 2 );
		$paid_price = $ipn['mc_gross'];
		if($cart_price > $paid_price)
		{	
			// If for some reason someone achieves to hack the price then we will just change the order price.
			// By a rule of Three:
			// new price without tax = ( new price with tax * old price without tax ) / old price with tax
			$new_price = ( ($cart_price - $paid_price) * $cart_price_info[0]['price'] ) / $cart_price;
			// we don't want to loose money in this fraudulent transaction, 
			// so if the rounded new price is less than the new price then we sum one cent to the rounded value.
			if($new_price > number_format( $new_price, 2 ))
				$new_price = number_format( $new_price, 2 ) + 0.01;
			
			$subject = "Error: Incorrect payment amount";
			$body = "<b>PayPal Payment For <a href='".
					$current_folder_url.
					"../../index.php?m=simple-billing&p=shop_guest' >".
					$panel_name."</a></b><br><br>".
					"<h2>Order</h2>".
					"- Item: ".$ipn['item_name']."<br>".
					"- Item number: ".$ipn['item_number']."<br>".
					"- Quantity: ".$ipn['quantity']."<br>".
					"- Shipping: ".$ipn['shipping']."<br>".
					"- Tax: ".$ipn['tax']."<br>".
					"- Currency: ".$ipn['mc_currency']."<br>".
					"- Currency fee: ".$ipn['mc_fee']."<br>".
					"- Currency gross: ".$ipn['mc_gross']."<br>".
					"- Transaction type: ".$ipn['txn_type']."<br>".
					"- Transaction ID: ".$ipn['txn_id']."<br>".
					"- Notify version: ".$ipn['notify_version']."<br><br>".
					"<h2>Payer Info</h2>".
					"- ID: ".$ipn['payer_id']."<br>".
					"- First name: ".$ipn['first_name']."<br>".
					"- Last name: ".$ipn['last_name']."<br>".	
					"- Email: ".$ipn['payer_email']."<br>".
					"- Email status: ".$ipn['payer_status']."<br><br>".
					"<h2>Address</h2>".
					"- Name: ".$ipn['address_name']."<br>".
					"- Street: ".$ipn['address_street']."<br>".
					"- City: ".$ipn['address_city']."<br>".
					"- State: ".$ipn['address_state']."<br>".
					"- Zip: ".$ipn['address_zip']."<br>".
					"- Country code: ".$ipn['address_country_code']."<br>".
					"- Country: ".$ipn['address_country']."<br>".
					"- Residence country code: ".$ipn['residence_country']."<br>".
					"- Address status: ".$ipn['address_status']."<br><br>".
					"<h2>Payment Receiver Info</h2>".
					"- Email: ".$ipn['receiver_email']."<br>".
					"- ID: ".$ipn['receiver_id']."<br><br>".
					"<h2>Payment</h2>".
					"- Type: ".$ipn['payment_type']."<br>".
					"- Date: ".$ipn['payment_date']."<br>".
					"- Status: ".$ipn['payment_status']."<br>".
					'<b style="color:red;">amount paid</b>: '.$paid_price."<br>".
					'<b style="color:red;">amount owed</b>: '.$new_price."<br>";
			mymail($panel_settings['panel_email_address'], $subject, $body, $panel_settings);
			die("Error: Incorrect payment amount");
		}
		$query = "UPDATE OGP_DB_PREFIXbilling_carts
				  SET paid=1
				  WHERE cart_id=".$db->realEscapeSingle($ipn['item_number']);
				  
		foreach($user_homes as $user_home)
		{			
			if($user_home['home_id'] != 0)
			{
				$home_id = $user_home['home_id'];
				$home_info = $db->getGameHomeWithoutMods($home_id);
				$server_info = $db->getRemoteServerById($home_info['remote_server_id']);
				$remote = new OGPRemoteLibrary($server_info['agent_ip'], $server_info['agent_port'], $server_info['encryption_key'], $server_info['timeout']);

				if ( isset( $home_info['ftp_password'] ) AND !empty( $home_info['ftp_password'] ) )
				{
					$remote->ftp_mgr("useradd", $home_info['home_id'], $home_info['ftp_password'], $home_info['home_path']);
					$db->changeFtpStatus('enabled',$home_info['home_id']);
				}

				if ($user_home['end_date'] == "0")
				{
					if ($user_home['invoice_duration'] == "hour")
					{
						$add_time = time() + ($user_home['qty'] * 60 * 60);
						$end_date = date('YmdHi',$add_time);
						$period_to_extend = time() + ( ( $user_home['qty'] * 60 * 60 ) + 900 ); // Fifteen minutes to extend or finish the server.
						$finish_date = date('YmdHi',$period_to_extend);
					}
					elseif ($user_home['invoice_duration'] == "month")
					{
						$end_date = date('YmdHi', strtotime('+'.$user_home['qty'].' month'));
						$finish_date = date('YmdHi', strtotime('+'.$user_home['qty'].' month 5 day'));
					}
					elseif ($user_home['invoice_duration'] == "year")
					{
						$end_date = date('YmdHi', strtotime('+'.$user_home['qty'].' year'));
						$finish_date = date('YmdHi', strtotime('+'.$user_home['qty'].' year 15 day'));
					}
			
					//Set the expiration date to the new order
					$db->query("UPDATE OGP_DB_PREFIXbilling_orders
								SET end_date='" . $db->realEscapeSingle($end_date) . "'
								WHERE order_id=". $db->realEscapeSingle($user_home['order_id']));
								 
					$db->query("UPDATE OGP_DB_PREFIXbilling_orders
								SET finish_date='" . $db->realEscapeSingle($finish_date) . "' 
								WHERE order_id=".$db->realEscapeSingle($user_home['order_id']));
								 
					// Set payment/creation date
					$date = date('d/m/Y H:i');
					$db->query("UPDATE OGP_DB_PREFIXbilling_carts
								SET date='$date'
								WHERE cart_id=".$db->realEscapeSingle($ipn['item_number']));
				}
				
				$services = $db->resultQuery( "SELECT * 
											   FROM OGP_DB_PREFIXbilling_services
											   WHERE service_id=".$db->realEscapeSingle($user_home['service_id']));
				$service = $services[0];
				$user_id = $user_home['user_id'];
				$db->assignHomeTo("user", $user_id, $home_id, $service['access_rights']);
				
				$query = "UPDATE OGP_DB_PREFIXbilling_carts
						  SET paid=3
						  WHERE cart_id=".$db->realEscapeSingle($ipn['item_number']);
			}
		}
	}
	elseif( $ipn['payment_status']=="Pending" OR $ipn['payment_status']=="In-Progress" )
	{
		$query = "UPDATE OGP_DB_PREFIXbilling_carts
				  SET paid=2
				  WHERE cart_id=".$db->realEscapeSingle($ipn['item_number']);
	}
	elseif( $ipn['payment_status']=="Reversed" OR $ipn['payment_status']=="Refunded" OR $ipn['payment_status']=="Denied" OR $ipn['payment_status']=="Expired" OR $ipn['payment_status']=="Failed" OR $ipn['payment_status']=="Voided" OR $ipn['payment_status']=="Partially_Refunded" )
	{
		$body .= "- Reason code: ".$ipn['reason_code']; 
				 
		$query = "UPDATE OGP_DB_PREFIXbilling_carts
				  SET paid=0
				  WHERE cart_id=".$db->realEscapeSingle($ipn['item_number']);
		
		foreach($user_homes as $user_home)
		{
			$user_id = $user_home['user_id'];
			
			if($user_home['home_id'] != 0)
			{
				$home_id = $user_home['home_id'];
				$home_info = $db->getGameHomeWithoutMods($home_id);
				$server_info = $db->getRemoteServerById($home_info['remote_server_id']);
				$remote = new OGPRemoteLibrary($server_info['agent_ip'], $server_info['agent_port'], $server_info['encryption_key'], $server_info['timeout']);
				$update_ftp_users = "pure-pw userdel ".$home_id." && pure-pw mkdb";
				$remote->sudo_exec( $update_ftp_users );
				$addresses = $db->getHomeIpPorts($home_id);
				
				foreach($addresses as $address)
				{	
					$server_xml = read_server_config(SERVER_CONFIG_LOCATION."/".$home_info['home_cfg_file']);
					if(isset($server_xml->control_protocol_type))$control_type = $server_xml->control_protocol_type; else $control_type = "";
					$remote->remote_stop_server($home_id,$address['ip'],$address['port'],$server_xml->control_protocol,$home_info['control_password'],$control_type);
				}
				$db->unassignHomeFrom("user", $user_id, $home_id);
				
				/*
				// Remove the game home from db 
				$db->deleteGameHome($home_id);
				
				// Remove the game home files from remote server
				$remote->remove_home($home_info['home_path']);
				
				// Set order as not installed
				$db->resultQuery( "UPDATE OGP_DB_PREFIXbilling_orders
								   SET home_id=0
								   WHERE home_id=".$home_id); 
				*/
			}
		}
	}		  
	$db->query($query);
	$subject = "Payment ".$ipn['payment_status'];
	mymail($to, $subject, $body, $panel_settings);
}

?>
