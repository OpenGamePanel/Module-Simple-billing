<?php
function curPageName() 
{
	return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}

function exec_ogp_module()
{
	global $db,$view,$settings;
	
	$cart_id = $_GET['cart_id'];

	if(!empty($cart_id))
	{		
		$orders = $db->resultQuery( "SELECT * FROM OGP_DB_PREFIXbilling_orders WHERE cart_id=".$db->realEscapeSingle($cart_id) );
		if( !empty( $orders ) )
		{
			$cart['price'] = 0;
			foreach($orders as $order) 
			{
				if( $order['qty'] > 1 )
					$order['invoice_duration'] = $order['invoice_duration']."s";
				
				$cart['price'] += $order['price'];
				
				if( !isset( $cart['name'] ) )
					$cart['name'] = $order['home_name']."(".$order['qty'].get_lang($order['invoice_duration']).",".$order['max_players'].get_lang('slots').")";
				else
					$cart['name'] .= ' + '.$order['home_name']."(".$order['qty'].get_lang($order['invoice_duration']).",".$order['max_players'].get_lang('slots').")";
			}
			
			$total = $cart['price']+($settings['tax_amount']/100*$cart['price']);
			if ($total === 0)
			{
				$db->query("UPDATE " . $table_prefix . "billing_carts
												SET paid=1
												WHERE cart_id='".$db->realEscapeSingle($cart_id) . "'");
				$view->refresh("home.php?m=simple-billing&p=cart",0);
			}
			else
			{
				$s = ( isset($_SERVER['HTTPS']) and  get_true_boolean($_SERVER['HTTPS']) ) ? "s" : "";
				$p = isset($_SERVER['SERVER_PORT']) & $_SERVER['SERVER_PORT'] != "80" ? ":".$_SERVER['SERVER_PORT'] : NULL ;
				$this_script = 'http'.$s.'://'.$_SERVER['SERVER_NAME'].$p.$_SERVER['SCRIPT_NAME'];
				$current_folder_url = str_replace( curPageName(), "", $this_script);
				
				$lang_array = array('Danish' => 'DA',
									'English' => 'EN',
									'French' => 'FR',
									'German' => 'DE',
									'Polish' => 'PL',
									'Russian' => 'RU',
									'Spanish' => 'ES');
				
				$ogp_lang = !empty($_SESSION['users_lang']) ? $_SESSION['users_lang'] : $settings['panel_language'];
				$skrill_lang = 'EN';
				foreach($lang_array as $userlang => $langcode )
				{
					if($userlang == $ogp_lang)
						$skrill_lang = $langcode;
				}
								
				$url = "https://www.moneybookers.com/app/payment.pl";
				$ipn_url = $current_folder_url.'modules/simple-billing/skrill-ipn.php';
				$return_url = $current_folder_url.'home.php?m=simple-billing&p=cart';
				$amount = number_format( $total , 2 );
				
				$fields = array(
						'pay_to_email' => urlencode($settings['skrill_email']),
						'status_url' => urlencode($ipn_url),
						'language' => $skrill_lang,
						'amount' => urlencode($amount),
						'currency' => $settings['currency'],
						'detail1_description' => urlencode("CART ID: ".$cart_id),
						'detail1_text' => urlencode($cart['name']),
						'return_url' => urlencode($return_url),
						'return_url_text' => urlencode(get_lang('back_to_your_cart')),
						'return_url_target' => '3',
						'cancel_url' => urlencode($return_url),
						'cancel_url_target' => '3',
						'merchant_fields' => 'cart_id',
						'cart_id' => $cart_id
				);

				//url-ify the data for the POST
				foreach($fields as $key=>$value) 
				{ 
					$fields_string .= $key.'='.$value.'&'; 
				}
				$fields_string = rtrim($fields_string, '&');

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POST, 1);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				echo curl_exec($ch);
				curl_close($ch);			 
					 
				echo "<h2>".get_lang_f('redirecting_to',get_lang('skrill'))."</h2>";
				echo "<img style='border:4px dotted white;background:black' src='modules/addonsmanager/loading.gif' width='180' height='180' /img><br><br>";
			}
		}
	}
}
?>
