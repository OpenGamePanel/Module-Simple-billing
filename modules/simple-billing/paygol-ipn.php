<?php
// check that the request comes from PayGol server
if(!in_array($_SERVER['REMOTE_ADDR'],
  array('109.70.3.48', '109.70.3.146', '109.70.3.58'))) {
  header("HTTP/1.0 403 Forbidden");
  die("Error: Unknown IP");
}

ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/paygol-ipn_errors.log');
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

$body = 'message_id	= '.$_GET['message_id']."<br>".
		'shortcode	= '.$_GET['shortcode']."<br>".
		'keyword	= '.$_GET['keyword']."<br>".
		'message	= '.$_GET['message']."<br>".
		'sender		= '.$_GET['sender']."<br>".
		'operator	= '.$_GET['operator']."<br>".
		'country	= '.$_GET['country']."<br>".
		'points		= '.$_GET['points']."<br>".
		'price		= '.$_GET['price']."<br>".
		'currency	= '.$_GET['currency']."<br>".
		'service_id	= '.$_GET['service_id']."<br>".
		'###cart_id	= '.$_GET['custom']."<br>";

$cart_id = $_GET['custom'];

$cart_price_info = $db->resultQuery( "SELECT price,tax_amount 
									 FROM OGP_DB_PREFIXbilling_carts AS cart
									 JOIN
									 OGP_DB_PREFIXbilling_orders AS orders  
									 ON 
									 orders.cart_id=cart.cart_id
									 WHERE cart.cart_id=".$db->realEscapeSingle($cart_id));
									 
$cart_price = number_format( $cart_price_info[0]['price'] + (($cart_price_info[0]['price']/100)*$cart_price_info[0]['tax_amount']) , 2 );
$paid_price = $_GET['price'];
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
	$body = 'message_id	 = '.$_GET['message_id']."<br>".
			'shortcode	 = '.$_GET['shortcode']."<br>".
			'keyword	 = '.$_GET['keyword']."<br>".
			'message	 = '.$_GET['message']."<br>".
			'sender		 = '.$_GET['sender']."<br>".
			'operator	 = '.$_GET['operator']."<br>".
			'country	 = '.$_GET['country']."<br>".
			'points		 = '.$_GET['points']."<br>".
			'price		 = '.$_GET['price']."<br>".
			'currency	 = '.$_GET['currency']."<br>".
			'service_id	 = '.$_GET['service_id']."<br>".
			'CART ID	 = '.$_GET['custom']."<br>".
			'<b style="color:red;">Amount paid</b>: '.$paid_price."<br>".
			'<b style="color:red;">Amount owed</b>: '.$new_price."<br>";
	mymail($panel_settings['panel_email_address'], $subject, $body, $panel_settings);
	die("Error: Incorrect payment amount");
}

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
?>
