<?php
function exec_ogp_module()
{
	global $db,$view;
	
	$settings = $db->getSettings();
	
	$cart_id = $_GET['cart_id'];

	if(!empty($cart_id))
	{		
		$orders = $db->resultQuery( "SELECT * FROM OGP_DB_PREFIXbilling_orders WHERE cart_id=".$db->realEscapeSingle($cart_id));
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
				// Setup class
				require_once('paypal.class.php');  // include the class file
				
				$receiver_email = $settings['paypal_email'];
				
				$p = new paypal_class;             // initiate an instance of the class
				//$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // Paypal Sandbox URL for developers (https://developer.paypal.com)
				$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';     // PayPal url
					
				// setup a variable for this script (ie: 'http://www.micahcarrick.com/paypal.php')
				$s = ( isset($_SERVER['HTTPS']) and  get_true_boolean($_SERVER['HTTPS']) ) ? "s" : "";
				$port = isset($_SERVER['SERVER_PORT']) & $_SERVER['SERVER_PORT'] != "80" ? ":".$_SERVER['SERVER_PORT'] : NULL ;
				$this_script = 'http'.$s.'://'.$_SERVER['SERVER_NAME'].$port.$_SERVER['SCRIPT_NAME'];
				
				function curPageName() 
				{
					return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
				}
				
				$current_folder_url = str_replace( curPageName(), "", $this_script);
				
				$p->add_field('business', $receiver_email);
				$p->add_field('currency_code', $settings['currency']);
				$p->add_field('return', $this_script.'?m=simple-billing&p=paid');
				$p->add_field('cancel_return', $this_script.'?m=simple-billing&p=cart');
				$p->add_field('notify_url', $current_folder_url.'modules/simple-billing/paid-ipn.php');
				$p->add_field('item_name', $cart['name']);
				$p->add_field('item_number', $cart_id);
				$p->add_field('amount', number_format( $total , 2 ));
				echo "<h2>".get_lang_f('redirecting_to',get_lang('paypal'))."</h2>";
				echo "<img style='border:4px dotted white;background:black' src='modules/addonsmanager/loading.gif' width='180' height='180' /img>";
				$p->submit_paypal_post(); // submit the fields to paypal
				//$p->dump_fields();      // for debugging, output a table of all the fields
			}
		}
	}
}
?>
