<?php
function exec_ogp_module()
{	
	if (isset($_POST['payment_status']) AND ($_POST['payment_status']=="Completed" OR $_POST['payment_status']=="Canceled_Reversal"))
	{
		echo "<html><head><title>Success</title></head><body><h4>Thank you for your order.</h4>";
	}
	else if (isset($_POST['payment_status']) AND ( $_POST['payment_status']=="Pending" OR $_POST['payment_status']=="In-Progress" OR $_POST['payment_status']=="Partially_Refunded" ) )
	{
		echo "<html><head><title>Pending</title></head><body><h4>Pending<br>Thank you for your order.</h4><br><p style='color:red'>Payment process is pending</p>";
	}
	else if (isset($_POST['payment_status']) AND ($_POST['payment_status']=="Reversed" OR $_POST['payment_status']=="Refunded" OR $_POST['payment_status']=="Denied" OR $_POST['payment_status']=="Expired" OR $_POST['payment_status']=="Failed" OR $_POST['payment_status']=="Voided"))
	{
		echo "<html><head><title>Reversed OR Refunded</title></head><body><h4>Reversed OR Refunded</h4>";
	}
	echo "<meta HTTP-EQUIV='REFRESH' content='2; url=?m=simple-billing&p=cart'>";
}
?>
