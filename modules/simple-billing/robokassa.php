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
												WHERE cart_id=".$db->realEscapeSingle($cart_id));
				$view->refresh("home.php?m=simple-billing&p=cart",0);
			}
			else
			{
				$mrh_login = $settings['robokassa_merchant_login'];      // your login here
				$mrh_pass1 = $settings['robokassa_securepass1'];   // merchant pass1 here

				// order properties
				$inv_id    = $cart_id; // shop's invoice number 
									   // (unique for shop's lifetime)
				$inv_desc  = urlencode($cart['name']);   // invoice desc
				$out_summ  = number_format( $total , 2 );  // invoice summ

				// build CRC value
				$crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");

				// build URL
				$url = "https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=$mrh_login&".
					   "OutSum=$out_summ&InvId=$inv_id&Desc=$inv_desc&SignatureValue=$crc";
				echo "<h2>".get_lang_f('redirecting_to',get_lang('robokassa'))."</h2>";
				echo "<img style='border:4px dotted white;background:black' src='modules/addonsmanager/loading.gif' width='180' height='180' /img>";
				echo '<meta HTTP-EQUIV="REFRESH" content="0; url='.$url.'">';
			}
		}
	}
}
?>
