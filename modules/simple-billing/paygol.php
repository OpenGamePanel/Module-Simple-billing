<?php
function curPageName() 
{
	return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}

function exec_ogp_module()
{
	global $db,$view;
	
	$settings = $db->getSettings();
	
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
				$s = ( isset($_SERVER['HTTPS']) and  get_true_boolean($_SERVER['HTTPS']) ) ? "s" : "";
				$p = isset($_SERVER['SERVER_PORT']) & $_SERVER['SERVER_PORT'] != "80" ? ":".$_SERVER['SERVER_PORT'] : NULL ;
				$this_script = 'http'.$s.'://'.$_SERVER['SERVER_NAME'].$p.$_SERVER['SCRIPT_NAME'];
				$current_folder_url = str_replace( curPageName(), "", $this_script);
				
				echo '<script src="http://www.paygol.com/micropayment/js/paygol.js" type="text/javascript"></script>'.
					 '<form name="pg_frm">'.
				     ' <input type="hidden" name="pg_serviceid" value="'.$settings['paygol_service_id'].'">'."\n".
					 ' <input type="hidden" name="pg_currency" value="'.$settings['currency'].'">'."\n".
					 ' <input type="hidden" name="pg_name" value=\''.$cart['name'].'\'>'."\n".
					 ' <input type="hidden" name="pg_custom" value="'.$cart_id.'">'."\n".
					 ' <input type="hidden" name="pg_price" value="'.number_format( $total , 2 ).'">'."\n".
					 ' <input type="hidden" name="pg_return_url" value="'.urlencode($this_script.'?m=simple-billing&p=cart').'">'."\n".
					 ' <input type="hidden" name="pg_cancel_url" value="'.$this_script.'?m=simple-billing&p=cart">'."\n".
					 ' <input type="hidden" name="pg_notify_url" value="'.$current_folder_url.'modules/simple-billing/paygol-ipn.php">'."\n".
					 ' <input type="image" name="pg_button" class="paygol" src="http://www.paygol.com/micropayment/img/buttons/150/black_en_pbm.png"'.
					 ' border="0" onClick="pg_reDirect(this.form)">'."\n".
					 '</form>';
			}
		}
	}
}
?>
