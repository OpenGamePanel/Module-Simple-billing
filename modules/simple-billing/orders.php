<?php 
function exec_ogp_module()
{
	error_reporting(E_ALL);
	
	global $db,$settings;
	
	if(isset($_POST['remove']))
	{
		$query_delete_order = $db->query("DELETE FROM OGP_DB_PREFIXbilling_orders WHERE cart_id=".$db->realEscapeSingle($_POST['cart_id']));
		$query_delete_order = $db->query("DELETE FROM OGP_DB_PREFIXbilling_carts WHERE cart_id=".$db->realEscapeSingle($_POST['cart_id']));
	}
	if(isset($_POST['paid']))
	{
		$query_set_as_paid =  $db->query("UPDATE OGP_DB_PREFIXbilling_carts
										  SET paid=1
										  WHERE cart_id=".$db->realEscapeSingle($_POST['cart_id']));
	}
	$status_array = array ( "not_paid" => 0,
							"paid" => 1,
							"procesing_payment" => 2,
							"paid_and_installed" => 3
						  );
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
	<h2><?php print_lang("orders");?></h2>
	<?php

	foreach($status_array as $status => $paid_value)
	{
		$carts = $db->resultQuery("SELECT * FROM OGP_DB_PREFIXbilling_carts WHERE paid=" . $db->realEscapeSingle($paid_value) . ";");

		if( $carts > 0 )
		{
			?>
		<h4><?php print_lang($status);?></h4><?php
			foreach($carts as $cart) 
			{
			?>
		<center>
			<table style="width:100%;text-align:center;" class="center">
				<tr>
					<th><?php print_lang("login");?></th>
					<th><?php print_lang("cart_id");?></th>
					<th><?php print_lang("order_id");?></th>
					<th><?php print_lang("home_name");?></th>
					<th><?php print_lang("price");?></th>
				<?php
				if($status == "paid_and_installed")
				{?>
					<th><?php print_lang("installation_and_expiration_date");?></th>
				<?php
				}?>
				</tr>
				<?php  
				$orders = $db->resultQuery("SELECT * FROM OGP_DB_PREFIXbilling_orders WHERE cart_id=".$db->realEscapeSingle($cart['cart_id']));
				$subtotal = 0;
				foreach($orders as $order) 
				{
				if($order['qty'] > 1)
					$order['invoice_duration'] = $order['invoice_duration']."s";
				?>
				<tr class="tr">
					<td><a href="?m=user_admin&p=edit_user&user_id=<?php echo $order['user_id'];?>" ><?php $user = $db->getUserById($order['user_id']); echo $user['users_login'];?></a></td>
					<td><b class="success"><?php echo $order['cart_id'];?></b></td>
					<td><b class="success"><?php echo $order['order_id'];?></b></td>
					<td><?php echo $order['home_name']." [ ".$order['max_players']." ".get_lang('slots').", ".$order['qty']." ".get_lang($order['invoice_duration'])." ]";?></td>
					<td><?php echo $order['price'].$cart['currency'];?></td>
					<?php
					if($status == "paid_and_installed")
					{
						$warning_end_date = $order['end_date'] < date('YmdHi') ? "<b style='color:red;'>".get_lang('expired')."</b>":"";
						$warning_finish_date = $order['finish_date'] < date('YmdHi') ? "<b style='color:red;'>".get_lang('removed')."</b>":"";
						$warning_finish_date = ($order['end_date'] == '-2' and $order['finish_date'] != '-2') ? "&nbsp;<b style='color:green;'>".get_lang('extended')."</b>":$warning_finish_date;
						$end_date = new DateTime($order['end_date']);
						$formated_end_date = ($order['end_date'] != '-1' and $order['end_date'] != '-2')? $end_date->format('d/m/Y H:i') : "";
						$finish_date = new DateTime($order['finish_date']);
						$formated_finish_date = $order['finish_date'] != '-2' ? $finish_date->format('d/m/Y H:i') : "";
						echo '<td>'.get_lang('expiration_date').": <b>$formated_end_date$warning_end_date</b>";
						echo '<br>'.get_lang('removal_date').": <b>$formated_finish_date$warning_finish_date</b></td>";
					}
					?>
			    </tr><?php 
				$subtotal += $order['price'];
				}
				$total = $subtotal+($settings['tax_amount']/100*$subtotal);
				?>
				<tr>
					<td>
				<?php
				if ($status == "not_paid")
				{
					?>
					 <form method="post" action="">
					  <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
					  <input name="paid" type="submit" value="<?php print_lang("set_as_paid");?>">
					 </form>
					<?php
				}
				elseif($status == "paid")
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
				elseif($status == "procesing_payment")
				{
					?>
					 <form method="post" action="">
					  <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
					  <input name="paid" type="submit" value="<?php print_lang("set_as_paid");?>">
					 </form>
					<?php
				}
				elseif($status == "paid_and_installed")
				{
					?>
					 <form method="post" action="?m=simple-billing&p=bill">
					  <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
					  <input name="paid" type="submit" value="<?php print_lang("see_invoice");?>">
					 </form>
					<?php
				}
				?>
					</td>
					<td>
					 <form method="post" action="">
					  <input type="hidden" name="cart_id" value="<?php echo $order['cart_id'];?>">
					  <input name="remove" type="submit" value="<?php print_lang("remove_cart");?>">
					 </form>
					</td>
					<td>
					 <?php echo get_lang('subtotal')." <b>".number_format( $subtotal , 2 ).$cart['currency']."</b>"; ?>
					</td>
					<td>
					 <?php echo get_lang('tax')." <b>".$settings['tax_amount']."% (".number_format( $settings['tax_amount']/100*$subtotal, 2 ).$cart['currency'].")</b>"; ?>
					</td>
					<td>
					 <?php echo get_lang('total')." <b>".number_format( $total , 2 ).$cart['currency']."</b>"; ?>
					</td>
					<?php
					if($status == "paid_and_installed")
					{
					?>
					<td>
					 <?php echo get_lang('installation_date')." <b>".$cart['date']."</b>"; ?>
					</td>
					<?php
					}
					?>
				</tr>
			</table>
		</center>
				<?php
			}
		}
	}
}
?>
