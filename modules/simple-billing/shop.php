<?php
/*
 *
 * OGP - Open Game Panel
 * Copyright (C) 2008 - 2017 The OGP Development Team
 *
 * http://www.opengamepanel.org/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */

function exec_ogp_module()
{	
	global $db, $view;

	$settings = $db->getSettings();
		
	if (isset($_POST['save']) AND !empty($_POST['description']))
	{
		$new_description = str_replace("\\r\\n", "<br>", $_POST['description']);
		$service = $_POST['service_id'];
		
		$change_description = "UPDATE OGP_DB_PREFIXbilling_services
						       SET description ='".$db->realEscapeSingle($new_description)."'
						       WHERE service_id=".$db->realEscapeSingle($service);
		$save = $db->query($change_description);
	}
	?>
	<table class="center">
	<tr>
	<td>
	<a href="?m=simple-billing&p=cart"><img SRC="images/cart.png" BORDER="0" WIDTH=22 HEIGHT=20/><?php print_lang('your_cart');?></a>
	</td>
	</tr>
	<tr>
	<td>
	<?php 
	echo date('d-m-Y');
	?>
	</td>
	</tr>
	<tr>
	<td>
	<?php 
	echo date('H:i');
	?>
	</td>
	</tr>
	</table>
	<?php 
	// Shop Form
	if(intval($_REQUEST['service_id']) !==0) $where_service_id = " WHERE service_id=".intval($_REQUEST['service_id']); else $where_service_id = "";
	$qry_services = "SELECT * FROM OGP_DB_PREFIXbilling_services".$where_service_id;
	$services = $db->resultQuery($qry_services);
	
	if (isset($_REQUEST['service_id']) && $services === false) {
		$view->refresh('home.php?m=simple-billing&p=shop');
		return;
	}
	
	foreach ($services as $key => $row) {
		$service_id[$key] = $row['service_id'];
		$home_cfg_id[$key] = $row['home_cfg_id'];
		$mod_cfg_id[$key] = $row['mod_cfg_id'];
		$service_name[$key] = $row['service_name'];
		$remote_server_id[$key] = $row['remote_server_id'];
		$slot_max_qty[$key] = $row['slot_max_qty'];
		$slot_min_qty[$key] = $row['slot_min_qty'];
		$price_hourly[$key] = $row['price_hourly'];
		$price_monthly[$key] = $row['price_monthly'];
		$price_year[$key] = $row['price_year'];
		$description[$key] = $row['description'];
		$img_url[$key] = $row['img_url'];
		$ftp[$key] = $row['ftp'];
		$install_method[$key] = $row['install_method'];
		$manual_url[$key] = $row['manual_url'];
		$access_rights[$key] = $row['access_rights'];
	}
	array_multisort($service_name,
					$service_id,
					$home_cfg_id,
					$mod_cfg_id,
					$remote_server_id,
					$slot_max_qty,
					$slot_min_qty,
					$price_hourly,
					$price_monthly,
					$price_year,
					$description,
					$img_url,
					$ftp,
					$install_method,
					$manual_url,
					$access_rights, SORT_DESC, $services);
	?>
	<div style="border-left:10px solid transparent;">
	<?php		
	foreach( $services as $row )
	{
		if(!isset($_REQUEST['service_id']))
		{
			?>
			<div style="float:left; border: 4px solid transparent;border-bottom: 25px solid transparent;">
			<form action="" method="POST">
				<input name="service_id" type="hidden" value="<?php echo $row['service_id'];?>" />
				<input type="image" src="<?php echo $row['img_url'] ;?>" width=280 height=132 border=0 alt="Bad Image" onsubmit="submit-form();" value="More Info" />
				<center><b><?php echo $row['service_name'];?></b></center>
				<center><em style="text-align:center;background-color:orange;color:blue;"><?php echo get_lang('starting_on') . " <b>" .
				floatval(round(($row['price_monthly']*$row['slot_min_qty']),2 )) . "</b>&nbsp;" . $settings['currency'] . "/" . get_lang('month') . 
				" (" . $row['slot_min_qty'] . " " . get_lang('slots') . ").";?></em></center>
			</form>
			</div>
			<?php 
		}		else
		{	
			?>
			<div style="float:left; border: 4px solid transparent;border-bottom: 25px solid transparent;">
			<img src="<?php echo $row['img_url'] ;?>" width=280 height=132 border=0 alt="Bad Image">
			<center><b><?php echo $row['service_name']."</b></center>";
			$isAdmin = $db->isAdmin($_SESSION['user_id'] );
			if($isAdmin)
			{
				if(!isset($_POST['edit']))
				{
					echo "<p style='color:gray;width:280px;' >$row[description]<p>";
					echo "<form action='' method='post'>".
						 "<input type='hidden' name='service_id' value='$row[service_id]' />".
						 "<input type='submit' name='edit' value='" . get_lang('edit') . "' />".
						 "</form>";
				}
				else
				{
					echo "<form action='' method='post'>".
						 "<textarea style='resize:none;width:280px;height:132px;' name='description' >".str_replace("<br>", "\r\n", $row['description'])."</textarea><br>".
						 "<input type='hidden' name='service_id' value='$row[service_id]' />".
						 "<input type='submit' name='save' value='" . get_lang('save') . "' />".
						 "</form>";
				}
			}
			else
				echo "<p style='color:gray;width:280px;' >$row[description]<p>";
			?>
			</div>
			<table style="width:420px;float:left;">
			<form method="post" action="?m=simple-billing&p=add_to_cart<?php if(isset($_POST['service_id'])) echo "&service_id=".$_POST['service_id'];?>">
			<tr>
			<td align="right"><?php print_lang('service_name');?> ::</td>
			<td align="left">
			<input type="text" name="home_name" size="40" value="<?php echo $row['service_name'];?>">
			</td>
			<tr>
			<td align="right"><?php print_lang('rcon_pass');?> ::</td>
			<td align="left">
			<input type="text" name="remote_control_password" size="15" value="<?php echo genRandomString(10);?>">
			</td>
			</tr>
			<?php
			if($row['ftp'] == "enabled")
			{
			?>
				<tr>
				<td align="right"><?php print_lang('ftp_pass');?> ::</td>
				<td align="left">
				<input type="text" name="ftp_password" size="15" value="<?php echo genRandomString(10);?>">
				</td>
				</tr>
			<?php
			}
			?>
			<tr>
			  <td align="right"><?php print_lang('available_ips');?> ::</td>
			  <td align="left">
			  <select name='ip_id'>
			<?php
			$qry_ip = "SELECT ip_id,ip FROM OGP_DB_PREFIXremote_server_ips WHERE remote_server_id=".$db->realEscapeSingle($row['remote_server_id']);
			$ips = $db->resultQuery($qry_ip);

			foreach($ips as $ip)
			{
				printf("<option value='%s'>%s</option>", $ip['ip_id'], $ip['ip']);
			}?>
			  </select>
			  </td>
			</tr>
			<tr> 
			  <td align="right"><?php print_lang('max_players');?> ::</td>
			  <td  align="left">
			  <select name="max_players">
			  <?php 
			  $players=$row['slot_min_qty'];
			  while($players<=$row['slot_max_qty'])
			  {
			  echo "<option value='$players'>$players</option>";
			  $players++;
			  }
			  ?>
			  </select>
			  </td>
			</tr>
			<tr> 
			  <td align="right"><?php print_lang('invoice_duration');?> ::</td>
			  <td align="left">
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
			  </td>
			</tr>
			<tr>
			  <td align="left" colspan="2">
			  	<input name="service_id" type="hidden" value="<?php echo $row['service_id'];?>"/>
				<input type="submit" name="add_to_cart" value="<?php print_lang('add_to_cart');?>"/>
			  </form>
			  </td>
			</tr>
			<tr>
			<td align="left" colspan="2">
			<form action ="?m=simple-billing&p=shop" method="POST">
			  <button><< <?php print_lang('back_to_list');?></button>
			</form>
			</td>
			</tr>
			</table>
			<?php
		}
	}
	?>
	</div>
	<?php  
}
?>
