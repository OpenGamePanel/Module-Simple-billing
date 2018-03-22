<?php
if(!isset($_POST['cart_id']) or !is_numeric($_POST['cart_id']))
	exit();
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/skrill-ipn_errors.log');
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

$cart_id = $_POST['cart_id'];

$cart_price_info = $db->resultQuery( "SELECT price,tax_amount,currency 
									 FROM OGP_DB_PREFIXbilling_carts AS cart
									 JOIN
									 OGP_DB_PREFIXbilling_orders AS orders  
									 ON 
									 orders.cart_id=cart.cart_id
									 WHERE cart.cart_id=".$db->realEscapeSingle($cart_id));

if(!$cart_price_info or empty($cart_price_info))	
	exit();

$cart_price = number_format( $cart_price_info[0]['price'] + (($cart_price_info[0]['price']/100)*$cart_price_info[0]['tax_amount']) , 2 );
$cart_currency = $cart_price_info[0]['currency'];
// Validate the Moneybookers signature
$concatFields = $panel_settings['skrill_merchant_id'].
				$_POST['transaction_id'].
				strtoupper($panel_settings['skrill_secret_word']).
				$cart_price.
				$cart_currency.
				$_POST['status'];

// Ensure the signature is valid, the status code == 2,
// and that the money is going to you
if (strtoupper(md5($concatFields)) == $_POST['md5sig']
    && $_POST['status'] == 2
    && $_POST['pay_to_email'] == $panel_settings['skrill_email'])
{
	$body = 'Paid to email		: '.$_POST['pay_to_email']."<br>".
			'Currency			: '.$_POST['currency']."<br>".
			'Amount				: '.$_POST['amount']."<br>".
			'Payment type		: '.$_POST['payment_type']."<br>".
			'Transaction ID		: '.$_POST['transaction_id']."<br>".
			'Paid from email	: '.$_POST['pay_from_email']."<br>".
			'CART ID			: '.$_POST['cart_id']."<br>";

	
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
							 WHERE cart_id=".$cart_id);
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
	mymail($panel_settings['skrill_email'], $subject, $body, $panel_settings);
}
?>
