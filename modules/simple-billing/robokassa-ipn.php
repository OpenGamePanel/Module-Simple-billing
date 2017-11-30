<?php
if(!isset($_REQUEST["OutSum"]) or !isset($_REQUEST["InvId"]) or !isset($_REQUEST["SignatureValue"]))
	exit;

ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/robokassa-ipn_errors.log');
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

// HTTP parameters:
$out_summ = $_REQUEST["OutSum"];
$inv_id = $_REQUEST["InvId"];
$crc = $_REQUEST["SignatureValue"];

$cart_price_info = $db->resultQuery( "SELECT price,tax_amount,currency 
									 FROM OGP_DB_PREFIXbilling_carts AS cart
									 JOIN
									 OGP_DB_PREFIXbilling_orders AS orders  
									 ON 
									 orders.cart_id=cart.cart_id
									 WHERE cart.cart_id=".$db->realEscapeSingle($inv_id));
									 
$cart_price = number_format( $cart_price_info[0]['price'] + (($cart_price_info[0]['price']/100)*$cart_price_info[0]['tax_amount']) , 2 );
$paid_price = $out_summ;
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
	$body = 'CART ID	 = '.$inv_id."<br>".
			'Price		 = '.$cart_price."<br>".
			'<b style="color:red;">Amount paid</b>: '.$paid_price."<br>".
			'<b style="color:red;">Amount owed</b>: '.$new_price."<br>";
	mymail($panel_settings['panel_email_address'], $subject, $body, $panel_settings);
	die("Error: Incorrect payment amount");
}
// your registration data
$mrh_pass2 = $panel_settings['robokassa_securepass2'];   // merchant pass2 here

// HTTP parameters: $out_summ, $inv_id, $crc
$crc = strtoupper($crc);   // force uppercase

// build own CRC
$my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2"));

if (strtoupper($my_crc) != strtoupper($crc))
{
  echo "bad sign\n";
  exit();
}
// perform some action (change order state to paid)
else
{
	$body = 'Amount	paid		: '.$out_summ."<br>".
			'CART ID			: '.$inv_id."<br>";

	
	// Here you can do whatever you want with the variables, for instance inserting or updating data into your Database

	$user_homes = $db->resultQuery( "SELECT * 
									 FROM OGP_DB_PREFIXbilling_carts AS cart
									 JOIN
									 OGP_DB_PREFIXbilling_orders AS orders  
									 ON 
									 orders.cart_id=cart.cart_id
									 WHERE cart.cart_id=".$db->realEscapeSingle($cart_id));

	$query = "UPDATE " . $table_prefix . "billing_carts
			  SET paid=1
			  WHERE cart_id=".$db->realEscapeSingle($cart_id);
					  
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
				}
				elseif ($user_home['invoice_duration'] == "month")
				{
					$end_date = date('YmdHi', strtotime('+'.$user_home['qty'].' month'));
				}
				elseif ($user_home['invoice_duration'] == "year")
				{
					$end_date = date('YmdHi', strtotime('+'.$user_home['qty'].' year'));
				}
				//Set the expiration date to the new order
				$db->query( "UPDATE " . $table_prefix . "billing_orders
							 SET end_date='" . $db->realEscapeSingle($end_date) . "'
							 WHERE order_id=".$db->realEscapeSingle($user_home['order_id']));
							 
				// Set payment/creation date
				$date = date('d/m/Y H:i');
				$db->query( "UPDATE OGP_DB_PREFIXbilling_carts
							 SET date='" . $db->realEscapeSingle($date) . "'
							 WHERE cart_id=".$db->realEscapeSingle($cart_id));
			}
			
			$services = $db->resultQuery( "SELECT * 
										   FROM OGP_DB_PREFIXbilling_services
										   WHERE service_id=".$db->realEscapeSingle($user_home['service_id']));
			$service = $services[0];
			$user_id = $user_home['user_id'];
			$db->assignHomeTo("user", $user_id, $home_id, $service['access_rights']);
			
			$query = "UPDATE " . $table_prefix . "billing_carts
					  SET paid=3
					  WHERE cart_id=".$db->realEscapeSingle($cart_id);
		}
	}
	
	$db->query($query);
	$subject = "Payment done.";
	mymail($panel_settings['panel_email_address'], $subject, $body, $panel_settings);
}
?>
