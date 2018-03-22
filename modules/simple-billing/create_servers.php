<?php
require_once("includes/lib_remote.php");
require_once("modules/config_games/server_config_parser.php");
function exec_ogp_module()
{
	global $db,$view,$settings;
	$user_id = $_SESSION['user_id'];
	$cart_id = $_POST['cart_id'];
	$cart_paid = $db->resultQuery( "SELECT paid FROM OGP_DB_PREFIXbilling_carts WHERE paid=1 AND cart_id=".$db->realEscapeSingle($cart_id) );
	$isAdmin = $db->isAdmin( $_SESSION['user_id'] );
	if ( $isAdmin )
		$orders = $db->resultQuery( "SELECT * FROM OGP_DB_PREFIXbilling_orders WHERE cart_id=".$db->realEscapeSingle($cart_id) );
	else
		$orders = $db->resultQuery( "SELECT * FROM OGP_DB_PREFIXbilling_orders WHERE cart_id=".$db->realEscapeSingle($cart_id)." AND user_id=".$db->realEscapeSingle($user_id) );
		
	if( !empty($orders) and !empty($cart_paid) )
	{
		foreach($orders as $order)
		{
			$order_id = $order['order_id'];
			$service_id = $order['service_id'];
			$home_name = $order['home_name'];
			$remote_control_password = $order['remote_control_password'];
			$ftp_password = $order['ftp_password'];
			$ip = $order['ip'];
			$max_players = $order['max_players'];
			$user_id = $order['user_id'];
			$extended = $order['extended'] == "1" ? TRUE : FALSE;
			
			//Query service info	
			$service = $db->resultQuery( "SELECT * 
							   FROM OGP_DB_PREFIXbilling_services 
							   WHERE service_id=".$db->realEscapeSingle($service_id) );
							   
			if( !empty( $service[0] ) )
			{
				$home_cfg_id = $service[0]['home_cfg_id'];
				$mod_cfg_id = $service[0]['mod_cfg_id'];
				$remote_server_id = $service[0]['remote_server_id'];
				$ftp = $service[0]['ftp'];
				$install_method = $service[0]['install_method'];
				$manual_url = $service[0]['manual_url'];
				$access_rights = $service[0]['access_rights'];
			}
			else
				return;
						
			if($extended)
			{
				$home_id = $order['home_id'];
				
				//Get The home info without mods in 1 array (Necesary for remote connection).
				$home_info = $db->getGameHomeWithoutMods($home_id);
				
				//Create the remote connection
				$remote = new OGPRemoteLibrary($home_info['agent_ip'],$home_info['agent_port'],$home_info['encryption_key'],$home_info['timeout']);
				
				//Reassign the server
				$db->assignHomeTo( "user", $user_id, $home_id, $access_rights );
				
				//Reenable the FTP account
				if ($ftp == "enabled")
				{
					$remote->ftp_mgr("useradd", $home_info['home_id'], $home_info['ftp_password'], $home_info['home_path']);
					$db->changeFtpStatus('enabled',$home_info['home_id']);
				}
				echo "<h4>".get_lang('success')."</h4><br><p>".get_lang('redirecting_to_game_monitor')."</p><br>";
			}
			else
			{
				//OPTIONS, change it at your choice;
				$extra_params = "";//no extra params defined by default
				$cpu_affinity = "NA";//Affinity to one core/thread of the cpu by number, use NA to disable it
				$nice = "0";//Min priority=19 Max Priority=-19
				
				//Add Game home to database
				$rserver = $db->getRemoteServer($remote_server_id);
				$game_path = "/home/".$rserver['ogp_user']."/OGP_User_Files/simple-billing/";
				$home_id = $db->addGameHome( $remote_server_id, $user_id, $home_cfg_id, $game_path, $home_name, $remote_control_password, $ftp_password);
				
				//Add IP:Port Pair to the Game Home
				$add_port = $db->addGameIpPort( $home_id, $ip, $db->getNextAvailablePort($ip,$home_cfg_id) );
				
				//Assign the Game Mod to the Game Home
				$mod_id = $db->addModToGameHome( $home_id, $mod_cfg_id );
				$db->updateGameModParams( $max_players, $extra_params, $cpu_affinity, $nice, $home_id, $mod_cfg_id );
				$db->assignHomeTo( "user", $user_id, $home_id, $access_rights );
				
				//Get The home info without mods in 1 array (Necesary for remote connection).
				$home_info = $db->getGameHomeWithoutMods($home_id);
				
				//Create the remote connection
				$remote = new OGPRemoteLibrary($home_info['agent_ip'],$home_info['agent_port'],$home_info['encryption_key'],$home_info['timeout']);
								
				//Get Full home info in 1 array
				$home_info = $db->getGameHome($home_id);
				
				//Read the Game Config from the XML file
				$server_xml = read_server_config(SERVER_CONFIG_LOCATION."/".$home_info['home_cfg_file']);
				
				//Get Values from XML
				$modkey = $home_info['mods'][$mod_id]['mod_key'];
				$mod_xml = xml_get_mod($server_xml, $modkey);
				$installer_name = $mod_xml->installer_name;
				$mod_cfg_id = $home_info['mods'][$mod_id]['mod_cfg_id'];
				
				//Get Preinstall commands from db
				$game_mod_precmd = $db->resultQuery("SELECT DISTINCT precmd FROM OGP_DB_PREFIXgame_mods WHERE mod_id='" . $db->realEscapeSingle($mod_id) . "'");
				if ($game_mod_precmd[0]['precmd'] === NULL OR empty($game_mod_precmd[0]['precmd']))
				{
					$config_mod_precmd = $db->resultQuery("SELECT DISTINCT def_precmd FROM OGP_DB_PREFIXconfig_mods WHERE mod_cfg_id='" . $db->realEscapeSingle($mod_cfg_id) . "'");
					if ($config_mod_precmd[0]['def_precmd'] === NULL OR empty($config_mod_precmd[0]['def_precmd']))
						$precmd = "";
					else
						$precmd = $config_mod_precmd[0]['def_precmd'];
				}
				else
					$precmd = $game_mod_precmd[0]['precmd'];
					
				//Get Postinstall commands from db
				$game_mod_postcmd = $db->resultQuery("SELECT DISTINCT postcmd FROM OGP_DB_PREFIXgame_mods WHERE mod_id='" . $db->realEscapeSingle($mod_id) . "'");
				if ($game_mod_postcmd[0]['postcmd'] === NULL OR empty($game_mod_postcmd[0]['postcmd']))
				{
					$config_mod_postcmd = $db->resultQuery("SELECT DISTINCT def_postcmd FROM OGP_DB_PREFIXconfig_mods WHERE mod_cfg_id='" . $db->realEscapeSingle($mod_cfg_id) . "'");
					if ($config_mod_postcmd[0]['def_postcmd'] === NULL OR empty($config_mod_postcmd[0]['def_postcmd']))
						$postcmd = "";
					else
						$postcmd = $config_mod_postcmd[0]['def_postcmd'];
				}
				else
					$postcmd = $game_mod_postcmd[0]['postcmd'];

				//Enable FTP account in remote server
				if ($ftp == "enabled")
				{
					$remote->ftp_mgr("useradd", $home_info['home_id'], $home_info['ftp_password'], $home_info['home_path']);
					$db->changeFtpStatus('enabled',$home_info['home_id']);
				}
				
				//Install files for this service in the remote server
				// -Steam
				$exec_folder_path = clean_path($home_info['home_path'] . "/" . $server_xml->exe_location );
				$exec_path = clean_path($exec_folder_path . "/" . $server_xml->server_exec_name );
				
				if ($install_method == "steam")
				{
					if ( $server_xml->installer == "steamcmd" )
					{
						if( preg_match("/win32/", $server_xml->game_key) OR preg_match("/win64/", $server_xml->game_key) ) 
							$cfg_os = "windows";
						elseif( preg_match("/linux/", $server_xml->game_key) )
							$cfg_os = "linux";
						
						// Some games like L4D2 require anonymous login
						if($mod_xml->installer_login){
							$login = $mod_xml->installer_login;
							$pass = '';
						}else{
							$login = $settings['steam_user'];
							$pass = $settings['steam_pass'];
						}
						
						$modname = ( $installer_name == '90' and !preg_match("/(cstrike|valve)/", $modkey) ) ? $modkey : '';
						$betaname = isset($mod_xml->betaname) ? $mod_xml->betaname : '';
						$betapwd = isset($mod_xml->betapwd) ? $mod_xml->betapwd : '';
						$arch = isset($mod_xml->steam_bitness) ? $mod_xml->steam_bitness : '';
						
						$remote->steam_cmd( $home_id,$home_info['home_path'],$installer_name,$modname,
											$betaname,$betapwd,$login,$pass,$settings['steam_guard'],
											$exec_folder_path,$exec_path,$precmd,$postcmd,$cfg_os,'',$arch); 
					}
				}
				// -Rsync
				elseif ($install_method == "rsync")
				{
					//Rsync Server
					$url = "rsync.opengamepanel.org";
					//OS
					if( preg_match("/win32/", $server_xml->game_key) OR preg_match("/win64/", $server_xml->game_key) ) 
						$os = "windows";
					elseif( preg_match("/linux/", $server_xml->game_key) )
						$os = "linux";
					//Rsync Game Name
					if( isset($server_xml->lgsl_query_name) )
					{
						$rs_gname = $server_xml->lgsl_query_name;
						if($rs_gname == "quake3")
						{
							if($server_xml->game_name == "Quake 3")
								$rs_gname = "q3";
						}
					}
					elseif( isset($server_xml->gameq_query_name) )
					{
						$rs_gname = $server_xml->gameq_query_name;
						if($rs_gname == "minecraft")
						{
							if($server_xml->game_name == "Minecraft Tekkit")
								$rs_gname = "tekkit";
							elseif($server_xml->game_name == "Minecraft Bukkit")
								$rs_gname = "bukkit";
						}
					}
					elseif( isset($server_xml->protocol) )
						$rs_gname = $server_xml->protocol;
					else
						$rs_gname = $server_xml->mods->mod['key'];
					//Starting Sync
					$full_url = "$url/ogp_game_installer/$rs_gname/$os/";
					$remote->start_rsync_install($home_id,$home_info['home_path'],"$full_url",$exec_folder_path,$exec_path,$precmd,$postcmd);
				}
				// -Manual
				elseif ($install_method == "manual")
				{
					// Start File Download and uncompress
					$filename = !empty($manual_url) ? substr($manual_url, -9) : "";
					$remote->start_file_download($manual_url,$home_info['home_path'],$filename,"uncompress");
				}
				echo "<h4>".get_lang('success')."</h4><br><p>".get_lang('starting_installations')."</p><br>";
			}
			// Set expiration date in ogp database
			if ($order['invoice_duration'] == "hour")
			{
				$add_time = time() + ($order['qty'] * 60 * 60);
				$end_date = date('YmdHi',$add_time);
				$period_to_extend = time() + ( ( $order['qty'] * 60 * 60 ) + 900 ); // Fifteen minutes to extend or finish the server.
				$finish_date = date('YmdHi',$period_to_extend);
			}
			elseif ($order['invoice_duration'] == "month")
			{
				$end_date = date('YmdHi', strtotime('+'.$order['qty'].' month'));
				$finish_date = date('YmdHi', strtotime('+'.$order['qty'].' month 5 day')); // 5 days to extend or finish the server.
			}
			elseif ($order['invoice_duration'] == "year")
			{
				$end_date = date('YmdHi', strtotime('+'.$order['qty'].' year'));
				$finish_date = date('YmdHi', strtotime('+'.$order['qty'].' year 15 day')); // Fifteen days to extend or finish the server.
			}
			// set order expire date
			$db->query("UPDATE OGP_DB_PREFIXbilling_orders
						SET end_date='" . $db->realEscapeSingle($end_date) . "' 
						WHERE order_id=".$db->realEscapeSingle($order_id));
	
			$db->query("UPDATE OGP_DB_PREFIXbilling_orders
						SET finish_date='" . $db->realEscapeSingle($finish_date) . "' 
						WHERE order_id=".$db->realEscapeSingle($order_id));
						
			// Save home id created by this order
			$db->query("UPDATE OGP_DB_PREFIXbilling_orders
						SET home_id='" . $db->realEscapeSingle($home_id) . "' WHERE order_id=".$db->realEscapeSingle($order_id));
						
		}

		//Update Cart Payment Status as 3(paid and installed)
		$db->query("UPDATE OGP_DB_PREFIXbilling_carts
					SET paid=3
					WHERE cart_id=".$db->realEscapeSingle($cart_id));

		// Set payment/creation date
		$date = date('d/m/Y H:i');
		$db->query("UPDATE OGP_DB_PREFIXbilling_carts 
					SET date='" . $db->realEscapeSingle($date) . "' 
					WHERE cart_id=".$db->realEscapeSingle($cart_id));

		//Refresh to Game Monitor.
		$view->refresh("home.php?m=gamemanager&p=game_monitor");
	}
}
?>
