<?php
function saveOrderToDb($user_id,$service_id,$home_name,$ip,$max_players,$qty,$invoice_duration,$price,$remote_control_password,$ftp_password,$cart_id,$home_id = "0",$extended = "0"){
	global $db;
	$fields['user_id'] = $user_id;
	$fields['service_id'] = $service_id;
	$fields['home_name'] = $home_name;
	$fields['ip'] = $ip;
	$fields['max_players'] = $max_players;
	$fields['qty'] = $qty;
	$fields['invoice_duration'] = $invoice_duration;
	$fields['price'] = $price;
	$fields['remote_control_password'] = $remote_control_password;
	$fields['ftp_password'] = $ftp_password;
	$fields['cart_id'] = $cart_id;
	$fields['home_id'] = $home_id;
	$fields['extended'] = $extended;
	return $db->resultInsertId( 'billing_orders', $fields );
}

function assignOrdersToCart($user_id,$tax_amount,$currency){
	global $db;
	$fields['user_id'] = $user_id;
	$fields['paid'] = '0';
	$fields['tax_amount'] = $tax_amount;
	$fields['currency'] = $currency;
	return $db->resultInsertId( 'billing_carts', $fields );
}

function exec_ogp_module()
{
	error_reporting(E_ALL);
	
	global $db,$view,$settings;
	
	$user_id = $_SESSION['user_id'];
	
	
	if( isset( $_POST["buy"] ) or isset( $_POST["pay_paypal"] ) or isset( $_POST["pay_paygol"] ) or isset( $_POST["pay_skrill"] ) or isset( $_POST["pay_robokassa"] ) )
	{
		if( isset( $_SESSION['CART'] ) )
		{
			$orders = $_SESSION['CART'];
			// Fill The Cart on DB
			$cart_id = assignOrdersToCart($user_id,$settings['tax_amount'],$settings['currency']);
			foreach($orders as $order) 
			{
				$service_id = $order['service_id'];
				$home_name = $order['home_name'];
				$ip = $order['ip'];
				$max_players = $order['max_players'];
				$qty = $order['qty'];
				$invoice_duration = $order['invoice_duration'];
				$price = $order['price'];
				$remote_control_password = $order['remote_control_password'];
				$ftp_password = $order['ftp_password'];
				//Save order to DB
				saveOrderToDb($user_id,$service_id,$home_name,$ip,$max_players,$qty,$invoice_duration,$price,$remote_control_password,$ftp_password,$cart_id);
			}
			// Remove Cart From Session
			unset($_SESSION['CART']);
		}
		else
		{
			$cart_id = $_POST['cart_id'];
		}
		
		if ( !empty( $cart_id ) and isset( $_POST["pay_paypal"] ) and $settings['paypal'] == "1" )
		{
			echo '<meta http-equiv="refresh" content="0;url=home.php?m=simple-billing&p=paypal&cart_id='.$cart_id.'" >';
		}
		elseif ( !empty( $cart_id ) and isset( $_POST["pay_paygol"] ) and $settings['paygol'] == "1" )
		{
			echo '<meta http-equiv="refresh" content="0;url=home.php?m=simple-billing&p=paygol&cart_id='.$cart_id.'" >';
		}
		elseif ( !empty( $cart_id ) and isset( $_POST["pay_skrill"] ) and $settings['skrill'] == "1" )
		{
			echo '<meta http-equiv="refresh" content="0;url=home.php?m=simple-billing&p=skrill&cart_id='.$cart_id.'" >';
		}
		elseif ( !empty( $cart_id ) and isset( $_POST["pay_robokassa"] ) and $settings['robokassa'] == "1" )
		{
			echo '<meta http-equiv="refresh" content="0;url=home.php?m=simple-billing&p=robokassa&cart_id='.$cart_id.'" >';
		}
	}
	
	if( isset( $_POST["extend"] ) or isset( $_POST["extend_and_pay_paypal"] ) or isset( $_POST["extend_and_pay_paygol"] ) or isset( $_POST["extend_and_pay_skrill"] ) or isset( $_POST["extend_and_pay_robokassa"] ) )
	{
		$orders = $db->resultQuery("SELECT * FROM OGP_DB_PREFIXbilling_orders WHERE order_id=".$db->realEscapeSingle($_POST['order_id']));
		// Fill The Cart on DB
		$cart_id = assignOrdersToCart($user_id,$settings['tax_amount'],$settings['currency']);
		foreach($orders as $order) 
		{
			$service_id = $order['service_id'];
			$home_name = $order['home_name'];
			$ip = $order['ip'];
			$max_players = $order['max_players'];
			$qty = $_POST['qty'];
			$invoice_duration = $_POST['invoice_duration'];
			$remote_control_password = $order['remote_control_password'];
			$ftp_password = $order['ftp_password'];
			$home_id = $order['home_id'];
			
			$services = $db->resultQuery( "SELECT * 
										   FROM OGP_DB_PREFIXbilling_services 
										   WHERE service_id=".$db->realEscapeSingle($service_id) );
			$service = $services[0];
			//Calculating Price
			switch ($_POST['invoice_duration']) 
			{
				case "hour":
					$price_slot = $service['price_hourly'];
					break;
				case "month":
					$price_slot = $service['price_monthly'];
					break;
				case "year":
					$price_slot = $service['price_year']*12;
					break;
			}
			$price = $max_players*$price_slot*$_POST['qty'];
			//Save order to DB
			$order_id = saveOrderToDb($user_id,$service_id,$home_name,$ip,$max_players,$qty,$invoice_duration,$price,$remote_control_password,$ftp_password,$cart_id,$home_id,"1");
			//Change the old order expiration to -2 so it can not be extended, since there is a new order managing the same game home.
			$db->query( "UPDATE OGP_DB_PREFIXbilling_orders
						 SET end_date=-2
						 WHERE order_id=".$db->realEscapeSingle($_POST['order_id']));
		}
		
		if ( !empty( $cart_id ) and isset( $_POST["extend_and_pay_paypal"] ) and $settings['paypal'] == "1" )
		{
			echo '<meta http-equiv="refresh" content="0;url=home.php?m=simple-billing&p=paypal&cart_id='.$cart_id.'" >';
		}
		elseif ( !empty( $cart_id ) and isset( $_POST["extend_and_pay_paygol"] ) and $settings['paygol'] == "1" )
		{
			echo '<meta http-equiv="refresh" content="0;url=home.php?m=simple-billing&p=paygol&cart_id='.$cart_id.'" >';
		}
		elseif ( !empty( $cart_id ) and isset( $_POST["extend_and_pay_skrill"] ) and $settings['skrill'] == "1" )
		{
			echo '<meta http-equiv="refresh" content="0;url=home.php?m=simple-billing&p=skrill&cart_id='.$cart_id.'" >';
		}
		elseif ( !empty( $cart_id ) and isset( $_POST["extend_and_pay_robokassa"] ) and $settings['robokassa'] == "1" )
		{
			echo '<meta http-equiv="refresh" content="0;url=home.php?m=simple-billing&p=robokassa&cart_id='.$cart_id.'" >';
		}
	}
	
	if(isset($_POST['remove']))
	{
		$cart_id = $_POST['cart_id'];
		if( isset( $_SESSION['CART'][$cart_id] ) )
		{
			unset($_SESSION['CART'][$cart_id]);
		}
		$order_id = $_POST['order_id'];
		$db->query( "DELETE FROM OGP_DB_PREFIXbilling_orders WHERE order_id=".$db->realEscapeSingle($order_id) );
		$orders_in_cart = $db->resultQuery( "SELECT * FROM OGP_DB_PREFIXbilling_orders WHERE cart_id=".$db->realEscapeSingle($cart_id) );
		if( !$orders_in_cart )
		{
			$db->query( "DELETE FROM OGP_DB_PREFIXbilling_carts WHERE cart_id=".$db->realEscapeSingle($cart_id) );
		}

	}
		
	?>
	<style>
	h4 {
		width:250px;
		height:25px;
		background:#f5f5f5;
		border-top-style:solid;
		border-top-color:#afafaf;
		border-top-width:1px;
		border-style: solid;
		border-color: #CFCFCF;
		border-width: 1px;
		padding-top:8px;
		text-align: center;
		font-family:"Trebuchet MS";
	}
	</style>
	<h2><?php print_lang("your_cart");?></h2>
	<?php
	if( isset($_SESSION['CART']) and !empty($_SESSION['CART']) )
	{
		$carts[0] = $_SESSION['CART'];
	}

	$user_carts = $db->resultQuery( "SELECT * FROM OGP_DB_PREFIXbilling_carts WHERE user_id=".$db->realEscapeSingle($user_id) );
	
	if( $user_carts >=1 )
	{
		foreach ( $user_carts as $user_cart )
		{
			$cart_id = $user_cart['cart_id'];
			$carts[$cart_id] = $db->resultQuery( "SELECT * FROM OGP_DB_PREFIXbilling_carts AS cart JOIN
																OGP_DB_PREFIXbilling_orders AS orders  
																ON orders.cart_id=cart.cart_id
																WHERE cart.cart_id=".$db->realEscapeSingle($cart_id) );
		}
	}
	
	if( empty( $carts ) )
	{
		print_failure( get_lang('there_are_no_orders_in_cart') );
		?>		
		<a href="?m=simple-billing&p=shop"><?php print_lang('back'); ?></a>
		<?php
		return;
	}
	foreach ( $carts as $orders )
	{
		if( !empty( $orders ) )
		{
			?>
	<center>
		<table style="width:95%;text-align:center;" class="center">
			<tr>
			 <th>
			CART ID</th>
			 <th>
			<?php print_lang("order_desc");?></th>
			 <th>
			<?php print_lang("price");?>
			 </th>
			 <?php
			 if(isset($orders[0]['paid']) and $orders[0]['paid'] == 3)
			 {
			 ?>
			 <th>
			 <?php print_lang('expiration_date');?>
			 </th>
			 <th>
			 <?php print_lang('removal_date');?>
			 </th>
			 <?php
			 }
			 ?>
			 <th>
			 </th>
			</tr>
			<?php 
			$subtotal = 0;
			foreach($orders as $order)
			{
				if ( $order['qty'] > 1 ) 
					$order['invoice_duration'] = $order['invoice_duration']."s";

				$subtotal += $order['price'];
				?>
			<tr class="tr">
			 <td>
				<?php
					echo "<b>".$order['cart_id']."</b>";
				?>
			 </td>
			 <td>
				<?php 
				echo "<b>".$order['home_name']."</b> [".$order['qty']." ".get_lang($order['invoice_duration']).", ".$order['max_players']." ".get_lang('slots')."]";
				?>
			 </td>
			 <td>
				<?php 
				echo $order['price'].$order['currency'];
				?>
			 </td>
				<?php
				if($order['paid'] == 0)
				{
					?>
			 <td>
			  <form method="post" action="">
			   <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
			   <input type="hidden" name="order_id" value="<?php echo @$order['order_id'];?>">
			   <input type="submit" name="remove" value="<?php print_lang("remove_from_cart");?>">
			  </form>
			 </td><?php
				}
				elseif($order['paid'] == 3)
				{
					$warning_end_date = $order['end_date'] < date('YmdHi') ? "<b style='color:red;'>".get_lang('expired')."</b>" : "";
					$warning_finish_date = $order['finish_date'] < date('YmdHi') ? "<b style='color:red;'>".get_lang('removed')."</b>" : "";
					$warning_finish_date = ($order['end_date'] == '-2' and $order['finish_date'] != '-2') ? "&nbsp;<b style='color:green;'>".get_lang('extended')."</b>":$warning_finish_date;
					$end_date = new DateTime($order['end_date']);
					$formated_end_date = ($order['end_date'] != '-1' and $order['end_date'] != '-2') ? $end_date->format('d/m/Y H:i') : "";
					$finish_date = new DateTime($order['finish_date']);
					$formated_finish_date = $order['finish_date'] != '-2' ? $finish_date->format('d/m/Y H:i') : "";
				?>
			 <td>
				<?php echo "$formated_end_date$warning_end_date";?>
			 </td>
			 <td>
				<?php echo "$formated_finish_date$warning_finish_date";?>
			 </td>
			<?php
				}
				
				if( isset( $order['end_date'] ) and $order['end_date'] == "-1" )
				{
					?>
			 <td>
			  <form method="post" action="">
			   <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
			   <input type="hidden" name="order_id" value="<?php echo $order['order_id'];?>">
			   <select name="qty">
					<?php 
					$qty=1;
					while($qty<=12)
					{
					echo "<option value='$qty'>$qty</option>";
					$qty++;
					}
					?>
			   </select>
			   <select name="invoice_duration">
					<?php
					if( $settings['hourly'] == 1) echo '<option value="hour">'.get_lang('hours').'</option>';
					if( $settings['monthly'] == 1) echo '<option value="month">'.get_lang('months').'</option>';
					if( $settings['annually'] == 1) echo '<option value="year">'.get_lang('years').'</option>';
					?>
			   </select>
			   <input type="submit" name="extend" value="<?php print_lang("extend");?>">
			   <?php
			   if($settings['paypal'] == "1")
					echo '<input name="extend_and_pay_paypal" type="submit" value="'.get_lang("extend")." ".get_lang("and")." ".get_lang_f("pay_from", get_lang('paypal')).'">';
				if($settings['paygol'] == "1")
					echo '<input name="extend_and_pay_paygol" type="submit" value="'.get_lang("extend")." ".get_lang("and")." ".get_lang_f("pay_from", get_lang('paygol')).'">';
				if($settings['skrill'] == "1")
					echo '<input name="extend_and_pay_skrill" type="submit" value="'.get_lang("extend")." ".get_lang("and")." ".get_lang_f("pay_from", get_lang('skrill')).'">';
				if($settings['robokassa'] == "1")
					echo '<input name="extend_and_pay_robokassa" type="submit" value="'.get_lang("extend")." ".get_lang("and")." ".get_lang_f("pay_from", get_lang('robokassa')).'">';
			   ?>
			  </form>
			 </td><?php
				}
				?>
			</tr><?php
			}
			?>
		</table>
		<table style="width:95%;text-align:left;" class="center">
			<tr>
			 <td>
			<?php print_lang("subtotal");?></td>
			 <td>
			<?php 
			echo $subtotal.$order['currency'];?>
			 </td>
			</tr>
			<tr>
			 <td>
			<?php print_lang("tax");?></td>
			 <td>
			<?php echo $order['tax_amount'];?>%
			 </td>
			</tr>
			<tr>
			 <td>
			<?php print_lang("total");?>
			 </td>
			 <td>
			<?php 
			  $total = $subtotal+($order['tax_amount']/100*$subtotal);
			  echo number_format( $total , 2 ).$order['currency'];
			?>
			 </td>
			 <td>
			  <?php
			  if($order['paid'] == 1)
			  {
			  ?>
			 <form method="post" action="home.php?m=simple-billing&p=create_servers">
			  <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
			  <?php
			 if($order['extended'] == "1")
			 {
			 ?>
			  <input name="enable_server" type="submit" value="<?php print_lang("enable_server");?>">
			 <?php 
			 }
			 else
			 {
			 ?>
			  <input name="create_server" type="submit" value="<?php print_lang("create_server");?>">
			 <?php 
			 }
			?>
			 </form>
			  <?php
			  }
			  elseif($order['paid'] == 2)
			  {
			  echo get_lang_f("payment_is_pending_of_approval");
			  }
			  elseif($order['paid'] == 3)
			  {
			  ?>
			 <form method="post" action="?m=simple-billing&p=bill">
			  <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
			  <input name="paid" type="submit" value="<?php print_lang("see_invoice");?>">
			 </form>
			  <?php
			  }
			  else
			  {
			  ?>
			 <form method="post" action="">
			  <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
			  <input name="buy" type="submit" value="<?php print_lang("buy");?>">
			  <?php
			   if($settings['paypal'] == "1")
					echo '<input name="pay_paypal" type="submit" value="'.get_lang_f("pay_from", get_lang('paypal')).'">';
				if($settings['paygol'] == "1")
					echo '<input name="pay_paygol" type="submit" value="'.get_lang_f("pay_from", get_lang('paygol')).'">';
				if($settings['skrill'] == "1")
					echo '<input name="pay_skrill" type="submit" value="'.get_lang_f("pay_from", get_lang('skrill')).'">';
				if($settings['robokassa'] == "1")
					echo '<input name="pay_robokassa" type="submit" value="'.get_lang_f("pay_from", get_lang('robokassa')).'">';
			   ?>
			 </form>
			  <?php
			  }
			  ?>
			  </form>
			 </td>
			</tr>
		</table>
	</center>
			<?php
		}
	}
	?>		
	<a href="?m=simple-billing&p=shop"><?php print_lang('back'); ?></a>
	<?php
}
?>
