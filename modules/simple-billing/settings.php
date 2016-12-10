<?php
function curPageName() 
{
	return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}

function exec_ogp_module()
{		
	require('includes/config.inc.php');
	require_once('modules/settings/functions.php');
    require_once('includes/form_table_class.php');
    global $db,$view,$settings;
	
	$pp_currencies = Array ( 
							'AUD'	=>	'Australian Dollar',
							'BRL'	=>	'Brazilian Real',
							'CAD'	=>	'Canadian Dollar',
							'CZK'	=>	'Czech Koruna',
							'DKK'	=>	'Danish Krone',
							'EUR'	=>	'Euro',
							'HKD'	=>	'Hong Kong Dollar',
							'HUF'	=>	'Hungarian Forint',
							'ILS'	=>	'Israeli New Sheqel',
							'JPY'	=>	'Japanese Yen',
							'MYR'	=>	'Malaysian Ringgit',
							'MXN'	=>	'Mexican Peso',
							'NOK'	=>	'Norwegian Krone',
							'NZD'	=>	'New Zealand Dollar',
							'PHP'	=>	'Philippine Peso',
							'PLN'	=>	'Polish Zloty',
							'GBP'	=>	'Pound Sterling',
							'RUB'	=>	'Russian Ruble',
							'SGD'	=>	'Singapore Dollar',
							'SEK'	=>	'Swedish Krona',
							'CHF'	=>	'Swiss Franc',
							'TWD'	=>	'Taiwan New Dollar',
							'THB'	=>	'Thai Baht',
							'TRY'	=>	'Turkish Lira',
							'USD'	=>	'U.S. Dollar'
						);
	
	$sk_currencies = Array ( 
							'AUD'	=>	'Australian Dollar',
							'BGN'	=>	'Bulgarian Leva',
							'CAD'	=>	'Canadian Dollar',
							'HRK'	=>	'Croatian Kuna',
							'CZK'	=>	'Czech Koruna',
							'DKK'	=>	'Danish Krone',
							'EEK'	=>	'Estonian Kroon',
							'EUR'	=>	'Euro',
							'HKD'	=>	'Hong Kong Dollar',
							'HUF'	=>	'Hungarian Forint',
							'ISK'	=>	'Iceland Krona',
							'INR'	=>	'Indian Rupee',
							'ILS'	=>	'Israeli New Sheqel',
							'JPY'	=>	'Japanese Yen',
							'JOD'	=>	'Jordanian Dinar',
							'LVL'	=>	'Latvian Lat',
							'LTL'	=>	'Lithuanian Litas',
							'MYR'	=>	'Malaysian Ringgit',
							'MAD'	=>	'Moroccan Dirham',
							'NOK'	=>	'Norwegian Krone',
							'NZD'	=>	'New Zealand Dollar',
							'OMR'	=>	'Omani Rial',
							'PLN'	=>	'Polish Zloty',
							'GBP'	=>	'Pound Sterling',
							'QAR'	=>	'Qatari Rial',
							'RON'	=>	'Romanian Leu New',
							'SAR'	=>	'Saudi Riyal',
							'RSD'	=>	'Serbian dinar',
							'SGD'	=>	'Singapore Dollar',
							'SKK'	=>	'Slovakian Koruna',
							'ZAR'	=>	'South-African Rand',
							'KRW'	=>	'South-Korean Won',
							'SEK'	=>	'Swedish Krona',
							'CHF'	=>	'Swiss Franc',
							'TWD'	=>	'Taiwan New Dollar',
							'THB'	=>	'Thai Baht',
							'TND'	=>	'Tunisian Dinar',
							'TRY'	=>	'Turkish Lira',
							'AED'	=>	'Utd. Arab Emir. Dirham',
							'USD'	=>	'U.S. Dollar'
						);

	$pg_currencies = Array ( 
							"AED"	=>	"United Arab Emirates Dirham",
							"ALL"	=>	"Albania Lek",
							"ARS"	=>	"Argentina Peso",
							"AUD"	=>	"Australia Dollar",
							"AZN"	=>	"Azerbaijan New Manat",
							"BAM"	=>	"Bosnia and Herzegovina Convertible Marka",
							"BGN"	=>	"Bulgaria Lev",
							"BOB"	=>	"Bolivia Boliviano",
							"BRL"	=>	"Brazil Real",
							"BYR"	=>	"Belarus Ruble",
							"CAD"	=>	"Canada Dollar",
							"CHF"	=>	"Switzerland Franc",
							"CLP"	=>	"Chile Peso",
							"COP"	=>	"Colombia Peso",
							"CRC"	=>	"Costa Rica Colon",
							"CZK"	=>	"Czech Republic Koruna",
							"DKK"	=>	"Denmark Krone",
							"DOP"	=>	"Dominican Republic Peso",
							"EGP"	=>	"Egypt Pound",
							"EUR"	=>	"Euro",
							"GBP"	=>	"United Kingdom Pound",
							"GTQ"	=>	"Guatemala Quetzal",
							"HKD"	=>	"Hong Kong Dollar",
							"HRK"	=>	"Croatia Kuna",
							"HUF"	=>	"Hungary Forint",
							"IDR"	=>	"Indonesia Rupiah",
							"ILS"	=>	"Israel Shekel",
							"IQD"	=>	"Iraq Dinar",
							"JOD"	=>	"Jordan Dinar",
							"KES"	=>	"Kenya Shilling",
							"KGS"	=>	"Kyrgyzstan Som",
							"KWD"	=>	"Kuwait Dinar",
							"LTL"	=>	"Lithuania Litas",
							"LVL"	=>	"Latvia Lat",
							"MAD"	=>	"Morocco Dirham",
							"MKD"	=>	"Macedonia Denar",
							"MXN"	=>	"Mexico Peso",
							"MYR"	=>	"Malaysia Ringgit",
							"NGN"	=>	"Nigeria Naira",
							"NIO"	=>	"Nicaragua Cordoba",
							"NOK"	=>	"Norway Krone",
							"PEN"	=>	"Peru Nuevo Sol",
							"PLN"	=>	"Poland Zloty",
							"QAR"	=>	"Qatar Riyal",
							"RSD"	=>	"Serbia Dinar",
							"RUB"	=>	"Russia Ruble",
							"SAR"	=>	"Saudi Arabia Riyal",
							"SEK"	=>	"Sweden Krona",
							"THB"	=>	"Thailand Baht",
							"TRY"	=>	"Turkey Lira",
							"TWD"	=>	"Taiwan New Dollar",
							"TZS"	=>	"Tanzania Shilling",
							"UAH"	=>	"Ukraine Hryvna",
							"USD"	=>	"U.S. Dollar",
							"UYU"	=>	"Uruguay Peso",
							"VEF"	=>	"Venezuela Bolivar",
							"VND"	=>	"Viet Nam Dong",
							"ZAR"	=>	"South Africa Rand"
						);

	$rk_currencies = Array (
							"RUB"	=>	"Russia Ruble"
						);				
	
	$settings['paypal'] = isset($settings['paypal']) ? $settings['paypal'] : "1";
	$settings['paygol'] = isset($settings['paygol']) ? $settings['paygol'] : "1";
	$settings['skrill'] = isset($settings['skrill']) ? $settings['skrill'] : "1";
	$settings['robokassa'] = isset($settings['robokassa']) ? $settings['robokassa'] : "1";
	$settings['currency'] = isset($settings['currency']) ? $settings['currency'] : "EUR";
	$settings['hourly'] = isset($settings['hourly']) ? $settings['hourly'] : 1;
	$settings['monthly'] = isset($settings['monthly']) ? $settings['monthly'] : 1;
	$settings['annually'] = isset($settings['annually']) ? $settings['annually'] : 1;
	$settings['tax_amount'] = isset($settings['tax_amount']) ? $settings['tax_amount'] : 18;
	$settings['paypal_email'] = isset($settings['paypal_email']) ? $settings['paypal_email'] : "Business@E-mail";
	$settings['skrill_merchant_id'] = isset($settings['skrill_merchant_id']) ? $settings['skrill_merchant_id'] : "";
	$settings['skrill_email'] = isset($settings['skrill_email']) ? $settings['skrill_email'] : "Business@E-mail";
	$settings['skrill_secret_word'] = (isset($settings['skrill_secret_word']) and $settings['skrill_secret_word'] != "") ? get_lang('hash_stored_correctly') : "";
	$settings['paygol_service_id'] = isset($settings['paygol_service_id']) ? $settings['paygol_service_id'] : "0";
	$settings['robokassa_merchant_login'] = isset($settings['robokassa_merchant_login']) ? $settings['robokassa_merchant_login'] : "";
	$settings['robokassa_securepass1'] = isset($settings['robokassa_securepass1']) ? $settings['robokassa_securepass1'] : "";
	$settings['robokassa_securepass2'] = isset($settings['robokassa_securepass2']) ? $settings['robokassa_securepass2'] : "";
	function checked($value){
		global $settings;
		if( $settings[$value] == 1 )
			return 'checked="checked"';
	}
	
	$currencies = array();
	
	if($settings['paypal'] == "1")
		$currencies = array_merge($currencies,$pp_currencies);
	if($settings['paygol'] == "1")
		$currencies = array_merge($currencies,$pg_currencies);
	if($settings['skrill'] == "1")
		$currencies = array_merge($currencies,$sk_currencies);
	if($settings['robokassa'] == "1")
		$currencies = array_merge($currencies,$rk_currencies);
		
	asort($currencies);

	if(isset($_POST['currency']))
	{
			$currency = $_REQUEST['currency'];
			$ERROR = FALSE;
			$_SESSION['err_str'] = "";
			if($_REQUEST['paypal'] == '1')
			{
				if(!array_key_exists($currency,$pp_currencies))
				{
					$_SESSION['err_str'] .= get_lang_f('currency_not_available_at',$currencies[$currency],get_lang('paypal'))."<br>";
					$ERROR = TRUE;
				}
			}
			if($_REQUEST['paygol'] == '1')
			{
				if(!array_key_exists($currency,$pg_currencies))
				{
					$_SESSION['err_str'] .= get_lang_f('currency_not_available_at',$currencies[$currency],get_lang('paygol'))."<br>";
					$ERROR = TRUE;
				}
			}
			if($_REQUEST['skrill'] == '1')
			{
				if(!array_key_exists($currency,$sk_currencies))
				{
					$_SESSION['err_str'] .= get_lang_f('currency_not_available_at',$currencies[$currency],get_lang('skrill'));
					$ERROR = TRUE;
				}
			}
			if($_REQUEST['robokassa'] == '1')
			{
				if(!array_key_exists($currency,$rk_currencies))
				{
					$_SESSION['err_str'] .= get_lang_f('currency_not_available_at',$currencies[$currency],get_lang('robokassa'));
					$currency = "RUB";
				}
			}
		if($ERROR)
			$currency = "EUR";
	}
	
    if ( isset($_REQUEST['update_settings']) )
    {
        $settings = array(
			"paypal" => $_REQUEST['paypal'],
			"paygol" => $_REQUEST['paygol'],
			"skrill" => $_REQUEST['skrill'],
			"robokassa" => $_REQUEST['robokassa'],
			"currency" => $currency,
			"hourly" => @$_REQUEST['hourly'],
			"monthly" => @$_REQUEST['monthly'],
			"annually" => @$_REQUEST['annually'],
			"tax_amount" => $_REQUEST['tax_amount'],
			"paypal_email" => $_REQUEST['paypal_email'],
			"skrill_merchant_id" => $_REQUEST['skrill_merchant_id'],
			"skrill_email" => $_REQUEST['skrill_email'],
			"paygol_service_id" => $_REQUEST['paygol_service_id'],
			"robokassa_merchant_login" => $_REQUEST['robokassa_merchant_login'],
			"robokassa_securepass1" => $_REQUEST['robokassa_securepass1'],
			"robokassa_securepass2" => $_REQUEST['robokassa_securepass2']);
			
		if($_REQUEST['skrill_secret_word'] != get_lang('hash_stored_correctly'))
		{
			if($_REQUEST['skrill_secret_word'] != "")
				$settings['skrill_secret_word'] = md5($_REQUEST['skrill_secret_word']);
			else
				$settings['skrill_secret_word'] = "";
		}
        $db->setSettings($settings);
        print_success(get_lang('settings_updated'));
        $view->refresh("?m=simple-billing&p=shop_settings");
        return;
    }
	
	$s = ( isset($_SERVER['HTTPS']) and  get_true_boolean($_SERVER['HTTPS']) ) ? "s" : "";
	$p = isset($_SERVER['SERVER_PORT']) & $_SERVER['SERVER_PORT'] != "80" ? ":".$_SERVER['SERVER_PORT'] : NULL ;
	$this_script = 'http'.$s.'://'.$_SERVER['SERVER_NAME'].$p.$_SERVER['SCRIPT_NAME'];
	$current_folder_url = str_replace( curPageName(), "", $this_script);
	$robokassa_Result_URL = $current_folder_url.'modules/simple-billing/robokassa-ipn.php';
	
    echo "<style>
		  h4{
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
			font-family:'Trebuchet MS';
			}
			</style>
			";
    echo "<h2>".get_lang('shop_settings')."</h2>";
	print_failure($_SESSION['err_str']);
    $ft = new FormTable();
    $ft->start_form("?m=simple-billing&p=shop_settings");
    $ft->start_table();
	echo "<tr><td colspan='2' ><h4>".get_lang('payment_gateway')."</h4></td></tr>";
	$ft->add_custom_field('paypal','<input type="checkbox" name="paypal" value="1" '.checked('paypal').'/>');
	$ft->add_custom_field('paygol','<input type="checkbox" name="paygol" value="1" '.checked('paygol').'/>');
	$ft->add_custom_field('skrill','<input type="checkbox" name="skrill" value="1" '.checked('skrill').'/>');
	$ft->add_custom_field('robokassa','<input type="checkbox" name="robokassa" value="1" '.checked('robokassa').'/>');
	$ft->add_custom_field('currency',
        create_drop_box_from_array($currencies,"currency",$settings['currency'],false));
	echo "<tr><td colspan='2' ><h4>".get_lang('available_invoice_types')."</h4></td></tr>";
	$ft->add_custom_field('hourly','<input type="checkbox" name="hourly" value="1" '.checked('hourly').'/>');
	$ft->add_custom_field('monthly','<input type="checkbox" name="monthly" value="1" '.checked('monthly').'/>');
	$ft->add_custom_field('annually','<input type="checkbox" name="annually" value="1" '.checked('annually').'/>');
	echo "<tr><td colspan='2' ><h4>".get_lang('tax_amount')."</h4></td></tr>";
	$ft->add_field('string','tax_amount',$settings['tax_amount'],2);
	echo "<tr><td colspan='2' ><h4>".get_lang('paypal_email')."</h4></td></tr>";
	$ft->add_field('string','paypal_email',$settings['paypal_email'],35);
	echo "<tr><td colspan='2' ><h4>".get_lang('skrill_merchant_info')."</h4></td></tr>";
	$ft->add_field('string','skrill_merchant_id',$settings['skrill_merchant_id'],35);
	$ft->add_field('string','skrill_email',$settings['skrill_email'],35);
	$ft->add_field('string','skrill_secret_word',$settings['skrill_secret_word'],35);
	echo "<tr><td colspan='2' ><h4>".get_lang('paygol_service')."</h4></td></tr>";
	$ft->add_field('string','paygol_service_id',$settings['paygol_service_id'],35);
	echo "<tr><td colspan='2' ><h4>".get_lang('robokassa_service')."</h4></td></tr>";
	$ft->add_field('string','robokassa_merchant_login',$settings['robokassa_merchant_login'],35);
	$ft->add_field('password','robokassa_securepass1',$settings['robokassa_securepass1'],35);
	$ft->add_field('password','robokassa_securepass2',$settings['robokassa_securepass2'],35);
	echo "<tr><td align='right'>Result_URL:</td><td align='left'><b>".$robokassa_Result_URL."</b></td></tr>";
	$ft->end_table();
	$ft->add_button("submit","update_settings",get_lang('update_settings'));
	$ft->end_form();
}
?>
